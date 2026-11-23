<?php
/**
 * AuthService: sessions, login / logout, roles and permissions, CSRF.
 * The role is re-read from the database on every request (check()), never trusted from the session alone.
 */
class AuthService
{
    private ?User $current = null;
    private bool $loaded = false;
    // Used only to spend the same time when the email does not exist (no user enumeration by timing).
    private const DUMMY_HASH = '$2y$10$B9OJgl/Y9A05dLd4TYZtsuZ2.qLbFcnJYdxLHcije9.Z/hhkDxkcq';

    public function __construct(private UserService $users) {}

    // ---------------- CSRF ----------------
    public function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function verifyCsrf(): bool
    {
        return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''));
    }

    // ---------------- LOGIN / LOGOUT ----------------
    /** Returns an error message, or null when the user is now logged in. */
    public function login(string $email, string $password): ?string
    {
        $u = $this->users->findByEmail($email);
        $ok = password_verify($password, $u ? $u->getPassword() : self::DUMMY_HASH);
        if (!$u || !$ok) {
            return 'Incorrect email or password.';          // generic on purpose
        }
        if ($u->getStatus() !== 'active') {
            return 'This account is suspended. Contact an administrator.';
        }
        session_regenerate_id(true);                         // protects against session fixation
        $_SESSION['user_id'] = $u->getId();
        $_SESSION['username'] = $u->getUsername();
        $_SESSION['role'] = $u->getRoleCode();
        $_SESSION['last_activity'] = time();
        $this->loaded = false;
        return null;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        $this->current = null;
        $this->loaded = false;
    }

    // ---------------- CURRENT USER AND ROLES ----------------
    /** The logged-in user, or null. Suspended or deleted accounts are logged out immediately. */
    public function check(): ?User
    {
        if ($this->loaded) return $this->current;
        if (empty($_SESSION['user_id'])) return null;
        $u = $this->users->findById((int)$_SESSION['user_id']);
        if (!$u || $u->getStatus() !== 'active') {
            $this->logout();
            session_start();                                 // fresh empty session for the next page
            return null;
        }
        $_SESSION['role'] = $u->getRoleCode();               // role refreshed from the database
        $this->loaded = true;
        return $this->current = $u;
    }

    public function can(string $permission): bool
    {
        $u = $this->check();
        return $u !== null && in_array($permission, PERMISSIONS[$u->getRoleCode()] ?? [], true);
    }

    /** May $actor edit / suspend / delete $target (according to MANAGEABLE in config.php)? */
    public function canManage(User $actor, User $target): bool
    {
        return in_array($target->getRoleCode(), MANAGEABLE[$actor->getRoleCode()] ?? [], true);
    }
}
