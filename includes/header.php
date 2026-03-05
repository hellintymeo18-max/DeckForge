<?php
/**
 * Header commun du site
 * 
 * Affiche le header avec logo, barre de recherche et boutons de connexion/profil
 * selon l'état de connexion de l'utilisateur
 * 
 * Variables attendues :
 * - $page_title : Titre de la page
 * - $meta_description : Description meta (optionnel)
 * - $canonical_url : URL canonique (optionnel)
 * 
 * @author DeckForge Team
 * @version 1.0
 */

// Démarrage de la session si nécessaire
require_once __DIR__ . '/session.php';
session_start_secure();

// Variables par défaut
$page_title = isset($page_title) ? $page_title : 'DeckForge - Yu-Gi-Oh! Deck Manager';
$meta_description = isset($meta_description) ? $meta_description : 'Créez, gérez et partagez vos decks Yu-Gi-Oh! en ligne avec DeckForge.';
$canonical_url = isset($canonical_url) ? $canonical_url : get_base_url() . $_SERVER['REQUEST_URI'];
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta name="keywords" content="yugioh, deck, deck builder, tcg, cartes, yu-gi-oh">
    <meta name="author" content="DeckForge">
    
    <!-- SEO -->
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical_url); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonical_url); ?>">
    
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- Styles CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
</head>
<body>
    <!-- Header principal -->
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <div class="logo">
                    <a href="<?php echo is_logged_in() ? '/home.php' : '/index.php'; ?>" title="Retour à la page d'accueil">
                        <img src="/assets/images/logo.png" alt="DeckForge Logo">
                    </a>
                </div>
                
                <!-- Barre de recherche -->
                <div class="search-bar">
                    <form action="/search.php" method="GET" class="search-form">
                        <input 
                            type="text" 
                            name="q" 
                            placeholder="Rechercher des cartes..." 
                            class="search-input"
                            aria-label="Rechercher des cartes"
                            autocomplete="off"
                        >
                        <button type="submit" class="search-button" aria-label="Lancer la recherche">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                        </button>
                    </form>
                </div>
                
                <!-- Actions utilisateur -->
                <div class="user-actions">
                    <?php if (is_logged_in()): ?>
                        <!-- Utilisateur connecté -->
                        <button class="btn btn-profile" id="btn-profile" aria-label="Ouvrir le profil">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <span><?php echo htmlspecialchars(get_username()); ?></span>
                        </button>
                    <?php else: ?>
                        <!-- Utilisateur non connecté -->
                        <button class="btn btn-primary" id="btn-login" aria-label="Se connecter">
                            Connexion
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Messages flash -->
    <?php
    $flash = get_flash_message();
    if ($flash):
    ?>
    <div class="flash-message flash-<?php echo htmlspecialchars($flash['type']); ?>" id="flash-message">
        <div class="container">
            <p><?php echo htmlspecialchars($flash['message']); ?></p>
            <button class="flash-close" aria-label="Fermer le message">×</button>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Contenu principal -->
    <main class="main-content">