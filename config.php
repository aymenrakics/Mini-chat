<?php
// Démarrer la session en premier
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mini_chat');

// Connexion à la base de données avec gestion d'erreur
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        error_log("Erreur de connexion à la base de données : " . $conn->connect_error);
        die("Erreur de connexion à la base de données. Veuillez réessayer plus tard.");
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Fonction pour vérifier si l'email existe vraiment
function verifierEmailValide($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    $domain = substr(strrchr($email, "@"), 1);
    if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
        return false;
    }
    
    return true;
}

// Fonction pour nettoyer les sessions inactives
function nettoyerSessionsInactives() {
    $conn = getConnection();
    
    // Supprimer les sessions de plus de 5 minutes
    $conn->query("DELETE FROM sessions_actives WHERE derniere_activite < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
    
    // Mettre à jour le statut des utilisateurs
    $conn->query("UPDATE utilisateurs SET est_en_ligne = 0 WHERE id NOT IN (SELECT user_id FROM sessions_actives)");
    
    $conn->close();
}

// Fonction pour nettoyer le texte (protection XSS)
function cleanText($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Fonction pour vérifier si l'utilisateur est connecté
function verifierConnexion() {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        header('Location: connexion.php');
        exit();
    }
}

// Configuration des erreurs (à désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>