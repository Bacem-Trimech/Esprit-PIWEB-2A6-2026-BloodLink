<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__.'/../config/config.php';
$nom = trim((string)($argv[1] ?? ''));
$email = trim((string)($argv[2] ?? ''));
$password = getenv('BLOODLINK_ADMIN_PASSWORD') ?: '';
if (mb_strlen($nom) < 2 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
    fwrite(STDERR, "Usage : définir BLOODLINK_ADMIN_PASSWORD (12 caractères minimum), puis php scripts/creer_admin.php \"Nom\" email@example.org\n");
    exit(1);
}
$q = connexion()->prepare("INSERT INTO utilisateurs (nom,email,mot_de_passe,role,actif) VALUES (?,?,?,'ADMIN',1)");
try {
    $q->execute([$nom,mb_strtolower($email),password_hash($password,PASSWORD_DEFAULT)]);
    echo "Compte administrateur créé.\n";
} catch (PDOException $error) {
    if ($error->getCode() === '23000') {
        fwrite(STDERR,"Adresse e-mail déjà utilisée.\n");
        exit(1);
    }
    throw $error;
}
