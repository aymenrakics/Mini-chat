<?php
require_once 'config.php';

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || !$_SESSION['est_admin']) {
    header('Location: chat.php');
    exit();
}

$conn = getConnection();

// Nettoyer les sessions inactives
nettoyerSessionsInactives();

// Statistiques
$stats = [];

// Total utilisateurs
$result = $conn->query("SELECT COUNT(*) as total FROM utilisateurs");
$stats['total_users'] = $result->fetch_assoc()['total'];

// Utilisateurs en ligne
$result = $conn->query("SELECT COUNT(*) as total FROM sessions_actives 
                        WHERE derniere_activite > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
$stats['users_online'] = $result->fetch_assoc()['total'];

// Total messages
$result = $conn->query("SELECT COUNT(*) as total FROM messages");
$stats['total_messages'] = $result->fetch_assoc()['total'];

// Messages aujourd'hui
$result = $conn->query("SELECT COUNT(*) as total FROM messages WHERE DATE(date_message) = CURDATE()");
$stats['messages_today'] = $result->fetch_assoc()['total'];

// Récupérer tous les utilisateurs avec leur statut
$users = [];
$query = "SELECT u.id, u.pseudo, u.email, u.est_admin, 
          DATE_FORMAT(u.date_inscription, '%d/%m/%Y %H:%i') as date_inscription, 
          DATE_FORMAT(u.derniere_connexion, '%d/%m/%Y %H:%i') as derniere_connexion,
          CASE 
              WHEN s.derniere_activite > DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 1
              ELSE 0
          END as est_en_ligne
          FROM utilisateurs u
          LEFT JOIN sessions_actives s ON u.id = s.user_id
          ORDER BY u.id";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Données pour les graphiques
$graphData = [
    'messages_par_jour' => [],
    'top_users' => []
];

// Messages par jour (7 derniers jours)
$result = $conn->query("SELECT DATE(date_message) as jour, COUNT(*) as nombre 
                        FROM messages 
                        WHERE date_message >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
                        GROUP BY DATE(date_message) 
                        ORDER BY jour");
while ($row = $result->fetch_assoc()) {
    $graphData['messages_par_jour'][] = $row;
}

// Top 10 des utilisateurs les plus actifs
$result = $conn->query("SELECT pseudo, COUNT(*) as nombre 
                        FROM messages 
                        GROUP BY pseudo 
                        ORDER BY nombre DESC 
                        LIMIT 10");
while ($row = $result->fetch_assoc()) {
    $graphData['top_users'][] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panneau Admin - Mini Chat</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 15px rgba(0,0,0,0.2);
        }
        .header h1 { 
            font-size: 26px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-back {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-back:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 25px rgba(0,0,0,0.15);
        }
        .stat-icon {
            font-size: 45px;
            margin-bottom: 15px;
        }
        .stat-value {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }
        .stat-label {
            color: #666;
            font-size: 15px;
            font-weight: 600;
        }
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .chart-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .chart-card h2 {
            color: #667eea;
            margin-bottom: 25px;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .users-table {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        .users-table h2 {
            color: #667eea;
            margin-bottom: 25px;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        tr:hover {
            background: #f9fafb;
        }
        tbody tr {
            transition: all 0.3s;
        }
        .status-online {
            color: #48bb78;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .status-offline {
            color: #999;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .admin-badge {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #333;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .user-badge {
            background: #e0e0e0;
            color: #666;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚙️ Panneau d'Administration</h1>
        <a href="chat.php" class="btn-back">← Retour au Chat</a>
    </div>
    
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-value"><?= $stats['total_users'] ?></div>
                <div class="stat-label">Total Utilisateurs</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🟢</div>
                <div class="stat-value"><?= $stats['users_online'] ?></div>
                <div class="stat-label">Utilisateurs En Ligne</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💬</div>
                <div class="stat-value"><?= $stats['total_messages'] ?></div>
                <div class="stat-label">Total Messages</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-value"><?= $stats['messages_today'] ?></div>
                <div class="stat-label">Messages Aujourd'hui</div>
            </div>
        </div>
        
        <?php if (!empty($graphData['messages_par_jour']) || !empty($graphData['top_users'])): ?>
        <div class="charts-grid">
            <?php if (!empty($graphData['messages_par_jour'])): ?>
            <div class="chart-card">
                <h2>📊 Messages par Jour (7 derniers jours)</h2>
                <canvas id="chartMessages"></canvas>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($graphData['top_users'])): ?>
            <div class="chart-card">
                <h2>🏆 Top 10 Utilisateurs Actifs</h2>
                <canvas id="chartTopUsers"></canvas>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="users-table">
            <h2>👤 Liste des Utilisateurs (<?= count($users) ?>)</h2>
            <?php if (count($users) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pseudo</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Date Inscription</th>
                        <th>Dernière Connexion</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong>#<?= $user['id'] ?></strong></td>
                        <td><?= htmlspecialchars($user['pseudo']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <?php if ($user['est_admin']): ?>
                                <span class="admin-badge">⭐ ADMIN</span>
                            <?php else: ?>
                                <span class="user-badge">👤 Utilisateur</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $user['date_inscription'] ?></td>
                        <td><?= $user['derniere_connexion'] ?? '<em>Jamais connecté</em>' ?></td>
                        <td>
                            <?php if ($user['est_en_ligne']): ?>
                                <span class="status-online">🟢 En ligne</span>
                            <?php else: ?>
                                <span class="status-offline">⚫ Hors ligne</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-data">Aucun utilisateur enregistré</div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Graphique des messages par jour
        <?php if (!empty($graphData['messages_par_jour'])): ?>
        const messagesData = <?= json_encode($graphData['messages_par_jour']) ?>;
        const ctxMessages = document.getElementById('chartMessages').getContext('2d');
        new Chart(ctxMessages, {
            type: 'line',
            data: {
                labels: messagesData.map(d => d.jour),
                datasets: [{
                    label: 'Nombre de messages',
                    data: messagesData.map(d => d.nombre),
                    backgroundColor: 'rgba(102, 126, 234, 0.2)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { 
                        display: true,
                        labels: {
                            font: { size: 14 }
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        <?php endif; ?>
        
        // Graphique des top utilisateurs
        <?php if (!empty($graphData['top_users'])): ?>
        const topUsersData = <?= json_encode($graphData['top_users']) ?>;
        const ctxTopUsers = document.getElementById('chartTopUsers').getContext('2d');
        new Chart(ctxTopUsers, {
            type: 'bar',
            data: {
                labels: topUsersData.map(d => d.pseudo),
                datasets: [{
                    label: 'Nombre de messages',
                    data: topUsersData.map(d => d.nombre),
                    backgroundColor: 'rgba(118, 75, 162, 0.7)',
                    borderColor: 'rgba(118, 75, 162, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { 
                        display: true,
                        labels: {
                            font: { size: 14 }
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>