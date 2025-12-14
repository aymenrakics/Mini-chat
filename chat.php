<?php
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
verifierConnexion();

$pseudo = $_SESSION['pseudo'];
$est_admin = $_SESSION['est_admin'];
$user_id = $_SESSION['user_id'];

// Nettoyer les sessions inactives et mettre à jour l'activité
nettoyerSessionsInactives();

$conn = getConnection();
$conn->query("INSERT INTO sessions_actives (user_id, derniere_activite) VALUES ($user_id, NOW()) 
             ON DUPLICATE KEY UPDATE derniere_activite = NOW()");
$conn->query("UPDATE utilisateurs SET est_en_ligne = 1 WHERE id = $user_id");
$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Chat - Bienvenue <?= htmlspecialchars($pseudo) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            flex-shrink: 0;
        }
        .header h1 { 
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        .admin-badge {
            background: #ffd700;
            color: #333;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .btn-logout, .btn-admin {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            transition: all 0.3s;
        }
        .btn-logout:hover, .btn-admin:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
        .container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }
        .sidebar {
            width: 280px;
            background: white;
            border-right: 2px solid #e0e0e0;
            padding: 20px;
            overflow-y: auto;
            flex-shrink: 0;
        }
        .sidebar h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .user-online {
            padding: 12px;
            margin: 8px 0;
            background: #f9fafb;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }
        .user-online:hover {
            background: #e6f0ff;
            transform: translateX(5px);
        }
        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #48bb78;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f9fafb;
            overflow: hidden;
        }
        #messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: linear-gradient(to bottom, #f9fafb, #ffffff);
        }
        .message {
            background: white;
            padding: 15px 20px;
            margin: 12px 0;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            animation: slideIn 0.3s ease-out;
            border-left: 4px solid #667eea;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .message-pseudo {
            font-weight: bold;
            color: #667eea;
            font-size: 15px;
        }
        .message-date {
            font-size: 12px;
            color: #999;
        }
        .message-text {
            color: #333;
            line-height: 1.6;
            font-size: 15px;
            word-wrap: break-word;
        }
        .form-container {
            padding: 20px;
            background: white;
            border-top: 2px solid #e0e0e0;
            flex-shrink: 0;
        }
        .form-group {
            display: flex;
            gap: 10px;
        }
        #message {
            flex: 1;
            padding: 14px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
        }
        #message:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn-send {
            padding: 14px 35px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
            transition: all 0.3s;
        }
        .btn-send:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-send:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .pagination {
            padding: 15px;
            text-align: center;
            background: white;
            border-top: 1px solid #e0e0e0;
            flex-shrink: 0;
        }
        .pagination a {
            padding: 8px 14px;
            margin: 0 5px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s;
        }
        .pagination a:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
        .pagination .active {
            background: #764ba2;
        }
        .date-time {
            text-align: center;
            padding: 8px 15px;
            background: rgba(255,255,255,0.2);
            color: white;
            font-weight: 600;
            border-radius: 8px;
            font-size: 13px;
        }
        .no-messages {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 16px;
        }
        .error-message {
            background: #fc8181;
            color: white;
            padding: 10px;
            border-radius: 8px;
            margin: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>💬 Mini Chat</h1>
        <div class="user-info">
            <div class="date-time" id="datetime"></div>
            <span>👤 <?= htmlspecialchars($pseudo) ?></span>
            <?php if ($est_admin): ?>
                <span class="admin-badge">⭐ ADMIN</span>
                <a href="admin.php" class="btn-admin">📊 Panneau Admin</a>
            <?php endif; ?>
            <a href="deconnexion.php" class="btn-logout">🚪 Déconnexion</a>
        </div>
    </div>
    
    <div class="container">
        <div class="sidebar">
            <h3>🟢 Utilisateurs en ligne</h3>
            <div id="users-online">
                <p style="color: #999; font-size: 14px;">Chargement...</p>
            </div>
        </div>
        
        <div class="chat-container">
            <div id="messages">
                <div class="no-messages">📭 Chargement des messages...</div>
            </div>
            
            <div class="pagination" id="pagination" style="display:none;"></div>
            
            <div class="form-container">
                <form id="chatForm" class="form-group">
                    <input type="text" id="message" placeholder="✍️ Tapez votre message..." required autocomplete="off" maxlength="1000">
                    <button type="submit" class="btn-send" id="btnSend">📤 Envoyer</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        const messagesPerPage = 10;
        let isLoading = false;
        let isSending = false;
        
        // Afficher la date et l'heure en temps réel
        function updateDateTime() {
            const now = new Date();
            const options = { 
                weekday: 'short', 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            document.getElementById('datetime').textContent = now.toLocaleDateString('fr-FR', options);
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);
        
        // Charger les utilisateurs en ligne
        function loadUsersOnline() {
            fetch('api.php?action=users_online')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                            console.error('Réponse non-JSON reçue:', text);
                            throw new Error('Réponse serveur invalide');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    const container = document.getElementById('users-online');
                    if (data.success && data.users && data.users.length > 0) {
                        container.innerHTML = data.users.map(user => `
                            <div class="user-online">
                                <div class="status-dot"></div>
                                <span><strong>${escapeHtml(user.pseudo)}</strong>${user.est_admin == 1 ? ' ⭐' : ''}</span>
                            </div>
                        `).join('');
                    } else {
                        container.innerHTML = '<p style="color: #999; font-size: 14px;">Aucun utilisateur en ligne</p>';
                    }
                })
                .catch(error => {
                    console.error('Erreur chargement utilisateurs:', error);
                });
        }
        
        // Échapper le HTML pour éviter les XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Charger les messages
        function loadMessages(page = 1) {
            if (isLoading) return;
            isLoading = true;
            
            fetch(`api.php?action=get_messages&page=${page}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const container = document.getElementById('messages');
                        
                        if (!data.messages || data.messages.length === 0) {
                            container.innerHTML = '<div class="no-messages">📭 Aucun message pour le moment. Soyez le premier à écrire !</div>';
                        } else {
                            container.innerHTML = data.messages.map(msg => `
                                <div class="message">
                                    <div class="message-header">
                                        <span class="message-pseudo">👤 ${escapeHtml(msg.pseudo)}</span>
                                        <span class="message-date">🕒 ${escapeHtml(msg.date_message)}</span>
                                    </div>
                                    <div class="message-text">${msg.message}</div>
                                </div>
                            `).join('');
                        }
                        
                        // Pagination
                        const pagination = document.getElementById('pagination');
                        if (data.total_pages > 1) {
                            let paginationHTML = '';
                            for (let i = 1; i <= data.total_pages; i++) {
                                paginationHTML += `<a href="#" class="${i === page ? 'active' : ''}" onclick="changePage(${i}); return false;">${i}</a>`;
                            }
                            pagination.innerHTML = paginationHTML;
                            pagination.style.display = 'block';
                        } else {
                            pagination.style.display = 'none';
                        }
                        
                        // Scroller vers le bas si on est sur la première page
                        if (page === 1) {
                            setTimeout(() => {
                                container.scrollTop = container.scrollHeight;
                            }, 100);
                        }
                    } else {
                        console.error('Erreur:', data.error);
                        if (data.error === 'Non connecté') {
                            window.location.href = 'connexion.php';
                        }
                    }
                    isLoading = false;
                })
                .catch(error => {
                    console.error('Erreur chargement messages:', error);
                    document.getElementById('messages').innerHTML = '<div class="error-message">❌ Erreur de connexion. Rafraîchissement...</div>';
                    isLoading = false;
                });
        }
        
        // Changer de page
        function changePage(page) {
            currentPage = page;
            loadMessages(page);
        }
        
        // Envoyer un message
        document.getElementById('chatForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (isSending) return;
            
            const messageInput = document.getElementById('message');
            const btnSend = document.getElementById('btnSend');
            const message = messageInput.value.trim();
            
            if (message === '') {
                alert('⚠️ Le message ne peut pas être vide !');
                return;
            }
            
            if (message.length > 1000) {
                alert('⚠️ Le message est trop long (max 1000 caractères) !');
                return;
            }
            
            isSending = true;
            btnSend.disabled = true;
            btnSend.textContent = '⏳ Envoi...';
            
            fetch('api.php?action=send_message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `message=${encodeURIComponent(message)}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                // Vérifier le Content-Type
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        console.error('Réponse non-JSON reçue:', text);
                        throw new Error('Réponse serveur invalide (pas JSON)');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    messageInput.value = '';
                    currentPage = 1;
                    loadMessages(1);
                } else {
                    alert('❌ ' + (data.error || 'Erreur lors de l\'envoi du message'));
                }
            })
            .catch(error => {
                console.error('Erreur envoi message:', error);
                alert('❌ Erreur de connexion');
            })
            .finally(() => {
                isSending = false;
                btnSend.disabled = false;
                btnSend.textContent = '📤 Envoyer';
            });
        });
        
        // Rafraîchir automatiquement toutes les 3 secondes
        setInterval(() => {
            if (!isSending) {
                loadMessages(currentPage);
                loadUsersOnline();
            }
        }, 3000);
        
        // Charger au démarrage
        loadMessages(1);
        loadUsersOnline();
    </script>
</body>
</html>