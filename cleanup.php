<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mini_chat');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

echo "<h1>🧹 Nettoyage de la Base de Données</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .ok{color:green;} .error{color:red;}</style>";

// 1. Supprimer les doublons dans sessions_actives
echo "<h2>1. Nettoyage des sessions actives</h2>";
$result = $conn->query("SELECT user_id, COUNT(*) as count FROM sessions_actives GROUP BY user_id HAVING count > 1");
if ($result && $result->num_rows > 0) {
    echo "<div class='error'>⚠️ Doublons détectés :</div>";
    while ($row = $result->fetch_assoc()) {
        echo "<div>- User ID " . $row['user_id'] . " : " . $row['count'] . " sessions</div>";
    }
    
    // Supprimer les doublons (garder seulement le plus récent)
    $conn->query("DELETE s1 FROM sessions_actives s1
                  INNER JOIN sessions_actives s2 
                  WHERE s1.id < s2.id AND s1.user_id = s2.user_id");
    
    echo "<div class='ok'>✅ Doublons supprimés</div>";
} else {
    echo "<div class='ok'>✅ Aucun doublon trouvé</div>";
}

// 2. Supprimer les sessions inactives (plus de 5 minutes)
echo "<h2>2. Suppression des sessions inactives</h2>";
$result = $conn->query("DELETE FROM sessions_actives WHERE derniere_activite < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
echo "<div class='ok'>✅ " . $conn->affected_rows . " session(s) inactive(s) supprimée(s)</div>";

// 3. Mettre à jour les statuts utilisateurs
echo "<h2>3. Mise à jour des statuts</h2>";
$conn->query("UPDATE utilisateurs SET est_en_ligne = 0");
$conn->query("UPDATE utilisateurs u 
              INNER JOIN sessions_actives s ON u.id = s.user_id 
              SET u.est_en_ligne = 1");
echo "<div class='ok'>✅ Statuts mis à jour</div>";

// 4. Statistiques
echo "<h2>4. Statistiques</h2>";
$result = $conn->query("SELECT COUNT(*) as total FROM utilisateurs");
$total_users = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM utilisateurs WHERE est_en_ligne = 1");
$users_online = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM messages");
$total_messages = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM sessions_actives");
$total_sessions = $result->fetch_assoc()['total'];

echo "<div class='ok'>👥 Utilisateurs : $total_users total, $users_online en ligne</div>";
echo "<div class='ok'>💬 Messages : $total_messages</div>";
echo "<div class='ok'>🔌 Sessions actives : $total_sessions</div>";

$conn->close();

echo "<h2>✅ Nettoyage terminé !</h2>";
echo "<p><a href='chat.php'>← Retour au chat</a> | <a href='debug.php'>🔍 Diagnostic</a></p>";
?>