<?php

//recuperer les variables d'environnement
$host = getenv('DB_HOST');
$db   = getenv('DB_NAME') ;
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');

//construction du DSN et options pour PDO
$dsn = "mysql:host=$host;dbname=$db";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

//essayer de se connecter à la base de données
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("❌ Erreur de connexion : " . $e->getMessage());
}