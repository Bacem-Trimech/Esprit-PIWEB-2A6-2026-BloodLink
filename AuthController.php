<?php
/** Public pages, sign up, sign in, sign out and the donor's own space (front office). */
class AuthController extends BaseController
{
    private const BAD_TOKEN = 'Invalid security token. Please try again.';

    public function home(): void { $this->render('home', ['title' => 'Home']); }
    public function notFound(): void { $this->missing(); }

    // ---------------- LOGIN ----------------
    public function login(): void
    {
        if ($u = $this->auth->check()) $this->redirect($this->spaceOf($u));
        $errors = [];
        $old = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->auth->verifyCsrf()) {
                $errors[] = self::BAD_TOKEN;
            } else {
                $old['email'] = trim($_POST['email'] ?? '');
                $err = $this->auth->login($old['email'], (string)($_POST['password'] ?? ''));
                if ($err === null) $this->redirect($this->spaceOf($this->auth->check()));   // role detection
                $errors[] = $err;
            }
        }
        $this->render('login', ['title' => 'Log in', 'errors' => $errors, 'old' => $old]);
    }

    // ---------------- REGISTER (always creates a donor) ----------------
    public function register(): void
    {
        if ($this->auth->check()) $this->redirect('dashboard');
        $errors = [];
        $old = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = [
                'username' => trim($_POST['username'] ?? ''), 'email' => trim($_POST['email'] ?? ''),
                'city' => trim($_POST['city'] ?? ''), 'birth_date' => trim($_POST['birth_date'] ?? ''),
            ];
            $pw = (string)($_POST['password'] ?? '');
            if (!$this->auth->verifyCsrf()) {
                $errors[] = self::BAD_TOKEN;
            } else {
                $errors = array_values(array_filter([
                    Validator::username($old['username']), Validator::email($old['email']), Validator::password($pw),
                    $pw !== (string)($_POST['password_confirm'] ?? '') ? 'Passwords do not match.' : null,
                    Validator::city($old['city']), Validator::birthDate($old['birth_date'], true),
                ]));
                if (!$errors && $this->users->exists($old['email'], $old['username'])) $errors[] = 'This email or username is already taken.';
                if (!$errors) {
                    $this->users->create(new User(
                        username: $old['username'], email: $old['email'], password: password_hash($pw, PASSWORD_DEFAULT),
                        roleId: $this->users->roleIdByCode('donneur'),          // role forced: no self-promotion
                        city: $old['city'] ?: null, birthDate: $old['birth_date']
                    ));
                    $this->flash('ok', 'Account created successfully, you can now log in.');
                    $this->redirect('login');
                }
            }
        }
        $this->render('register', ['title' => 'Sign up', 'errors' => $errors, 'old' => $old]);
    }

    // ---------------- LOGOUT (POST + CSRF token) ----------------
    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->auth->verifyCsrf()) $this->auth->logout();
        $this->redirect('login');
    }

    // ---------------- DONOR SPACE: profile + change password ----------------
    public function dashboard(): void
    {
        $u = $this->auth->check();
        if (!$u) $this->redirect('login');
        if ($this->spaceOf($u) === 'admin') $this->redirect('admin');
        $errors = [];
        $old = [];
        $form = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $form = $_POST['form'] ?? '';
            if (!$this->auth->verifyCsrf()) {
                $errors[] = self::BAD_TOKEN;
            } elseif ($form === 'profile') {
                $old = [
                    'username' => trim($_POST['username'] ?? ''), 'email' => trim($_POST['email'] ?? ''),
                    'city' => trim($_POST['city'] ?? ''), 'birth_date' => trim($_POST['birth_date'] ?? ''),
                ];
                $errors = array_values(array_filter([
                    Validator::username($old['username']), Validator::email($old['email']),
                    Validator::city($old['city']), Validator::birthDate($old['birth_date'], true),
                ]));
                if (!$errors && $this->users->exists($old['email'], $old['username'], $u->getId())) $errors[] = 'This email or username is already taken.';
                if (!$errors) {
                    $u->setUsername($old['username']); $u->setEmail($old['email']);
                    $u->setCity($old['city'] ?: null); $u->setBirthDate($old['birth_date']);
                    $this->users->update($u);                                    // role is never read from the form
                    $this->flash('ok', 'Profile updated.');
                    $this->redirect('dashboard');
                }
            } elseif ($form === 'password') {
                $new = (string)($_POST['new_password'] ?? '');
                if (!password_verify((string)($_POST['current_password'] ?? ''), $u->getPassword())) $errors[] = 'Current password is incorrect.';
                elseif ($err = Validator::password($new)) $errors[] = $err;
                elseif ($new !== (string)($_POST['new_password_confirm'] ?? '')) $errors[] = 'Passwords do not match.';
                else {
                    $this->users->updatePassword($u->getId(), password_hash($new, PASSWORD_DEFAULT));
                    session_regenerate_id(true);
                    $this->flash('ok', 'Password changed.');
                    $this->redirect('dashboard');
                }
            }
        }
        $this->render('dashboard', ['title' => 'My space', 'errors' => $errors, 'old' => $old, 'form' => $form]);
    }
}
