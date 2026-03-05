<?php
/**
 * API de déconnexion utilisateur
 * 
 * Déconnecte l'utilisateur et détruit la session
 * 
 * @author DeckForge Team
 * @version 1.0
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Déconnexion de l'utilisateur
session_logout();

// Message de confirmation et redirection
set_flash_message('info', 'Vous avez été déconnecté avec succès');
redirect('/index.php');