<?php
require_once 'config.php';

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pseudo = trim($_POST['pseudo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmer_mdp = $_POST['confirmer_mdp'] ?? '';
    
    // Vérifications
    if (empty($pseudo) || empty($email) || empty($mot_de_passe)) {
        $erreur = "Tous les champs sont obligatoires !";
    } elseif (strlen($pseudo) < 3) {
        $erreur = "Le pseudo doit contenir au moins 3 caractères !";
    } elseif ($mot_de_passe !== $confirmer_mdp) {
        $erreur = "Les mots de passe ne correspondent pas !";
    } elseif (strlen($mot_de_passe) < 6) {
        $erreur = "Le mot de passe doit contenir au moins 6 caractères !";
    } elseif (!verifierEmailValide($email)) {
        $erreur = "L'email n'est pas valide ou le domaine n'existe pas !";
    } else {
        $conn = getConnection();
        
        // Vérifier si pseudo ou email existe déjà
        $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE pseudo = ? OR email = ?");
        $stmt->bind_param("ss", $pseudo, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $erreur = "Ce pseudo ou cet email existe déjà !";
        } else {
            // Vérifier si c'est le premier utilisateur (devient admin)
            $count_result = $conn->query("SELECT COUNT(*) as total FROM utilisateurs");
            $row = $count_result->fetch_assoc();
            $est_admin = ($row['total'] == 0) ? 1 : 0;
            
            // Insérer le nouvel utilisateur
            $mdp_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO utilisateurs (pseudo, email, mot_de_passe, est_admin) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $pseudo, $email, $mdp_hash, $est_admin);
            
            if ($stmt->execute()) {
                $succes = "Inscription réussie ! " . ($est_admin ? "🎉 Vous êtes le premier utilisateur, vous êtes maintenant ADMINISTRATEUR ! " : "") . "Vous pouvez maintenant vous connecter.";
            } else {
                $erreur = "Erreur lors de l'inscription : " . $conn->error;
            }
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
    <title>Inscription - Mini Chat</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.3);
            max-width: 450px;
            width: 100%;
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
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            font-weight: bold;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(72, 187, 120, 0.4);
        }
        .erreur {
            background: #fc8181;
            color: white;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: shake 0.5s;
        }
        .succes {
            background: #48bb78;
            color: white;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: slideIn 0.5s;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
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
        <h1>📝 Inscription</h1>
        
        <?php if ($erreur): ?>
            <div class="erreur">❌ <?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>
        
        <?php if ($succes): ?>
            <div class="succes">✅ <?= htmlspecialchars($succes) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Pseudo :</label>
                <input type="text" name="pseudo" value="<?= htmlspecialchars($_POST['pseudo'] ?? '') ?>" required minlength="3" placeholder="Votre pseudo">
            </div>
            
            <div class="form-group">
                <label>Email :</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required placeholder="votre@email.com">
            </div>
            
            <div class="form-group">
                <label>Mot de passe :</label>
                <input type="password" name="mot_de_passe" required minlength="6" placeholder="Au moins 6 caractères">
            </div>
            
            <div class="form-group">
                <label>Confirmer le mot de passe :</label>
                <input type="password" name="confirmer_mdp" required placeholder="Confirmer votre mot de passe">
            </div>
            
            <button type="submit" class="btn">S'inscrire</button>
        </form>
        
        <div class="lien">
            <a href="connexion.php">Déjà inscrit ? Se connecter</a>
        </div>
    </div>
</body>
</html>