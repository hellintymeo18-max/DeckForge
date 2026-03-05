<?php
/**
 * API de connexion utilisateur
 * 
 * Traite la connexion d'un utilisateur et crée la session
 * 
 * @author DeckForge Team
 * @version 1.0
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Vérification de la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit('Méthode non autorisée');
}

// Récupération et nettoyage des données
$username = isset($_POST['username']) ? sanitize_string($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validation des données
$errors = array();

if (empty($username)) {
    $errors[] = 'Le nom d\'utilisateur est requis';
}

if (empty($password)) {
    $errors[] = 'Le mot de passe est requis';
}

// Si erreurs de validation
if (!empty($errors)) {
    set_flash_message('error', implode('<br>', $errors));
    redirect('/index.php');
}

// Connexion à la base de données
$conn = db_connect();

if (!$conn) {
    set_flash_message('error', 'Erreur de connexion à la base de données');
    redirect('/index.php');
}

// Recherche de l'utilisateur
$stmt = db_prepare($conn, "SELECT * FROM T_user WHERE username = ?");
mysqli_stmt_bind_param($stmt, 's', $username);
$result = db_execute($stmt);

if (!$result) {
    db_close($conn);
    set_flash_message('error', 'Erreur lors de la connexion');
    redirect('/index.php');
}

$user = db_fetch_one($result);

// Vérification de l'existence de l'utilisateur et du mot de passe
if (!$user || !verify_password($password, $user['password'])) {
    db_close($conn);
    set_flash_message('error', 'Nom d\'utilisateur ou mot de passe incorrect');
    redirect('/index.php');
}

// Connexion réussie - Création de la session
session_login($user);

// Fermeture de la connexion
db_close($conn);

// Message de succès et redirection
set_flash_message('success', 'Bienvenue ' . htmlspecialchars($user['username']) . ' !');
redirect('/home.php');