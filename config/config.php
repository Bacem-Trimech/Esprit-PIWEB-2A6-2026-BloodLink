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
