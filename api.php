<?php
// Désactiver l'affichage des erreurs (elles cassent le JSON)
error_reporting(0);
ini_set('display_errors', 0);

// Vider tout buffer de sortie
if (ob_get_level()) ob_end_clean();

require_once 'config.php';

// Header JSON AVANT tout autre output
header('Content-Type: application/json; charset=utf-8');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Non connecté'], JSON_UNESCAPED_UNICODE);
    exit();
}

$action = $_GET['action'] ?? '';

try {
    $conn = getConnection();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur base de données'], JSON_UNESCAPED_UNICODE);
    exit();
}

$user_id = intval($_SESSION['user_id']);
$pseudo = $_SESSION['pseudo'] ?? '';

// Mettre à jour l'activité de l'utilisateur à chaque requête
try {
    $stmt = $conn->prepare("INSERT INTO sessions_actives (user_id, derniere_activite) 
                            VALUES (?, NOW()) 
                            ON DUPLICATE KEY UPDATE derniere_activite = NOW()");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    
    $conn->query("UPDATE utilisateurs SET est_en_ligne = 1 WHERE id = $user_id");
} catch (Exception $e) {
    // Log l'erreur mais continue
    error_log("Erreur mise à jour activité: " . $e->getMessage());
}

switch ($action) {
    case 'send_message':
        $message = trim($_POST['message'] ?? '');
        
        if (empty($message)) {
            echo json_encode(['success' => false, 'error' => 'Message vide'], JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        if (strlen($message) > 1000) {
            echo json_encode(['success' => false, 'error' => 'Message trop long (max 1000 caractères)'], JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        // Éviter les doublons (même message envoyé dans les 3 dernières secondes)
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM messages 
                                WHERE user_id = ? AND message = ? 
                                AND date_message > DATE_SUB(NOW(), INTERVAL 3 SECOND)");
        $stmt->bind_param("is", $user_id, $message);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            echo json_encode(['success' => false, 'error' => 'Message déjà envoyé récemment'], JSON_UNESCAPED_UNICODE);
            $stmt->close();
            $conn->close();
            exit();
        }
        $stmt->close();
        
        // Nettoyer le message (protection XSS)
        $message_clean = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        
        // Insérer le message
        $stmt = $conn->prepare("INSERT INTO messages (user_id, pseudo, message, date_message) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $user_id, $pseudo, $message_clean);
        
        if ($stmt->execute()) {
            // Supprimer les vieux messages (garder seulement les 100 derniers)
            $conn->query("DELETE FROM messages 
                         WHERE id NOT IN (
                             SELECT * FROM (
                                 SELECT id FROM messages ORDER BY id DESC LIMIT 100
                             ) AS temp
                         )");
            
            echo json_encode(['success' => true, 'message' => 'Message envoyé'], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'envoi'], JSON_UNESCAPED_UNICODE);
        }
        $stmt->close();
        break;
        
    case 'get_messages':
        $page = max(1, intval($_GET['page'] ?? 1));
        $messagesPerPage = 10;
        $offset = ($page - 1) * $messagesPerPage;
        
        // Compter le total de messages
        $result = $conn->query("SELECT COUNT(*) as total FROM messages");
        $row = $result->fetch_assoc();
        $totalMessages = $row['total'];
        $totalPages = max(1, ceil($totalMessages / $messagesPerPage));
        
        // Récupérer les messages de la page demandée
        $stmt = $conn->prepare("SELECT pseudo, message, DATE_FORMAT(date_message, '%d/%m/%Y %H:%i:%s') as date_message 
                                FROM messages 
                                ORDER BY id DESC 
                                LIMIT ? OFFSET ?");
        $stmt->bind_param("ii", $messagesPerPage, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = [
                'pseudo' => $row['pseudo'],
                'message' => $row['message'],
                'date_message' => $row['date_message']
            ];
        }
        $stmt->close();
        
        // Inverser pour afficher du plus ancien au plus récent
        $messages = array_reverse($messages);
        
        echo json_encode([
            'success' => true,
            'messages' => $messages,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'total_messages' => $totalMessages
        ], JSON_UNESCAPED_UNICODE);
        break;
        
    case 'users_online':
        // Nettoyer d'abord les sessions inactives
        nettoyerSessionsInactives();
        
        // Récupérer les utilisateurs en ligne (DISTINCT pour éviter les doublons)
        $result = $conn->query("SELECT DISTINCT u.pseudo, u.est_admin 
                                FROM utilisateurs u
                                INNER JOIN sessions_actives s ON u.id = s.user_id
                                WHERE s.derniere_activite > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                                ORDER BY u.pseudo");
        
        $users = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $users[] = [
                    'pseudo' => $row['pseudo'],
                    'est_admin' => intval($row['est_admin'])
                ];
            }
        }
        
        echo json_encode([
            'success' => true,
            'users' => $users,
            'count' => count($users)
        ], JSON_UNESCAPED_UNICODE);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Action invalide'], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>