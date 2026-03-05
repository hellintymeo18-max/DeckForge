# DeckForge - Yu-Gi-Oh! Deck Manager

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.x-purple.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)

Un gestionnaire de decks Yu-Gi-Oh! professionnel développé en PHP procédural pur avec une interface moderne et intuitive.

---

## 📋 Table des matières

- [Fonctionnalités](#-fonctionnalités)
- [Prérequis](#-prérequis)
- [Installation](#-installation)
- [Configuration](#️-configuration)
- [Structure du projet](#-structure-du-projet)
- [Utilisation](#-utilisation)
- [API Documentation](#-api-documentation)
- [Sécurité](#-sécurité)
- [SEO](#-seo)
- [Maintenance](#️-maintenance)
- [Support](#-support)

---

## ✨ Fonctionnalités

### Gestion des utilisateurs
- ✅ Inscription / Connexion sécurisée
- ✅ Hashage des mots de passe (password_hash)
- ✅ Récupération de pseudo par email
- ✅ Réinitialisation de mot de passe
- ✅ Sessions sécurisées

### Gestion des decks
- ✅ Création de decks (Main Deck 40-60, Extra Deck 0-15)
- ✅ Édition et suppression
- ✅ Decks publics / privés
- ✅ 5 cartes mises en avant
- ✅ Drag & drop intuitif
- ✅ Validation automatique

### Recherche et filtres
- ✅ Recherche par nom
- ✅ Filtres avancés (Type, Attribut, Niveau, ATK/DEF)
- ✅ Recherche en temps réel (debouncing)
- ✅ Pagination

### Interface
- ✅ Design responsive
- ✅ Modals interactives
- ✅ Messages flash
- ✅ Animations fluides
- ✅ Interface professionnelle

### SEO
- ✅ URLs SEO-friendly
- ✅ Sitemap.xml
- ✅ Balises meta dynamiques
- ✅ Structure sémantique

---

## 🔧 Prérequis

- **PHP** : 8.0 ou supérieur
- **MySQL** : 5.7 ou supérieur
- **Apache** : 2.4+ avec mod_rewrite activé
- **Laragon** (ou XAMPP/WAMP)
- Base de données `yugioh` déjà créée

---

## 📦 Installation

### Étape 1 : Cloner le projet

```bash
git clone https://github.com/votre-repo/DeckForge.git
cd DeckForge
```

### Étape 2 : Configuration de la base de données

1. Assurez-vous que votre base de données `yugioh` existe avec les tables suivantes :
   - `T_user`
   - `T_card`
   - `T_deck`
   - `T_card_deck`
   - `T_card_type`
   - `T_card_attribut`
   - `T_card_property`

2. Le fichier de configuration se trouve dans :
   ```
   C:\laragon\www\DeckForgeV2\config\database.php
   ```

3. Les paramètres par défaut sont :
   ```php
   Host: localhost
   User: root
   Password: (vide)
   Database: yugioh
   ```

### Étape 3 : Copier les images des cartes

1. **Source des images** : `C:\Users\user\Desktop\image ygo`
2. **Destination** : `C:\laragon\www\DeckForgeV2\assets\images\cards\`

**Commande PowerShell :**
```powershell
Copy-Item "C:\Users\user\Desktop\image ygo\*" "C:\laragon\www\DeckForgeV2\assets\images\cards\" -Recurse
```

**Ou via l'explorateur Windows :**
- Copier tous les fichiers `.jpg` du dossier source
- Coller dans `assets/images/cards/`

⚠️ **Important** : Les images doivent être nommées selon l'ID officiel des cartes (ex: `483.jpg`)

### Étape 4 : Créer les dossiers nécessaires

```bash
mkdir logs
mkdir assets/images/cards
```

### Étape 5 : Configuration Apache (Laragon)

1. Vérifier que `mod_rewrite` est activé
2. Le fichier `.htaccess` est déjà configuré
3. Redémarrer Apache

### Étape 6 : Permissions

Donner les permissions d'écriture au dossier `logs` :

**Windows :**
```bash
icacls logs /grant Everyone:(OI)(CI)F
```

---

## ⚙️ Configuration

### Configuration de la base de données

Fichier : `config/database.php`

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'yugioh');
```

### Configuration des sessions

Fichier : `includes/session.php`

```php
// Timeout de session : 24 heures
$timeout = 24 * 60 * 60;
```

### Configuration des emails

Les emails sont loggés dans : `logs/emails_YYYY-MM-DD.log`

Pour activer l'envoi réel d'emails (en production) :
1. Configurer un serveur SMTP
2. Modifier la fonction `send_email()` dans `includes/functions.php`

---

## 📁 Structure du projet

```
DeckForgeV2/
│
├── index.php                 # Page d'accueil (non connecté)
├── home.php                  # Page d'accueil (connecté)
├── deck-builder.php          # Constructeur de deck
├── support.php               # Page support
├── forgot-username.php       # Récupération pseudo
├── forgot-password.php       # Récupération mot de passe
├── legal-mentions.php        # Mentions légales
├── .htaccess                 # Configuration Apache
├── sitemap.xml              # Plan du site SEO
│
├── config/
│   └── database.php          # Connexion MySQL
│
├── includes/
│   ├── header.php            # Header commun
│   ├── footer.php            # Footer commun
│   ├── functions.php         # Fonctions utilitaires
│   └── session.php           # Gestion sessions
│
├── assets/
│   ├── css/
│   │   └── style.css         # Styles principaux
│   ├── js/
│   │   ├── main.js           # Scripts globaux
│   │   ├── modal.js          # Gestion modals
│   │   └── deck-builder.js   # Logique deck builder
│   └── images/
│       ├── cards/            # Images des cartes Yu-Gi-Oh!
│       ├── logo.png
│       └── card-back.jpg
│
├── api/
│   ├── login.php             # Connexion
│   ├── register.php          # Inscription
│   ├── logout.php            # Déconnexion
│   ├── get-cards.php         # Récupération cartes
│   ├── save-deck.php         # Sauvegarde deck
│   └── delete-deck.php       # Suppression deck
│
└── logs/
    └── emails_*.log          # Logs des emails
```

---

## 🚀 Utilisation

### Accès au site

- **URL locale** : `http://localhost/` ou `http://deckforge.local/`

### Créer un compte

1. Cliquer sur "Connexion"
2. Cliquer sur "Je n'ai pas encore de compte"
3. Remplir le formulaire d'inscription
4. Connexion automatique après création

### Créer un deck

1. Se connecter
2. Cliquer sur "Créer un deck"
3. Donner un nom au deck
4. Double-cliquer sur les cartes pour les ajouter
5. Utiliser les filtres pour rechercher des cartes spécifiques
6. Cliquer sur "Sauvegarder"
7. Choisir Public/Privé
8. Sélectionner 5 cartes mises en avant

### Éditer un deck

1. Aller dans "Mes decks"
2. Cliquer sur "Éditer"
3. Modifier le deck
4. Sauvegarder

---

## 📚 API Documentation

### Authentification

#### POST `/api/login.php`
Connexion utilisateur

**Paramètres :**
```json
{
  "username": "string",
  "password": "string"
}
```

**Réponse :**
- Redirection vers `/home.php` si succès
- Message flash d'erreur si échec

#### POST `/api/register.php`
Inscription utilisateur

**Paramètres :**
```json
{
  "username": "string (3-20 chars)",
  "email": "string (valid email)",
  "password": "string (min 8 chars)",
  "confirm_password": "string"
}
```

#### GET `/api/logout.php`
Déconnexion utilisateur

---

### Gestion des cartes

#### GET `/api/get-cards.php`
Récupération des cartes avec filtres

**Query Parameters :**
- `page` : Numéro de page (défaut: 1)
- `limit` : Cartes par page (défaut: 50, max: 100)
- `search` : Recherche textuelle
- `types[]` : Filtres de type (array)
- `attributs[]` : Filtres d'attribut (array)
- `level_min` : Niveau minimum
- `level_max` : Niveau maximum
- `atk_min` / `atk_max` : ATK min/max
- `def_min` / `def_max` : DEF min/max

**Réponse :**
```json
{
  "success": true,
  "cards": [
    {
      "id_card": 123,
      "card_name": "Blue-Eyes White Dragon",
      "type": "Monstre",
      "level": 8,
      "atk": 3000,
      "def": 2500,
      "description": "...",
      "image_url": "/assets/images/cards/123.jpg"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 10,
    "total_cards": 500,
    "per_page": 50
  }
}
```

---

### Gestion des decks

#### POST `/api/save-deck.php`
Création/modification d'un deck

**Paramètres JSON :**
```json
{
  "deck_id": 123,              // Null pour nouveau deck
  "deck_name": "Mon Deck",
  "is_public": true,
  "cards": [1, 2, 3, ...],     // IDs des cartes
  "featured_cards": [1, 2, 3, 4, 5]
}
```

**Réponse :**
```json
{
  "success": true,
  "deck_id": 123,
  "message": "Deck sauvegardé avec succès",
  "validation": {
    "valid": true,
    "main_count": 45,
    "extra_count": 10
  }
}
```

#### POST `/api/delete-deck.php`
Suppression d'un deck

**Paramètres JSON :**
```json
{
  "deck_id": 123
}
```

---

## 🔒 Sécurité

### Mesures implémentées

1. **Authentification**
   - Hashage bcrypt des mots de passe
   - Sessions sécurisées avec regeneration d'ID
   - Timeout automatique (24h)

2. **Protection XSS**
   - `htmlspecialchars()` sur toutes les sorties
   - Headers de sécurité dans `.htaccess`

3. **Protection SQL Injection**
   - Requêtes préparées (prepared statements)
   - Fonction `db_escape()` pour échapper les chaînes

4. **Protection CSRF**
   - À implémenter en production avec tokens

5. **Validation des données**
   - Validation côté serveur
   - Sanitization des entrées utilisateur

6. **Fichiers sensibles**
   - Accès interdit aux dossiers `config/`, `includes/`, `logs/`
   - Configuration `.htaccess`

---

## 🎯 SEO

### URLs SEO-friendly

- `/deck/123-mon-super-deck` (au lieu de `deck.php?id=123`)
- `/card/456-blue-eyes` (au lieu de `card.php?id=456`)

### Sitemap.xml

Accessible à : `http://localhost/sitemap.xml`

### Balises meta

Chaque page possède :
- `<title>` unique
- `<meta name="description">`
- `<link rel="canonical">`
- Open Graph tags

---

## 🛠️ Maintenance

### Logs des emails

Fichier : `logs/emails_YYYY-MM-DD.log`

Format :
```
============================================
Date: 2025-01-19 14:30:00
To: user@example.com
Subject: Bienvenue sur DeckForge !
Message: ...
============================================
```

### Nettoyage des logs

Supprimer les logs de plus de 30 jours :

```bash
find logs/ -name "emails_*.log" -mtime +30 -delete
```

### Sauvegarde de la base de données

```bash
mysqldump -u root yugioh > backup_$(date +%Y%m%d).sql
```

---

## 📞 Support

### Contact

- **Email** : contact@deckforge.local
- **URL** : http://localhost/support.php

### Problèmes courants

#### Les images ne s'affichent pas
✅ Vérifier que les images sont dans `assets/images/cards/`
✅ Vérifier les permissions du dossier
✅ Vérifier que le nom correspond à l'ID de la carte

#### Erreur de connexion à la base de données
✅ Vérifier les identifiants dans `config/database.php`
✅ Vérifier que MySQL est démarré
✅ Vérifier que la base `yugioh` existe

#### Les URLs ne fonctionnent pas
✅ Vérifier que `mod_rewrite` est activé
✅ Vérifier que le fichier `.htaccess` est présent
✅ Redémarrer Apache

#### Les emails ne sont pas envoyés
✅ Les emails sont loggés dans `logs/emails_*.log`
✅ Configurer un serveur SMTP pour l'envoi réel

---

## 📝 Notes importantes

### Images des cartes
- Format : JPG
- Nommage : ID officiel de la carte (ex: `483.jpg`)
- Chemin : `assets/images/cards/`

### Développement vs Production

**En développement :**
- Erreurs PHP affichées
- Emails loggés (non envoyés)
- HTTPS désactivé

**En production :**
```php
// config/database.php
php_flag display_errors Off

// .htaccess
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 🎉 Fonctionnalités à venir

- [ ] Système de likes
- [ ] Commentaires sur les decks
- [ ] Statistiques avancées
- [ ] Import/Export de decks
- [ ] Partage sur réseaux sociaux
- [ ] Mode sombre
- [ ] Application mobile

---

## 📜 Licence

Ce projet est développé pour un usage personnel et éducatif.

Yu-Gi-Oh! est une marque déposée de KONAMI DIGITAL ENTERTAINMENT.
Ce projet n'est pas affilié à KONAMI.

---

## 👨‍💻 Développeur

**DeckForge Team**  
Version 1.0.0 - Janvier 2025

---

**Bon développement ! 🚀**