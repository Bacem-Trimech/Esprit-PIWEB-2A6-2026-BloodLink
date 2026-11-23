<?php
/** Back office: dashboard, users CRUD, roles. Every action starts with need() = login + permission check. */
class UserController extends BaseController
{
    private const BAD_TOKEN = 'Invalid security token. Please try again.';

    public function admin(): void
    {
        $this->need('backoffice');
        $this->render('dashboard', [
            'title' => 'Dashboard', 'total' => $this->users->countAll(),
            'byRole' => $this->users->roleCounts(), 'roles' => $this->users->roles(), 'recent' => $this->users->recent(5),
        ], 'back');
    }

    // ---------------- READ (list + search + filter) ----------------
    public function users(): void
    {
        $actor = $this->need('users.view');
        $q = trim($_GET['q'] ?? '');
        $role = trim($_GET['role'] ?? '');
        $rows = [];
        foreach ($this->users->findAll($q, $role !== '' ? $role : null) as $t) {
            $manage = $this->auth->canManage($actor, $t);
            $other = $t->getId() !== $actor->getId();
            $rows[] = [
                'user'   => $t,
                'edit'   => $manage && $this->auth->can('users.edit'),
                'status' => $manage && $other && $this->auth->can('users.suspend'),
                'delete' => $manage && $other && $this->auth->can('users.delete'),
            ];
        }
        $this->render('users', ['title' => 'Users', 'rows' => $rows, 'q' => $q, 'role' => $role, 'roles' => $this->users->roles()], 'back');
    }

    // ---------------- CREATE / UPDATE ----------------
    public function create(): void { $this->save(null); }
    public function edit(): void { $this->save((int)($_GET['id'] ?? 0)); }

    private function save(?int $id): void
    {
        $actor = $this->need($id === null ? 'users.create' : 'users.edit');
        $target = null;
        if ($id !== null) {
            $target = $this->users->findById($id);
            if (!$target) $this->missing('back');
            if (!$this->auth->canManage($actor, $target)) $this->forbid();
        }
        $canAssign = $this->auth->can('roles.assign');
        $old = [
            'username' => $target ? $target->getUsername() : '', 'email' => $target ? $target->getEmail() : '',
            'city' => $target ? (string)$target->getCity() : '', 'birth_date' => $target ? (string)$target->getBirthDate() : '',
            'role_id' => $target ? $target->getRoleId() : $this->users->roleIdByCode('donneur'),
        ];
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = [
                'username' => trim($_POST['username'] ?? ''), 'email' => trim($_POST['email'] ?? ''),
                'city' => trim($_POST['city'] ?? ''), 'birth_date' => trim($_POST['birth_date'] ?? ''), 'role_id' => $old['role_id'],
            ];
            $pw = (string)($_POST['password'] ?? '');
            if (!$this->auth->verifyCsrf()) {
                $errors[] = self::BAD_TOKEN;
            } else {
                $errors = array_values(array_filter([
                    Validator::username($old['username']), Validator::email($old['email']),
                    ($id === null || $pw !== '') ? Validator::password($pw) : null,
                    Validator::city($old['city']), Validator::birthDate($old['birth_date']),
                ]));
                if ($canAssign && isset($_POST['role_id'])) {                    // only roles.assign may choose a role
                    $newRole = (int)$_POST['role_id'];
                    $codes = array_column(array_map(fn(Role $r) => ['id' => $r->getId(), 'code' => $r->getCode()], $this->users->roles()), 'code', 'id');
                    if (!isset($codes[$newRole])) $errors[] = 'Unknown role.';
                    elseif ($target && $newRole !== $target->getRoleId()) {
                        if ($target->getId() === $actor->getId()) $errors[] = 'You cannot change your own role.';
                        elseif ($target->getRoleCode() === 'super_admin' && $this->users->countByRole('super_admin') <= 1) $errors[] = 'You cannot remove the last Super admin.';
                    }
                    $old['role_id'] = $newRole;
                }
                if (!$errors && $this->users->exists($old['email'], $old['username'], $target ? $target->getId() : 0)) $errors[] = 'This email or username is already taken.';
                if (!$errors) {
                    $u = $target ?? new User();
                    $u->setUsername($old['username']); $u->setEmail($old['email']);
                    $u->setCity($old['city'] ?: null); $u->setBirthDate($old['birth_date'] ?: null);
                    $u->setRoleId((int)$old['role_id']);
                    if ($target) {
                        $this->users->update($u);
                        if ($pw !== '') $this->users->updatePassword($u->getId(), password_hash($pw, PASSWORD_DEFAULT));
                        $this->flash('ok', 'User updated.');
                    } else {
                        $u->setPassword(password_hash($pw, PASSWORD_DEFAULT));
                        $this->users->create($u);
                        $this->flash('ok', 'User created.');
                    }
                    $this->redirect('users');
                }
            }
        }
        $this->render('user_form', [
            'title' => $target ? 'Edit user' : 'Add user', 'target' => $target, 'old' => $old,
            'errors' => $errors, 'roles' => $canAssign ? $this->users->roles() : [],
        ], 'back');
    }

    // ---------------- SUSPEND / REACTIVATE and DELETE (POST only) ----------------
    public function status(): void { $this->change('users.suspend', false); }
    public function delete(): void { $this->change('users.delete', true); }

    private function change(string $permission, bool $delete): void
    {
        $actor = $this->need($permission);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->auth->verifyCsrf()) { $this->flash('error', self::BAD_TOKEN); $this->redirect('users'); }
        $t = $this->users->findById((int)($_POST['id'] ?? 0));
        if (!$t) $this->missing('back');
        if (!$this->auth->canManage($actor, $t)) $this->forbid();
        if ($t->getId() === $actor->getId()) { $this->flash('error', 'You cannot do this to your own account.'); $this->redirect('users'); }
        $removing = $delete || $t->getStatus() === 'active';          // deleting or suspending
        if ($removing && $t->getRoleCode() === 'super_admin' && $this->users->countByRole('super_admin') <= 1) {
            $this->flash('error', 'You cannot remove the last Super admin.'); $this->redirect('users');
        }
        if ($delete) { $this->users->delete($t->getId()); $this->flash('ok', 'User deleted.'); }
        else {
            $this->users->setStatus($t->getId(), $t->getStatus() === 'active' ? 'suspended' : 'active');
            $this->flash('ok', $t->getStatus() === 'active' ? 'User suspended.' : 'User reactivated.');
        }
        $this->redirect('users');
    }

    // ---------------- ROLES (read-only overview) ----------------
    public function roles(): void
    {
        $this->need('users.view');
        $this->render('roles', ['title' => 'Roles', 'roles' => $this->users->roles(), 'byRole' => $this->users->roleCounts()], 'back');
    }
}
