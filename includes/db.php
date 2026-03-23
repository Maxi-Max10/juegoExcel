<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);

    $pdo = new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    $offset = (new DateTimeZone(date_default_timezone_get()))->getOffset(new DateTime());
    $sign   = $offset >= 0 ? '+' : '-';
    $hours  = str_pad((string) intdiv(abs($offset), 3600), 2, '0', STR_PAD_LEFT);
    $mins   = str_pad((string) (intdiv(abs($offset), 60) % 60), 2, '0', STR_PAD_LEFT);
    $pdo->exec("SET time_zone = '{$sign}{$hours}:{$mins}'");

    return $pdo;
}