<?php
/**
 * Gestion des sessions utilisateur
 * 
 * Ce fichier gère le démarrage sécurisé des sessions,
 * la connexion/déconnexion et les vérifications d'authentification
 * 
 * @author DeckForge Team
 * @version 1.0
 */

/**
 * Démarre une session sécurisée
 * 
 * @return void
 */
function session_start_secure() {
    // Ne définit les paramètres que si la session n'est pas encore active
    if (session_status() === PHP_SESSION_NONE) {
        // Paramètres de session sécurisés
        ini_set('session.cookie_lifetime', 0);
        ini_set('session.gc_maxlifetime', 3600);
        ini_set('session.use_strict_mode', 1); // mettre 0 si HTTP local
        
        // Démarrage de la session
        session_start();
        
        // Régénération de l'ID pour prévenir le vol de session
        if (!isset($_SESSION['initiated'])) {
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
        }
    }
}


/**
 * Connecte un utilisateur
 * 
 * @param array $user_data Tableau contenant les données de l'utilisateur
 * @return void
 */
function session_login($user_data) {
    session_start_secure();
    
    // Stockage des informations utilisateur en session
    $_SESSION['user_id'] = $user_data['id_user'];
    $_SESSION['username'] = $user_data['username'];
    $_SESSION['email'] = $user_data['email'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    
    // Régénération de l'ID pour sécurité
    session_regenerate_id(true);
}

/**
 * Déconnecte l'utilisateur
 * 
 * @return void
 */
function session_logout() {
    session_start_secure();
    
    // Suppression de toutes les variables de session
    $_SESSION = array();
    
    // Suppression du cookie de session
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destruction de la session
    session_destroy();
}

/**
 * Vérifie si l'utilisateur est connecté
 * 
 * @return bool True si connecté, false sinon
 */
function is_logged_in() {
    session_start_secure();
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Récupère l'ID de l'utilisateur connecté
 * 
 * @return int|null L'ID utilisateur ou null si non connecté
 */
function get_user_id() {
    session_start_secure();
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Récupère le username de l'utilisateur connecté
 * 
 * @return string|null Le username ou null si non connecté
 */
function get_username() {
    session_start_secure();
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}

/**
 * Récupère l'email de l'utilisateur connecté
 * 
 * @return string|null L'email ou null si non connecté
 */
function get_user_email() {
    session_start_secure();
    return isset($_SESSION['email']) ? $_SESSION['email'] : null;
}

/**
 * Redirige vers la page de connexion si non connecté
 * 
 * @return void
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: /index.php?login_required=1');
        exit();
    }
}

/**
 * Redirige vers la page d'accueil si déjà connecté
 * 
 * @return void
 */
function redirect_if_logged_in() {
    if (is_logged_in()) {
        header('Location: /home.php');
        exit();
    }
}

/**
 * Vérifie si la session est expirée (24h de timeout)
 * 
 * @return bool True si expirée, false sinon
 */
function is_session_expired() {
    session_start_secure();
    
    if (!isset($_SESSION['login_time'])) {
        return true;
    }
    
    // Timeout de 24 heures
    $timeout = 24 * 60 * 60;
    
    if (time() - $_SESSION['login_time'] > $timeout) {
        session_logout();
        return true;
    }
    
    return false;
}

/**
 * Met à jour le timestamp de dernière activité
 * 
 * @return void
 */
function session_update_activity() {
    session_start_secure();
    $_SESSION['last_activity'] = time();
}