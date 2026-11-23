<?php
declare(strict_types=1);

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnexion(): PDO
    {
        if (self::$pdo === null) {
            $host = getenv('BLOODLINK_DB_HOST') ?: '127.0.0.1';
            $name = getenv('BLOODLINK_DB_NAME') ?: 'bloodlink_piweb_2a6';
            $user = getenv('BLOODLINK_DB_USER') ?: 'root';
            $pass = getenv('BLOODLINK_DB_PASS') ?: '';
            self::$pdo = new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            try {
                self::$pdo->exec("ALTER TABLE collectes ADD COLUMN IF NOT EXISTS image_url VARCHAR(255) NULL AFTER statut");
                self::$pdo->exec("ALTER TABLE collectes ADD COLUMN IF NOT EXISTS galerie_json TEXT NULL AFTER image_url");
            } catch (Throwable) {
            }
        }
        return self::$pdo;
    }
}

final class Config extends Database {}

function connexion(): PDO
{
    return Database::getConnexion();
}

if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }

if (!isset($_SESSION['etablissements'])) {
    $_SESSION['etablissements'] = [
        ['id'=>1,'nom'=>'Hôpital de démonstration','type'=>'Hôpital','ville'=>'Tunis','adresse'=>'Tunis, Tunisie','telephone'=>'71000000','email'=>'contact@example.tn','description'=>'Établissement de démonstration.'],
        ['id'=>2,'nom'=>'Clinique de démonstration','type'=>'Clinique','ville'=>'Ariana','adresse'=>'Ariana, Tunisie','telephone'=>'72000000','email'=>'clinique@example.tn','description'=>'Deuxième établissement de démonstration.']
    ];
}

// Identifiants de démonstration uniquement. À remplacer par l'authentification
// commune de l'équipe avant toute utilisation réelle.
define('DEMO_ADMIN_USERNAME', 'admin');
define('DEMO_ADMIN_PASSWORD', 'BloodLink2026!');
