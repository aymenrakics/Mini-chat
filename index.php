<?php
require_once 'config.php';

// Si déjà connecté, rediriger vers le chat
if (isset($_SESSION['user_id'])) {
    header('Location: chat.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Chat - Accueil</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        h1 {
            color: #667eea;
            margin-bottom: 30px;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: white;
        }
        .btn-primary {
            background: #667eea;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .btn-secondary {
            background: #48bb78;
        }
        .btn-secondary:hover {
            background: #38a169;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Mini Chat</h1>
        <p style="margin-bottom: 30px; color: #666;">Bienvenue ! Connectez-vous ou inscrivez-vous pour commencer à chatter.</p>
        <a href="connexion.php" class="btn btn-primary">Se connecter</a>
        <a href="inscription.php" class="btn btn-secondary">S'inscrire</a>
    </div>
</body>
</html>