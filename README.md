# 💬 Mini Chat - Application de Messagerie en Temps Réel

[![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4.svg?logo=php)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1.svg?logo=mysql)](https://www.mysql.com/)
[![HTML5](https://img.shields.io/badge/HTML5-E34F26.svg?logo=html5&logoColor=white)](https://developer.mozilla.org/fr/docs/Web/HTML)
[![CSS3](https://img.shields.io/badge/CSS3-1572B6.svg?logo=css3&logoColor=white)](https://developer.mozilla.org/fr/docs/Web/CSS)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E.svg?logo=javascript&logoColor=black)](https://developer.mozilla.org/fr/docs/Web/JavaScript)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

Une application de messagerie instantanée moderne développée en PHP/MySQL avec rafraîchissement automatique, système d'authentification sécurisé, panneau d'administration et gestion des utilisateurs en ligne en temps réel.

## 📋 Table des matières

- [Présentation](#-présentation)
- [Fonctionnalités](#-fonctionnalités)
- [Architecture](#-architecture)
- [Technologies utilisées](#-technologies-utilisées)
- [Base de données](#-base-de-données)
- [Installation](#-installation)
- [Utilisation](#-utilisation)
- [Structure du projet](#-structure-du-projet)
- [Sécurité](#-sécurité)
- [Captures d'écran](#-captures-décran)
- [API](#-api)
- [Améliorations futures](#-améliorations-futures)
- [Contribution](#-contribution)
- [Auteur](#-auteur)
- [License](#-license)

## 🎯 Présentation

**Mini Chat** est une application web de messagerie instantanée développée dans le cadre du module **Bases de Données**. Ce projet illustre la mise en œuvre d'une architecture client-serveur complète avec :

- **Backend PHP** : Gestion de la logique métier et des interactions base de données
- **Base MySQL** : Stockage persistant des utilisateurs, messages et sessions
- **Frontend dynamique** : Interface réactive avec AJAX pour un rafraîchissement en temps réel
- **Système de sécurité** : Authentification, hashage des mots de passe, protection XSS/CSRF

L'objectif principal est de démontrer la maîtrise des concepts suivants :
- Conception et modélisation d'une base de données relationnelle
- Requêtes SQL avancées (CRUD, agrégations, jointures)
- Architecture MVC simplifiée en PHP
- Gestion des sessions et sécurisation des données
- API REST pour la communication asynchrone

## ✨ Fonctionnalités

### 🔐 Authentification et Gestion Utilisateurs

- ✅ **Inscription sécurisée** avec validation des emails (vérification DNS)
- ✅ **Connexion** avec hashage bcrypt des mots de passe
- ✅ **Système de rôles** : Utilisateur standard / Administrateur
- ✅ **Déconnexion** avec nettoyage des sessions actives
- ✅ **Premier utilisateur devient admin** automatiquement

### 💬 Chat en Temps Réel

- 📨 **Envoi de messages** instantané (limite 1000 caractères)
- 🔄 **Rafraîchissement automatique** toutes les 3 secondes
- 📄 **Pagination** des messages (10 messages par page)
- 🚫 **Protection anti-spam** : Détection des doublons (3 secondes)
- 🧹 **Nettoyage automatique** : Conservation des 100 derniers messages
- 🛡️ **Sécurité XSS** : Échappement automatique du HTML

### 👥 Utilisateurs en Ligne

- 🟢 **Détection en temps réel** des utilisateurs connectés
- ⏱️ **Timeout de 5 minutes** pour les sessions inactives
- 👤 **Affichage du statut** : En ligne / Hors ligne
- ⭐ **Badge administrateur** pour les admins

### 📊 Panneau d'Administration

- 📈 **Statistiques en temps réel** :
  - Total utilisateurs
  - Utilisateurs en ligne
  - Total messages
  - Messages du jour
- 📊 **Graphiques interactifs** (Chart.js) :
  - Messages par jour (7 derniers jours)
  - Top 10 utilisateurs actifs
- 📋 **Tableau détaillé** des utilisateurs :
  - ID, pseudo, email, rôle
  - Date d'inscription
  - Dernière connexion
  - Statut actuel

### 🛠️ Outils de Maintenance

- 🧹 **Script de nettoyage** (`cleanup.php`) :
  - Suppression des doublons de sessions
  - Suppression des sessions inactives
  - Mise à jour des statuts utilisateurs
- 🔍 **Script de diagnostic** (`debug.php`) :
  - Affichage des données brutes
  - Vérification de l'intégrité de la base

## 🏗️ Architecture

### Schéma Général

```
┌─────────────────────────────────────────────────────────────┐
│              ARCHITECTURE MINI CHAT                          │
└─────────────────────────────────────────────────────────────┘

┌────────────────┐
│   NAVIGATEUR   │  (Frontend)
│   HTML/CSS/JS  │
└────────┬───────┘
         │
         │ HTTP/AJAX
         ▼
┌────────────────┐
│  SERVEUR PHP   │  (Backend)
│  ├─ index.php  │  ──► Page d'accueil
│  ├─ inscription.php  ──► Formulaire inscription
│  ├─ connexion.php    ──► Formulaire connexion
│  ├─ chat.php         ──► Interface de chat
│  ├─ admin.php        ──► Panneau admin
│  ├─ api.php          ──► API REST
│  ├─ deconnexion.php  ──► Déconnexion
│  ├─ cleanup.php      ──► Maintenance
│  └─ config.php       ──► Configuration
└────────┬───────┘
         │
         │ MySQLi
         ▼
┌────────────────┐
│  BASE MYSQL    │  (Persistance)
│  ├─ utilisateurs      ──► Comptes utilisateurs
│  ├─ messages          ──► Historique chat
│  └─ sessions_actives  ──► Sessions en cours
└────────────────┘
```

### Flux d'Authentification

```
[Utilisateur] → inscription.php
                     │
                     ├─► Validation des données
                     ├─► Vérification unicité pseudo/email
                     ├─► Hashage bcrypt du mot de passe
                     └─► INSERT INTO utilisateurs
                              │
                              ▼
                     [Compte créé ✓]

[Utilisateur] → connexion.php
                     │
                     ├─► SELECT utilisateur par pseudo
                     ├─► Vérification password_verify()
                     ├─► Création de $_SESSION
                     ├─► INSERT/UPDATE sessions_actives
                     └─► UPDATE derniere_connexion
                              │
                              ▼
                     [Redirige vers chat.php]
```

### Flux de Messagerie

```
[Chat.php] ───┐
              │
              ├─► Chargement initial
              │      └─► api.php?action=get_messages
              │              └─► SELECT messages + pagination
              │
              ├─► Rafraîchissement auto (3s)
              │      ├─► api.php?action=get_messages
              │      └─► api.php?action=users_online
              │
              └─► Envoi de message
                     └─► api.php?action=send_message
                            ├─► Validation longueur
                            ├─► Vérification anti-spam
                            ├─► Échappement HTML
                            ├─► INSERT INTO messages
                            └─► Nettoyage (garder 100 derniers)
```

## 🛠️ Technologies utilisées

### Backend

| Technologie | Version | Utilisation |
|------------|---------|-------------|
| **PHP** | 7.4+ | Langage serveur principal |
| **MySQL** | 8.0+ | Base de données relationnelle |
| **MySQLi** | Extension PHP | Connexion et requêtes DB |
| **Sessions PHP** | Native | Gestion de l'authentification |

### Frontend

| Technologie | Version | Utilisation |
|------------|---------|-------------|
| **HTML5** | - | Structure des pages |
| **CSS3** | - | Styles et animations |
| **JavaScript Vanilla** | ES6+ | Logique client et AJAX |
| **Chart.js** | 3.9.1 | Graphiques interactifs |

### Sécurité

- **bcrypt** : Hashage des mots de passe (via `password_hash()`)
- **htmlspecialchars()** : Protection XSS
- **Prepared Statements** : Protection injections SQL
- **Validation DNS** : Vérification existence emails
- **Session timeout** : Expiration automatique après 5 minutes d'inactivité

## 🗄️ Base de données

### Schéma de la Base de Données

```sql
mini_chat
├── utilisateurs
│   ├── id (PK, INT, AUTO_INCREMENT)
│   ├── pseudo (VARCHAR(50), UNIQUE)
│   ├── email (VARCHAR(100), UNIQUE)
│   ├── mot_de_passe (VARCHAR(255))
│   ├── est_admin (TINYINT(1), DEFAULT 0)
│   ├── date_inscription (DATETIME, DEFAULT CURRENT_TIMESTAMP)
│   ├── derniere_connexion (DATETIME, NULL)
│   └── est_en_ligne (TINYINT(1), DEFAULT 0)
│
├── messages
│   ├── id (PK, INT, AUTO_INCREMENT)
│   ├── user_id (FK → utilisateurs.id)
│   ├── pseudo (VARCHAR(50))
│   ├── message (TEXT)
│   └── date_message (DATETIME, DEFAULT CURRENT_TIMESTAMP)
│
└── sessions_actives
    ├── id (PK, INT, AUTO_INCREMENT)
    ├── user_id (FK → utilisateurs.id, UNIQUE)
    └── derniere_activite (DATETIME, DEFAULT CURRENT_TIMESTAMP)
```

### Relations

```
utilisateurs (1) ──────< (N) messages
     │
     │
     │ (1)
     │
     └──────────────< (1) sessions_actives
```

### Script de Création

```sql
CREATE DATABASE IF NOT EXISTS mini_chat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mini_chat;

-- Table des utilisateurs
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pseudo VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    est_admin TINYINT(1) DEFAULT 0,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion DATETIME NULL,
    est_en_ligne TINYINT(1) DEFAULT 0,
    INDEX idx_pseudo (pseudo),
    INDEX idx_email (email),
    INDEX idx_est_en_ligne (est_en_ligne)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des messages
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pseudo VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    date_message DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_date_message (date_message)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des sessions actives
CREATE TABLE sessions_actives (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    derniere_activite DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_derniere_activite (derniere_activite)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Requêtes SQL Clés

#### 1. Récupération des messages avec pagination

```sql
SELECT pseudo, message, DATE_FORMAT(date_message, '%d/%m/%Y %H:%i:%s') as date_message 
FROM messages 
ORDER BY id DESC 
LIMIT 10 OFFSET 0;
```

#### 2. Utilisateurs en ligne

```sql
SELECT DISTINCT u.pseudo, u.est_admin 
FROM utilisateurs u
INNER JOIN sessions_actives s ON u.id = s.user_id
WHERE s.derniere_activite > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
ORDER BY u.pseudo;
```

#### 3. Statistiques administrateur

```sql
-- Total messages du jour
SELECT COUNT(*) as total 
FROM messages 
WHERE DATE(date_message) = CURDATE();

-- Top 10 utilisateurs actifs
SELECT pseudo, COUNT(*) as nombre 
FROM messages 
GROUP BY pseudo 
ORDER BY nombre DESC 
LIMIT 10;

-- Messages par jour (7 derniers jours)
SELECT DATE(date_message) as jour, COUNT(*) as nombre 
FROM messages 
WHERE date_message >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
GROUP BY DATE(date_message) 
ORDER BY jour;
```

#### 4. Nettoyage des sessions inactives

```sql
-- Supprimer les sessions de plus de 5 minutes
DELETE FROM sessions_actives 
WHERE derniere_activite < DATE_SUB(NOW(), INTERVAL 5 MINUTE);

-- Mettre à jour les statuts
UPDATE utilisateurs SET est_en_ligne = 0 
WHERE id NOT IN (SELECT user_id FROM sessions_actives);
```

#### 5. Anti-spam (détection doublons)

```sql
SELECT COUNT(*) as count 
FROM messages 
WHERE user_id = ? AND message = ? 
AND date_message > DATE_SUB(NOW(), INTERVAL 3 SECOND);
```

## 📦 Installation

### Prérequis

- **Serveur web** : Apache 2.4+ ou Nginx
- **PHP** : Version 7.4 ou supérieure
- **MySQL** : Version 8.0 ou supérieure (ou MariaDB 10.4+)
- **Extensions PHP** :
  - `mysqli` (activée par défaut)
  - `session` (activée par défaut)
  - `json` (activée par défaut)

### Vérification des prérequis

```bash
# Vérifier la version PHP
php -v

# Vérifier les extensions PHP
php -m | grep -E 'mysqli|session|json'

# Vérifier MySQL
mysql --version
```

### 1. Cloner le dépôt

```bash
git clone https://github.com/votre-username/mini-chat.git
cd mini-chat
```

### 2. Configuration de la base de données

#### Option A : Via phpMyAdmin

1. Ouvrez **phpMyAdmin** dans votre navigateur
2. Cliquez sur **Nouveau** pour créer une base de données
3. Nom : `mini_chat`
4. Encodage : `utf8mb4_unicode_ci`
5. Cliquez sur **SQL** et collez le contenu de `database.sql`
6. Cliquez sur **Exécuter**

#### Option B : Via ligne de commande

```bash
# Se connecter à MySQL
mysql -u root -p

# Créer la base de données
CREATE DATABASE mini_chat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mini_chat;

# Importer le schéma
source database.sql;

# Ou en une ligne depuis le terminal
mysql -u root -p mini_chat < database.sql
```

### 3. Configuration de l'application

Modifiez le fichier `config.php` avec vos identifiants MySQL :

```php
<?php
// config.php

// Configuration de la base de données
define('DB_HOST', 'localhost');      // Hôte MySQL
define('DB_USER', 'root');           // Utilisateur MySQL
define('DB_PASS', '');               // Mot de passe MySQL (vide par défaut avec XAMPP/WAMP)
define('DB_NAME', 'mini_chat');      // Nom de la base de données

// ... reste du code ...
?>
```

### 4. Déployer l'application

#### Avec XAMPP (Windows/Mac/Linux)

1. Copiez le dossier `mini-chat` dans :
   - **Windows** : `C:\xampp\htdocs\`
   - **Mac/Linux** : `/opt/lampp/htdocs/`

2. Démarrez **Apache** et **MySQL** depuis le panneau XAMPP

3. Ouvrez votre navigateur : `http://localhost/mini-chat/`

#### Avec WAMP (Windows)

1. Copiez le dossier dans `C:\wamp64\www\`
2. Démarrez tous les services WAMP
3. Ouvrez : `http://localhost/mini-chat/`

#### Avec MAMP (Mac)

1. Copiez le dossier dans `/Applications/MAMP/htdocs/`
2. Démarrez les serveurs MAMP
3. Ouvrez : `http://localhost:8888/mini-chat/`

### 5. Vérifier l'installation

1. Accédez à `http://localhost/mini-chat/`
2. Vous devriez voir la page d'accueil avec deux boutons :
   - **Se connecter**
   - **S'inscrire**

### 6. Créer le premier compte (Administrateur)

1. Cliquez sur **S'inscrire**
2. Remplissez le formulaire :
   - Pseudo : `admin` (par exemple)
   - Email : `admin@example.com`
   - Mot de passe : Choisissez un mot de passe sécurisé
3. Soumettez le formulaire
4. ✅ **Le premier utilisateur devient automatiquement administrateur !**

## 💻 Utilisation

### Pour les Utilisateurs

#### 1. Inscription

```
http://localhost/mini-chat/inscription.php
```

- Entrez un **pseudo unique** (minimum 3 caractères)
- Entrez un **email valide** (la vérification DNS est activée)
- Choisissez un **mot de passe** (minimum 6 caractères)
- Confirmez le mot de passe

#### 2. Connexion

```
http://localhost/mini-chat/connexion.php
```

- Entrez votre **pseudo**
- Entrez votre **mot de passe**
- Cliquez sur **Se connecter**

#### 3. Utiliser le Chat

Une fois connecté, vous êtes redirigé vers `chat.php` :

**Interface de Chat :**
- 💬 **Zone de messages** : Affiche les 10 derniers messages (paginés)
- 👥 **Barre latérale** : Liste des utilisateurs en ligne
- ✍️ **Zone de saisie** : Tapez votre message (max 1000 caractères)
- 📤 **Bouton Envoyer** : Publier votre message

**Fonctionnalités :**
- ✅ Rafraîchissement automatique toutes les 3 secondes
- ✅ Pagination des messages (navigation en bas)
- ✅ Horodatage précis de chaque message
- ✅ Badge **⭐ ADMIN** visible pour les administrateurs

#### 4. Déconnexion

Cliquez sur **🚪 Déconnexion** dans l'en-tête pour :
- Supprimer votre session active
- Mettre votre statut à "Hors ligne"
- Retourner à la page d'accueil

### Pour les Administrateurs

#### Accès au Panneau Admin

```
http://localhost/mini-chat/admin.php
```

**Conditions d'accès :**
- ✅ Être connecté
- ✅ Avoir le rôle `est_admin = 1`

**Sections du Panneau :**

##### 1. Statistiques en Temps Réel

```
┌─────────────────────────────────────┐
│  👥 Total Utilisateurs      |  42   │
│  🟢 Utilisateurs En Ligne   |  5    │
│  💬 Total Messages          | 1,523 │
│  📅 Messages Aujourd'hui    |  87   │
└─────────────────────────────────────┘
```

##### 2. Graphiques Interactifs

**Messages par Jour (7 derniers jours)**
- Graphique linéaire montrant l'activité quotidienne
- Identification des pics d'activité

**Top 10 Utilisateurs Actifs**
- Diagramme en barres des utilisateurs les plus actifs
- Nombre de messages par utilisateur

##### 3. Tableau des Utilisateurs

| ID | Pseudo | Email | Rôle | Inscription | Dernière Connexion | Statut |
|----|--------|-------|------|-------------|-------------------|--------|
| #1 | admin | admin@example.com | ⭐ ADMIN | 15/12/2024 10:30 | 16/12/2024 14:25 | 🟢 En ligne |
| #2 | alice | alice@example.com | 👤 Utilisateur | 15/12/2024 11:15 | 16/12/2024 09:42 | ⚫ Hors ligne |

**Colonnes :**
- **ID** : Identifiant unique
- **Pseudo** : Nom d'utilisateur
- **Email** : Adresse email
- **Rôle** : Administrateur ou Utilisateur standard
- **Date Inscription** : Date de création du compte
- **Dernière Connexion** : Dernière activité enregistrée
- **Statut** : En ligne (actif dans les 5 dernières minutes) / Hors ligne

## 📁 Structure du projet

```
mini-chat/
│
├── index.php                  # Page d'accueil (redirection connexion/inscription)
├── inscription.php            # Formulaire d'inscription
├── connexion.php              # Formulaire de connexion
├── chat.php                   # Interface principale de chat
├── admin.php                  # Panneau d'administration (accès admin uniquement)
├── deconnexion.php            # Script de déconnexion
├── config.php                 # Configuration DB + fonctions utilitaires
├── api.php                    # API REST pour AJAX (send_message, get_messages, users_online)
├── cleanup.php                # Script de maintenance (nettoyage sessions/doublons)
├── debug.php                  # Script de diagnostic (optionnel)
├── database.sql               # Schéma de la base de données
├── README.md                  # Documentation (ce fichier)
├── LICENSE                    # Licence MIT
│
└── figures/                   # Dossier pour captures d'écran (optionnel)
    ├── inscription.png
    ├── chat.png
    ├── admin.png
    └── architecture.png
```

### Description des Fichiers Principaux

#### `config.php` - Configuration Centrale

```php
<?php
session_start();                        // Démarre les sessions
define('DB_HOST', 'localhost');         // Constantes de connexion DB
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mini_chat');

function getConnection() { ... }        // Retourne une connexion MySQLi
function verifierEmailValide($email) { ... }  // Validation DNS
function nettoyerSessionsInactives() { ... }  // Supprime sessions expirées
function cleanText($text) { ... }       // Protection XSS
function verifierConnexion() { ... }    // Vérifie si utilisateur connecté
?>
```

#### `api.php` - API REST

**Actions disponibles :**

| Action | Méthode | Paramètres | Retour |
|--------|---------|------------|--------|
| `send_message` | POST | `message` (string) | `{success: bool, error?: string}` |
| `get_messages` | GET | `page` (int) | `{success: bool, messages: array, total_pages: int}` |
| `users_online` | GET | - | `{success: bool, users: array, count: int}` |

**Exemple d'utilisation (JavaScript) :**

```javascript
// Envoyer un message
fetch('api.php?action=send_message', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `message=${encodeURIComponent(message)}`
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Message envoyé !');
    }
});

// Récupérer les messages
fetch('api.php?action=get_messages&page=1')
    .then(response => response.json())
    .then(data => {
        console.log(data.messages);
    });

// Liste des utilisateurs en ligne
fetch('api.php?action=users_online')
    .then(response => response.json())
    .then(data => {
        console.log(`${data.count} utilisateurs en ligne`);
    });
```

#### `chat.php` - Interface de Chat

**Composants :**

```html
<div class="header">
    <!-- En-tête avec pseudo, date/heure, boutons admin/déconnexion -->
</div>

<div class="container">
    <div class="sidebar">
        <!-- Liste des utilisateurs en ligne -->
        <div id="users-online"></div>
    </div>
    
    <div class="chat-container">
        <div id="messages">
            <!-- Zone d'affichage des messages -->
        </div>
        
        <div class="pagination" id="pagination">
            <!-- Navigation entre pages -->
        </div>
        
        <form id="chatForm">
            <!-- Zone de saisie + bouton Envoyer -->
        </form>
    </div>
</div>
```

**JavaScript clé :**

```javascript
// Rafraîchissement automatique toutes les 3 secondes
setInterval(() => {
    if (!isSending) {
        loadMessages(currentPage);
        loadUsersOnline();
    }
}, 3000);
```

## 🔒 Sécurité

### Mesures Implémentées

#### 1. Protection des Mots de Passe

```php
// Lors de l'inscription
$mdp_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
// Génère un hash bcrypt avec salt automatique

// Lors de la connexion
if (password_verify($mot_de_passe_saisi, $mdp_hash_stocke)) {
    // Authentification réussie
}
```

**Caractéristiques :**
- ✅ Algorithme : **bcrypt** (BLOWFISH)
- ✅ Cost factor : 10 par défaut (2^10 itérations)
- ✅ Salt automatique unique par mot de passe
- ✅ Résistant aux attaques par rainbow tables

#### 2. Protection contre les Injections SQL

```php
// ❌ MAUVAIS (vulnérable)
$query = "SELECT * FROM utilisateurs WHERE pseudo = '$pseudo'";

// ✅ BON (sécurisé avec prepared statements)
$stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE pseudo = ?");
$stmt->bind_param("s", $pseudo);
$stmt->execute();
```

**Toutes les requêtes utilisent des prepared statements.**

#### 3. Protection XSS (Cross-Site Scripting)

```php
// Échappement systématique avant affichage
echo htmlspecialchars($pseudo, ENT_QUOTES, 'UTF-8');

// Dans api.php, avant insertion
$message_clean = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
```

**Résultat :**
```
Entrée : <script>alert('XSS')</script>
Stockage : &lt;script&gt;alert('XSS')&lt;/script&gt;
Affichage : <script>alert('XSS')</script> (sans exécution)
```

#### 4. Validation des Emails

```php
function verifierEmailValide($email) {
    // 1. Validation du format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // 2. Vérification DNS du domaine
    $domain = substr(strrchr($email, "@"), 1);
    if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
        return false;
    }
    
    return true;
}
```

**Avantage** : Empêche les inscriptions avec des emails inexistants.

#### 5. Protection CSRF (Cross-Site Request Forgery)

**Mesure de base :**
- ✅ Vérification de `$_SESSION['user_id']` dans `api.php`
- ✅ Pas d'actions critiques accessibles via GET simple

**Amélioration recommandée :**

```php
// Générer un token CSRF à la connexion
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Inclure dans les formulaires
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

// Vérifier lors de la soumission
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Requête invalide');
}
```

#### 6. Gestion des Sessions

```php
// Expiration automatique après 5 minutes d'inactivité
function nettoyerSessionsInactives() {
    $conn = getConnection();
    
    // Supprimer les sessions expirées
    $conn->query("DELETE FROM sessions_actives 
                  WHERE derniere_activite < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
    
    // Mettre à jour les statuts
    $conn->query("UPDATE utilisateurs SET est_en_ligne = 0 
                  WHERE id NOT IN (SELECT user_id FROM sessions_actives)");
    
    $conn->close();
}
```

**Caractéristiques :**
- ✅ Timeout : 5 minutes
- ✅ Nettoyage automatique à chaque requête
- ✅ Mise à jour du statut utilisateur

#### 7. Protection Anti-Spam

```php
// Vérifier si le même message a été envoyé dans les 3 dernières secondes
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM messages 
                        WHERE user_id = ? AND message = ? 
                        AND date_message > DATE_SUB(NOW(), INTERVAL 3 SECOND)");
$stmt->bind_param("is", $user_id, $message);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    echo json_encode(['success' => false, 'error' => 'Message déjà envoyé récemment']);
    exit();
}
```

**Avantages :**
- 🚫 Empêche le spam de messages identiques
- 🚫 Limite les envois accidentels multiples
- ⏱️ Fenêtre de détection : 3 secondes

#### 8. Validation des Données

**Côté serveur :**

```php
// Inscription
if (strlen($pseudo) < 3) {
    $erreur = "Le pseudo doit contenir au moins 3 caractères !";
}
if (strlen($mot_de_passe) < 6) {
    $erreur = "Le mot de passe doit contenir au moins 6 caractères !";
}

// Messages
if (strlen($message) > 1000) {
    echo json_encode(['success' => false, 'error' => 'Message trop long']);
    exit();
}
```

**Côté client :**

```html
<input type="text" name="pseudo" required minlength="3">
<input type="password" name="mot_de_passe" required minlength="6">
<input type="text" id="message" required maxlength="1000">
```

### Recommandations de Sécurité Supplémentaires

#### En Production

1. **Désactiver l'affichage des erreurs**
```php
// config.php
error_reporting(0);
ini_set('display_errors', 0);
```

2. **Utiliser HTTPS**
```apache
# .htaccess
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

3. **Configurer les en-têtes de sécurité**
```php
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Content-Security-Policy: default-src 'self'");
```

4. **Limiter les tentatives de connexion**
```php
// Implémenter un compteur de tentatives échouées
// Bloquer l'IP après 5 tentatives en 15 minutes
```

5. **Journaliser les événements sensibles**
```php
error_log("Tentative de connexion échouée pour : $pseudo depuis $ip");
```

## 📸 Captures d'écran

### Page d'Accueil
![Accueil](figures/accueil.png)
*Page d'accueil avec options de connexion et inscription*

### Inscription
![Inscription](figures/inscription.png)
*Formulaire d'inscription avec validation des champs*

### Interface de Chat
![Chat](figures/chat.png)
*Interface principale avec zone de messages, liste des utilisateurs en ligne et zone de saisie*

### Panneau d'Administration
![Admin Dashboard](figures/admin.png)
*Tableau de bord administrateur avec statistiques, graphiques et liste des utilisateurs*

### Statistiques en Temps Réel
![Statistiques](figures/stats.png)
*Cartes de statistiques avec graphiques Chart.js interactifs*

## 🔌 API

### Documentation de l'API REST

#### Base URL
```
http://localhost/mini-chat/api.php
```

#### 1. Envoyer un Message

**Endpoint :** `POST /api.php?action=send_message`

**Paramètres (form-data) :**

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `message` | string | Oui | Contenu du message (max 1000 caractères) |

**Réponse (JSON) :**

```json
{
    "success": true,
    "message": "Message envoyé"
}
```

**Erreurs possibles :**

```json
{
    "success": false,
    "error": "Non connecté"
}
// Ou
{
    "success": false,
    "error": "Message vide"
}
// Ou
{
    "success": false,
    "error": "Message trop long (max 1000 caractères)"
}
// Ou
{
    "success": false,
    "error": "Message déjà envoyé récemment"
}
```

**Exemple d'utilisation :**

```javascript
fetch('api.php?action=send_message', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `message=${encodeURIComponent('Bonjour tout le monde !')}`
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Message envoyé avec succès');
    } else {
        console.error('Erreur:', data.error);
    }
});
```

#### 2. Récupérer les Messages

**Endpoint :** `GET /api.php?action=get_messages`

**Paramètres (query string) :**

| Paramètre | Type | Requis | Défaut | Description |
|-----------|------|--------|--------|-------------|
| `page` | integer | Non | 1 | Numéro de page (10 messages par page) |

**Réponse (JSON) :**

```json
{
    "success": true,
    "messages": [
        {
            "pseudo": "alice",
            "message": "Bonjour !",
            "date_message": "16/12/2024 14:25:30"
        },
        {
            "pseudo": "bob",
            "message": "Salut Alice !",
            "date_message": "16/12/2024 14:26:15"
        }
    ],
    "total_pages": 5,
    "current_page": 1,
    "total_messages": 42
}
```

**Exemple d'utilisation :**

```javascript
fetch('api.php?action=get_messages&page=1')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            data.messages.forEach(msg => {
                console.log(`${msg.pseudo}: ${msg.message} (${msg.date_message})`);
            });
        }
    });
```

#### 3. Liste des Utilisateurs en Ligne

**Endpoint :** `GET /api.php?action=users_online`

**Paramètres :** Aucun

**Réponse (JSON) :**

```json
{
    "success": true,
    "users": [
        {
            "pseudo": "admin",
            "est_admin": 1
        },
        {
            "pseudo": "alice",
            "est_admin": 0
        },
        {
            "pseudo": "bob",
            "est_admin": 0
        }
    ],
    "count": 3
}
```

**Exemple d'utilisation :**

```javascript
fetch('api.php?action=users_online')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log(`${data.count} utilisateur(s) en ligne`);
            data.users.forEach(user => {
                const badge = user.est_admin == 1 ? ' ⭐' : '';
                console.log(`- ${user.pseudo}${badge}`);
            });
        }
    });
```

### Codes d'Erreur

| Code | Description | Action recommandée |
|------|-------------|--------------------|
| `Non connecté` | Session expirée ou invalide | Rediriger vers connexion.php |
| `Action invalide` | Action non reconnue | Vérifier le paramètre `action` |
| `Message vide` | Message vide soumis | Valider côté client |
| `Message trop long` | Message > 1000 caractères | Tronquer ou alerter |
| `Message déjà envoyé` | Doublon détecté (< 3s) | Attendre avant de réessayer |
| `Erreur base de données` | Problème de connexion DB | Vérifier config.php |

## 🚀 Améliorations futures

### Fonctionnalités à Implémenter

#### 1. Messagerie Privée
```sql
-- Nouvelle table pour les conversations privées
CREATE TABLE messages_prives (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expediteur_id INT NOT NULL,
    destinataire_id INT NOT NULL,
    message TEXT NOT NULL,
    lu TINYINT(1) DEFAULT 0,
    date_message DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expediteur_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (destinataire_id) REFERENCES utilisateurs(id)
);
```

#### 2. Notifications en Temps Réel
```javascript
// Utiliser WebSockets ou Server-Sent Events
const eventSource = new EventSource('notifications.php');
eventSource.onmessage = function(event) {
    const data = JSON.parse(event.data);
    afficherNotification(data);
};
```

#### 3. Emojis et Formatage
```javascript
// Intégrer une bibliothèque d'emojis
import EmojiPicker from 'emoji-picker-element';
```

#### 4. Upload de Fichiers/Images
```php
// Nouvelle colonne dans messages
ALTER TABLE messages ADD COLUMN fichier VARCHAR(255) NULL;

// Gestion des uploads
if ($_FILES['fichier']['size'] <= 5*1024*1024) { // Max 5 MB
    move_uploaded_file($_FILES['fichier']['tmp_name'], 'uploads/');
}
```

#### 5. Recherche de Messages
```sql
-- Ajouter un index full-text
ALTER TABLE messages ADD FULLTEXT INDEX idx_message_fulltext (message);

-- Requête de recherche
SELECT * FROM messages 
WHERE MATCH(message) AGAINST(? IN BOOLEAN MODE);
```

#### 6. Système de Modération
```sql
-- Ajouter des rôles supplémentaires
ALTER TABLE utilisateurs ADD COLUMN role ENUM('user', 'moderator', 'admin') DEFAULT 'user';

-- Table des messages signalés
CREATE TABLE messages_signales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    signale_par INT NOT NULL,
    raison TEXT,
    date_signalement DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages(id),
    FOREIGN KEY (signale_par) REFERENCES utilisateurs(id)
);
```

#### 7. Historique de Connexion
```sql
CREATE TABLE historique_connexions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    date_connexion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id)
);
```

#### 8. Salons de Discussion
```sql
CREATE TABLE salons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE messages ADD COLUMN salon_id INT DEFAULT 1;
ALTER TABLE messages ADD FOREIGN KEY (salon_id) REFERENCES salons(id);
```

#### 9. Réactions aux Messages
```sql
CREATE TABLE reactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    type ENUM('like', 'love', 'laugh', 'sad', 'angry') NOT NULL,
    date_reaction DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_reaction (message_id, user_id),
    FOREIGN KEY (message_id) REFERENCES messages(id),
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id)
);
```

#### 10. Statuts Personnalisés
```sql
ALTER TABLE utilisateurs ADD COLUMN statut VARCHAR(100) NULL;
ALTER TABLE utilisateurs ADD COLUMN avatar VARCHAR(255) NULL;
```

### Optimisations Techniques

#### 1. Migration vers PDO
```php
// Remplacer MySQLi par PDO pour plus de flexibilité
$pdo = new PDO("mysql:host=localhost;dbname=mini_chat;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Exemple de requête
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE pseudo = :pseudo");
$stmt->execute(['pseudo' => $pseudo]);
```

#### 2. Cache des Données
```php
// Utiliser Redis ou Memcached
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

// Mettre en cache la liste des utilisateurs en ligne
$redis->setex('users_online', 10, json_encode($users)); // Expire après 10s
```

#### 3. Pagination Côté Serveur
```php
// Implémenter LIMIT/OFFSET plus efficacement
$offset = ($page - 1) * $limit;
$stmt = $conn->prepare("SELECT * FROM messages ORDER BY id DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $limit, $offset);
```

#### 4. WebSockets pour le Temps Réel
```php
// Utiliser Ratchet pour WebSockets PHP
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;
    
    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }
    
    public function onMessage(ConnectionInterface $from, $msg) {
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }
}
```

#### 5. API RESTful Complète
```php
// Supporter JSON Web Tokens (JWT) pour l'authentification API
use Firebase\JWT\JWT;

$token = JWT::encode(['user_id' => $user_id], $secret_key, 'HS256');
```

## 🤝 Contribution

Les contributions sont les bienvenues ! Pour contribuer à ce projet :

### 1. Forker le Projet

```bash
git clone https://github.com/votre-username/mini-chat.git
cd mini-chat
```

### 2. Créer une Branche

```bash
git checkout -b feature/ma-nouvelle-fonctionnalite
```

### 3. Faire vos Modifications

- Ajoutez vos changements
- Testez votre code
- Commentez votre code (suivez les conventions PHPDoc)
- Assurez-vous que tout fonctionne correctement

### 4. Commiter

```bash
git add .
git commit -m "Ajout de la fonctionnalité X"
```

**Format des messages de commit :**
- `feat:` Nouvelle fonctionnalité
- `fix:` Correction de bug
- `docs:` Documentation
- `style:` Formatage
- `refactor:` Refactorisation
- `test:` Tests
- `chore:` Maintenance

### 5. Pousser et Créer une Pull Request

```bash
git push origin feature/ma-nouvelle-fonctionnalite
```

Puis ouvrez une Pull Request sur GitHub avec :
- Une description claire des changements
- Les raisons de ces changements
- Des captures d'écran si applicable

### Guidelines de Contribution

#### Code Style

**PHP (PSR-12) :**
```php
<?php

namespace App;

class MyClass
{
    public function myMethod(string $param): void
    {
        // Indentation : 4 espaces
        if ($condition) {
            // Code ici
        }
    }
}
```

**SQL :**
```sql
-- Mots-clés en MAJUSCULES
-- Indentation pour la lisibilité
SELECT u.id, u.pseudo, COUNT(m.id) as nb_messages
FROM utilisateurs u
LEFT JOIN messages m ON u.id = m.user_id
WHERE u.est_en_ligne = 1
GROUP BY u.id
ORDER BY nb_messages DESC;
```

**JavaScript (ES6+) :**
```javascript
// Utiliser const/let au lieu de var
// Arrow functions
const loadMessages = async (page = 1) => {
    try {
        const response = await fetch(`api.php?action=get_messages&page=${page}`);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Erreur:', error);
    }
};
```

#### Documentation

- ✅ Commentez les fonctions complexes
- ✅ Utilisez PHPDoc pour les fonctions publiques
- ✅ Mettez à jour le README si nécessaire

```php
/**
 * Récupère les messages avec pagination
 * 
 * @param int $page Numéro de la page (commence à 1)
 * @param int $limit Nombre de messages par page
 * @return array Tableau associatif avec les messages et métadonnées
 */
function getMessages(int $page = 1, int $limit = 10): array
{
    // ...
}
```

#### Tests

- ✅ Testez toutes les nouvelles fonctionnalités
- ✅ Vérifiez les cas limites (edge cases)
- ✅ Testez avec différents navigateurs si applicable

## 🐛 Signaler un Bug

### Ouvrir une Issue

Si vous trouvez un bug, ouvrez une [issue](https://github.com/votre-username/mini-chat/issues) avec les informations suivantes :

**Template d'Issue :**

```markdown
## Description du Bug
[Description claire et concise du problème]

## Étapes pour Reproduire
1. Aller sur '...'
2. Cliquer sur '...'
3. Scroller jusqu'à '...'
4. Observer l'erreur

## Comportement Attendu
[Ce qui devrait se passer]

## Comportement Actuel
[Ce qui se passe réellement]

## Captures d'Écran
[Si applicable, ajoutez des captures]

## Environnement
- OS: [ex. Windows 10]
- Navigateur: [ex. Chrome 120]
- Version PHP: [ex. 7.4.33]
- Version MySQL: [ex. 8.0.30]
- Serveur: [ex. XAMPP 8.2.4]

## Logs d'Erreur
[Copiez les messages d'erreur ici]

## Informations Complémentaires
[Tout autre contexte utile]
```

## 👨‍💻 Auteur

**Aymen RAKI**  

📧 Email : [aymen.raki.cs@gmail.com](mailto:aymen.raki.cs@gmail.com)  
🔗 LinkedIn : [linkedin.com/in/votre-profil](https://linkedin.com/in/votre-profil)  
🐙 GitHub : [github.com/aymenrakics](https://github.com/aymenrakics)  
🌐 Portfolio : [votre-site.com](https://votre-site.com)

## 🎓 Contexte Académique

Ce projet a été développé dans le cadre du module **Bases de Données** pour démontrer :

- ✅ **Modélisation de données** : Schéma relationnel normalisé (3NF)
- ✅ **Requêtes SQL** : SELECT, INSERT, UPDATE, DELETE, JOINs, agrégations
- ✅ **Intégrité référentielle** : Clés étrangères, contraintes, cascades
- ✅ **Optimisation** : Index, EXPLAIN, requêtes performantes
- ✅ **Sécurité** : Prepared statements, hashage, validation
- ✅ **Architecture** : Séparation des couches (présentation/logique/données)

### Compétences Développées

| Catégorie | Compétences |
|-----------|-------------|
| **Base de Données** | Conception de schéma, requêtes SQL avancées, normalisation, transactions |
| **Backend** | PHP procédural/POO, sessions, authentification, API REST |
| **Frontend** | HTML5, CSS3, JavaScript ES6+, AJAX, manipulation DOM |
| **Sécurité** | Hashage bcrypt, protection XSS/SQL injection, validation |
| **Outils** | Git, MySQL Workbench, phpMyAdmin, VS Code |

## 📄 License

Ce projet est sous licence **MIT** - voir le fichier [LICENSE](LICENSE) pour plus de détails.

```
MIT License

Copyright (c) 2024 Votre Nom

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

## 📞 Support

Si vous avez des questions ou besoin d'aide :

- 📧 **Email** : [aymen.raki.cs@gmail.com](mailto:aymen.raki.cs@gmail.com)
- 💬 **Discussions GitHub** : [Ouvrir une discussion](https://github.com/votre-username/mini-chat/discussions)
- 🐛 **Issues** : [Signaler un problème](https://github.com/votre-username/mini-chat/issues)

---

<div align="center">

**⭐ Si ce projet vous a été utile, n'hésitez pas à lui donner une étoile sur GitHub ! ⭐**

Made with ❤️ by [Votre Nom](https://github.com/aymenrakics)

</div>
