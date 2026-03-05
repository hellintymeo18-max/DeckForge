<?php
/**
 * API d'inscription utilisateur
 * 
 * Traite l'inscription d'un nouvel utilisateur
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
$email = isset($_POST['email']) ? sanitize_string($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

// Validation des données
$errors = array();

if (empty($username)) {
    $errors[] = 'Le nom d\'utilisateur est requis';
} elseif (!validate_username($username)) {
    $errors[] = 'Le nom d\'utilisateur doit contenir entre 3 et 20 caractères alphanumériques ou underscore';
}

if (empty($email)) {
    $errors[] = 'L\'email est requis';
} elseif (!validate_email($email)) {
    $errors[] = 'L\'email n\'est pas valide';
}

if (empty($password)) {
    $errors[] = 'Le mot de passe est requis';
} elseif (!validate_password($password)) {
    $errors[] = 'Le mot de passe doit contenir au minimum 8 caractères';
}

if ($password !== $confirm_password) {
    $errors[] = 'Les mots de passe ne correspondent pas';
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

// Vérification si le username existe déjà
$stmt = db_prepare($conn, "SELECT id_user FROM T_user WHERE username = ?");
mysqli_stmt_bind_param($stmt, 's', $username);
$result = db_execute($stmt);

if (mysqli_num_rows($result) > 0) {
    db_close($conn);
    set_flash_message('error', 'Ce nom d\'utilisateur est déjà utilisé');
    redirect('/index.php');
}

// Vérification si l'email existe déjà
$stmt = db_prepare($conn, "SELECT id_user FROM T_user WHERE email = ?");
mysqli_stmt_bind_param($stmt, 's', $email);
$result = db_execute($stmt);

if (mysqli_num_rows($result) > 0) {
    db_close($conn);
    set_flash_message('error', 'Cet email est déjà utilisé');
    redirect('/index.php');
}

// Hash du mot de passe
$hashed_password = hash_password($password);

// Insertion du nouvel utilisateur
$stmt = db_prepare($conn, "INSERT INTO T_user (username, email, password) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'sss', $username, $email, $hashed_password);

if (!mysqli_stmt_execute($stmt)) {
    db_close($conn);
    set_flash_message('error', 'Erreur lors de la création du compte');
    redirect('/index.php');
}

// Récupération de l'ID du nouvel utilisateur
$user_id = db_insert_id($conn);

// Création de la session utilisateur
$user_data = array(
    'id_user' => $user_id,
    'username' => $username,
    'email' => $email
);

session_login($user_data);

// Envoi d'un email de bienvenue
$subject = 'Bienvenue sur DeckForge !';
$message = "
    <html>
    <head>
        <title>Bienvenue sur DeckForge</title>
    </head>
    <body>
        <h1>Bienvenue " . htmlspecialchars($username) . " !</h1>
        <p>Votre compte a été créé avec succès.</p>
        <p>Vous pouvez maintenant créer et gérer vos decks Yu-Gi-Oh!</p>
        <p><a href='" . get_base_url() . "/home.php'>Accéder à mon compte</a></p>
    </body>
    </html>
";

send_email($email, $subject, $message);

// Fermeture de la connexion
db_close($conn);

// Message de succès et redirection
set_flash_message('success', 'Votre compte a été créé avec succès ! Bienvenue ' . htmlspecialchars($username) . ' !');
redirect('/home.php');