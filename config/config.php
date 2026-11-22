<?php
declare(strict_types=1);

function connexion(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $host = getenv('BLOODLINK_DB_HOST') ?: '127.0.0.1';
        $name = getenv('BLOODLINK_DB_NAME') ?: 'bloodlink_piweb_2a6';
        $user = getenv('BLOODLINK_DB_USER') ?: 'root';
        $pass = getenv('BLOODLINK_DB_PASS') ?: '';
        $pdo = new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}
