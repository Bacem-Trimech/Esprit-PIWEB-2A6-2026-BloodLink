<?php
/**
 * config/database.php
 * Secure PDO connection, Singleton pattern (one connection shared by the whole application)
 */
class Database
{
    private static ?PDO $pdo = null;

    public static function getConnexion(): PDO
    {
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,   // real prepared statements (SQL injection protection)
                    ]
                );
            } catch (PDOException $e) {
                error_log($e->getMessage());
                http_response_code(500);
                exit('Database connection failed. Check config.php and that MySQL is running.');
            }
        }
        return self::$pdo;
    }
}
