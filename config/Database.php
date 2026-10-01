<?php
require_once __DIR__ . '/config.php';

try {
    $connectDB = new PDO(
        dsn: MARIA_DSN,
        username: DB_LOGIN,
        password: DB_PWD,
        options: [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]
    );

    $connectDB->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Une erreur est survenue. Veuillez réessayer plus tard.");
}