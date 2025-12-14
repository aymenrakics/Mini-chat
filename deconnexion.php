<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    $conn = getConnection();
    $user_id = $_SESSION['user_id'];
    
    // Mettre à jour le statut hors ligne
    mysqli_query($conn, "UPDATE utilisateurs SET est_en_ligne = 0 WHERE id = $user_id");
    
    // Supprimer la session active
    mysqli_query($conn, "DELETE FROM sessions_actives WHERE user_id = $user_id");
    
    mysqli_close($conn);
}

// Détruire la session
session_destroy();

// Rediriger vers la page d'accueil
header('Location: index.php');
exit();
?>