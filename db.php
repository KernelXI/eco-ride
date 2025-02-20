<?php
// db.php

$url = parse_url(getenv("JAWSDB_URL"));

$host = $url["host"];
$username = $url["user"];
$password = $url["pass"];
$database = ltrim($url["path"], "/");

$dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
