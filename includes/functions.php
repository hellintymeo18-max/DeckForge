<?php
/**
 * Fonctions utilitaires globales
 * 
 * Ce fichier contient toutes les fonctions réutilisables
 * pour la validation, le sanitization, l'envoi d'emails, etc.
 * 
 * @author DeckForge Team
 * @version 1.0
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Valide une adresse email
 * 
 * @param string $email L'email à valider
 * @return bool True si valide, false sinon
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valide un username (3-20 caractères alphanumériques et underscore)
 * 
 * @param string $username Le username à valider
 * @return bool True si valide, false sinon
 */
function validate_username($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

/**
 * Valide un mot de passe (minimum 8 caractères)
 * 
 * @param string $password Le mot de passe à valider
 * @return bool True si valide, false sinon
 */
function validate_password($password) {
    return strlen($password) >= 8;
}

/**
 * Nettoie une chaîne de caractères
 * 
 * @param string $string La chaîne à nettoyer
 * @return string La chaîne nettoyée
 */
function sanitize_string($string) {
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

/**
 * Hash un mot de passe de manière sécurisée
 * 
 * @param string $password Le mot de passe en clair
 * @return string Le hash du mot de passe
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Vérifie un mot de passe contre son hash
 * 
 * @param string $password Le mot de passe en clair
 * @param string $hash Le hash à vérifier
 * @return bool True si correspond, false sinon
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Génère un token aléatoire sécurisé
 * 
 * @param int $length Longueur du token
 * @return string Le token généré
 */
function generate_token($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Envoie un email (fonction wrapper pour logging en dev)
 * 
 * @param string $to Destinataire
 * @param string $subject Sujet
 * @param string $message Message
 * @param string $from Expéditeur
 * @return bool True si envoyé, false sinon
 */
function send_email($to, $subject, $message, $from = 'noreply@deckforge.local') {
    // Headers de l'email
    $headers = "From: " . $from . "\r\n";
    $headers .= "Reply-To: " . $from . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Log de l'email en développement
    log_email($to, $subject, $message);
    
    // Envoi de l'email (peut échouer en dev sans SMTP)
    return @mail($to, $subject, $message, $headers);
}

/**
 * Enregistre un email dans un fichier log
 * 
 * @param string $to Destinataire
 * @param string $subject Sujet
 * @param string $message Message
 * @return void
 */
function log_email($to, $subject, $message) {
    $log_dir = __DIR__ . '/../logs/';
    
    // Création du dossier logs s'il n'existe pas
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . 'emails_' . date('Y-m-d') . '.log';
    $log_content = "============================================\n";
    $log_content .= "Date: " . date('Y-m-d H:i:s') . "\n";
    $log_content .= "To: " . $to . "\n";
    $log_content .= "Subject: " . $subject . "\n";
    $log_content .= "Message:\n" . strip_tags($message) . "\n";
    $log_content .= "============================================\n\n";
    
    file_put_contents($log_file, $log_content, FILE_APPEND);
}

/**
 * Crée un slug SEO-friendly à partir d'une chaîne
 * 
 * @param string $string La chaîne à convertir
 * @return string Le slug généré
 */
function create_slug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

/**
 * Formate une date au format français
 * 
 * @param string $date Date au format SQL
 * @return string Date formatée
 */
function format_date($date) {
    $timestamp = strtotime($date);
    return date('d/m/Y à H:i', $timestamp);
}

/**
 * Retourne l'URL de base du site
 * 
 * @return string L'URL de base
 */
function get_base_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . '://' . $host;
}

/**
 * Redirige vers une URL
 * 
 * @param string $url L'URL de redirection
 * @return void
 */
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

/**
 * Retourne un message flash (notification temporaire)
 * 
 * @param string $type Type de message (success, error, info)
 * @param string $message Le message
 * @return void
 */
function set_flash_message($type, $message) {
    session_start_secure();
    $_SESSION['flash_message'] = array(
        'type' => $type,
        'message' => $message
    );
}

/**
 * Récupère et supprime le message flash
 * 
 * @return array|null Le message flash ou null
 */
function get_flash_message() {
    session_start_secure();
    
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    
    return null;
}

/**
 * Vérifie si une carte existe dans la base
 * 
 * @param mysqli $conn Connexion DB
 * @param int $card_id ID de la carte
 * @return bool True si existe, false sinon
 */
function card_exists($conn, $card_id) {
    $stmt = db_prepare($conn, "SELECT id_card FROM T_card WHERE id_card = ?");
    mysqli_stmt_bind_param($stmt, 'i', $card_id);
    $result = db_execute($stmt);
    
    return mysqli_num_rows($result) > 0;
}

/**
 * Récupère les informations d'une carte
 * 
 * @param mysqli $conn Connexion DB
 * @param int $card_id ID de la carte
 * @return array|null Les données de la carte ou null
 */
function get_card_by_id($conn, $card_id) {
    $stmt = db_prepare($conn, "SELECT * FROM T_card WHERE id_card = ?");
    mysqli_stmt_bind_param($stmt, 'i', $card_id);
    $result = db_execute($stmt);
    
    return db_fetch_one($result);
}

/**
 * Récupère le chemin de l'image d'une carte
 * 
 * @param int $card_id ID de la carte
 * @return string Chemin de l'image
 */
function get_card_image_path($card_id) {
    return '/assets/images/cards/' . $card_id . '.jpg';
}

/**
 * Vérifie si l'image d'une carte existe
 * 
 * @param int $card_id ID de la carte
 * @return bool True si existe, false sinon
 */
function card_image_exists($card_id) {
    $path = __DIR__ . '/../assets/images/cards/' . $card_id . '.jpg';
    return file_exists($path);
}

/**
 * Retourne une image placeholder si la carte n'a pas d'image
 * 
 * @param int $card_id ID de la carte
 * @return string Chemin de l'image ou placeholder
 */
function get_card_image_or_placeholder($card_id) {
    if (card_image_exists($card_id)) {
        return get_card_image_path($card_id);
    }
    return '/assets/images/placeholder-card.jpg';
}

/**
 * Compte le nombre de cartes dans un deck
 * 
 * @param mysqli $conn Connexion DB
 * @param int $deck_id ID du deck
 * @return int Nombre de cartes
 */
function count_deck_cards($conn, $deck_id) {
    $stmt = db_prepare($conn, "SELECT COUNT(*) as total FROM T_card_deck WHERE id_deck = ?");
    mysqli_stmt_bind_param($stmt, 'i', $deck_id);
    $result = db_execute($stmt);
    $row = db_fetch_one($result);
    
    return $row['total'];
}

/**
 * Vérifie la validité d'un deck (40-60 main, 0-15 extra)
 * 
 * @param mysqli $conn Connexion DB
 * @param int $deck_id ID du deck
 * @return array Résultat de la validation
 */
function validate_deck($conn, $deck_id) {
    $query = "
        SELECT 
            SUM(CASE WHEN c.type IN ('Xyz', 'Synchro', 'Fusion', 'Link') THEN 1 ELSE 0 END) as extra_count,
            SUM(CASE WHEN c.type NOT IN ('Xyz', 'Synchro', 'Fusion', 'Link') THEN 1 ELSE 0 END) as main_count
        FROM T_card_deck cd
        JOIN T_card c ON cd.id_card = c.id_card
        WHERE cd.id_deck = ?
    ";
    
    $stmt = db_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $deck_id);
    $result = db_execute($stmt);
    $counts = db_fetch_one($result);
    
    $main_valid = $counts['main_count'] >= 40 && $counts['main_count'] <= 60;
    $extra_valid = $counts['extra_count'] >= 0 && $counts['extra_count'] <= 15;
    
    return array(
        'valid' => $main_valid && $extra_valid,
        'main_count' => $counts['main_count'],
        'extra_count' => $counts['extra_count'],
        'errors' => array(
            'main' => !$main_valid ? 'Le Main Deck doit contenir entre 40 et 60 cartes' : null,
            'extra' => !$extra_valid ? 'L\'Extra Deck doit contenir entre 0 et 15 cartes' : null
        )
    );
}