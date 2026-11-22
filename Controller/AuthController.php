<?php
declare(strict_types=1);

final class AuthController
{
    public function inscription(array $post): array
    {
        verifierCsrf();
        $nom = trim((string)($post['nom'] ?? ''));
        $email = mb_strtolower(trim((string)($post['email'] ?? '')));
        $pass = (string)($post['mot_de_passe'] ?? '');
        $errors = [];
        if (mb_strlen($nom) < 2 || mb_strlen($nom) > 100) $errors[] = 'Le nom doit contenir 2 à 100 caractères.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) $errors[] = 'Adresse e-mail invalide.';
        if (strlen($pass) < 12) $errors[] = 'Le mot de passe doit contenir au moins 12 caractères.';
        if ($pass !== (string)($post['confirmation'] ?? '')) $errors[] = 'Les mots de passe ne correspondent pas.';
        if ($errors) return $errors;
        try {
            $q = connexion()->prepare('INSERT INTO utilisateurs (nom,email,mot_de_passe,role,actif) VALUES (?,?,?,"DONNEUR",1)');
            $q->execute([$nom, $email, password_hash($pass, PASSWORD_DEFAULT)]);
        } catch (PDOException $error) {
            if ($error->getCode() === '23000') return ['Cette adresse e-mail est déjà utilisée.'];
            throw $error;
        }
        return [];
    }

    public function connexion(array $post): string
    {
        verifierCsrf();
        $email = mb_strtolower(trim((string)($post['email'] ?? '')));
        $pass = (string)($post['mot_de_passe'] ?? '');
        $q = connexion()->prepare('SELECT id,mot_de_passe,role,actif FROM utilisateurs WHERE email = ?');
        $q->execute([$email]);
        $user = $q->fetch();
        if (!$user || !$user['actif'] || !password_verify($pass, $user['mot_de_passe'])) {
            return 'Identifiants incorrects ou compte désactivé.';
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        return '';
    }

    public function deconnexion(): never
    {
        verifierCsrf();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        rediriger('accueil');
    }
}
