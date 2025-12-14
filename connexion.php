<?php
require_once 'config.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pseudo = trim($_POST['pseudo'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    
    if (empty($pseudo) || empty($mot_de_passe)) {
        $erreur = "Tous les champs sont obligatoires !";
    } else {
        $conn = getConnection();
        
        $stmt = $conn->prepare("SELECT id, pseudo, mot_de_passe, est_admin FROM utilisateurs WHERE pseudo = ?");
        $stmt->bind_param("s", $pseudo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (password_verify($mot_de_passe, $row['mot_de_passe'])) {
                // Connexion réussie
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['pseudo'] = $row['pseudo'];
                $_SESSION['est_admin'] = $row['est_admin'];
                
                $user_id = $row['id'];
                
                // Mettre à jour la dernière connexion et le statut en ligne
                $conn->query("UPDATE utilisateurs SET derniere_connexion = NOW(), est_en_ligne = 1 WHERE id = $user_id");
                
                // Ajouter ou mettre à jour la session active
                $conn->query("INSERT INTO sessions_actives (user_id, derniere_activite) VALUES ($user_id, NOW()) 
                             ON DUPLICATE KEY UPDATE derniere_activite = NOW()");
                
                $stmt->close();
                $conn->close();
                
                header('Location: chat.php');
                exit();
            } else {
                $erreur = "Mot de passe incorrect !";
            }
        } else {
            $erreur = "Utilisateur introuvable !";
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Mini Chat</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.3);
            max-width: 400px;
            width: 90%;
        }
        h1 {
            color: #667eea;
            margin-bottom: 30px;
            text-align: center;
            font-size: 28px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: bold;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .erreur {
            background: #fc8181;
            color: white;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: shake 0.5s;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        .lien {
            text-align: center;
            margin-top: 20px;
        }
        .lien a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .lien a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Connexion</h1>
        
        <?php if ($erreur): ?>
            <div class="erreur">❌ <?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Pseudo :</label>
                <input type="text" name="pseudo" value="<?= htmlspecialchars($_POST['pseudo'] ?? '') ?>" required placeholder="Votre pseudo">
            </div>
            
            <div class="form-group">
                <label>Mot de passe :</label>
                <input type="password" name="mot_de_passe" required placeholder="Votre mot de passe">
            </div>
            
            <button type="submit" class="btn">Se connecter</button>
        </form>
        
        <div class="lien">
            <a href="inscription.php">Pas encore inscrit ? S'inscrire</a>
        </div>
    </div>
</body>
</html>