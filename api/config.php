<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'facebook');
define('DB_USER', 'root');
define('DB_PASS', '');

// je co à la base de données avec PDO je sais meme pas cest quoi on appelle comme ca
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    // Activation des erreurs PDO ah bro pourquoi vous voulez activer erreur
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Si la connexion échoue forcement il faut afficher message d'erreur
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>