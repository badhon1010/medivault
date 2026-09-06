<?php
session_start();

function medivault_env(string $key, string $default = ''): string
{
    $value = getenv($key);
    if ($value === false || $value === '') {
       $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }

    return (string) $value;
}

$host = medivault_env('DB_HOST', 'localhost');
$db = medivault_env('DB_NAME', 'medivault');
$user = medivault_env('DB_USER', 'root');
$pass = medivault_env('DB_PASS', '');
$charset = medivault_env('DB_CHARSET', 'utf8mb4');

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int) $e->getCode());
}
?>