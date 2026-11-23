<?php
declare(strict_types=1);

function demarrerSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        session_name('BLOODLINKSESSID');
        session_set_cookie_params([
            'httponly' => true,
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'samesite' => 'Lax',
            'path' => '/',
        ]);
        session_start();
    }

    // Expiration automatique après 30 minutes d'inactivité (Workshop Sécurité 5.5)
    $sessionLifetime = 1800;
    if (isset($_SESSION['last_activity']) && (time() - (int)$_SESSION['last_activity'] > $sessionLifetime)) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        session_start();
    }
    $_SESSION['last_activity'] = time();

    // En-têtes HTTP de sécurité (Workshop Sécurité 7.5)
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self'; img-src 'self' data: blob:; base-uri 'self'; form-action 'self'");
    header('Cache-Control: no-store');
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function jetonCsrf(): string
{
    if (!isset($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function verifierCsrf(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals(jetonCsrf(), $sent)) {
        http_response_code(419);
        throw new RuntimeException('Session expirée. Rechargez la page et réessayez.');
    }
}

function utilisateur(): ?array
{
    if (empty($_SESSION['user_id'])) return null;
    $sql = 'SELECT id, nom, email, role, actif, profil_complet, avatar_couleur FROM utilisateurs WHERE id = ?';
    $q = connexion()->prepare($sql);
    $q->execute([$_SESSION['user_id']]);
    $user = $q->fetch();
    if (!$user || !$user['actif']) {
        unset($_SESSION['user_id']);
        return null;
    }
    return $user;
}

function exigerConnexion(): array
{
    $user = utilisateur();
    if (!$user) {
        header('Location: index.php?page=connexion');
        exit;
    }
    return $user;
}

function exigerRole(array $roles): array
{
    $user = exigerConnexion();
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        throw new RuntimeException('Accès refusé pour votre rôle.');
    }
    return $user;
}

function rediriger(string $page): never
{
    header('Location: index.php?page=' . rawurlencode($page));
    exit;
}
