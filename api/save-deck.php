<?php
/**
 * API de sauvegarde de deck
 * 
 * Crée ou met à jour un deck avec ses cartes
 * 
 * @author DeckForge Team
 * @version 1.0
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Vérification de la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// Vérification de la connexion
if (!is_logged_in()) {
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

// Récupération des données JSON
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!$data) {
    echo json_encode(['error' => 'Données invalides']);
    exit;
}

// Validation des données
$errors = array();

$deck_name = isset($data['deck_name']) ? sanitize_string($data['deck_name']) : '';
$is_public = isset($data['is_public']) ? (bool)$data['is_public'] : false;
$cards = isset($data['cards']) && is_array($data['cards']) ? $data['cards'] : array();
$featured_cards = isset($data['featured_cards']) && is_array($data['featured_cards']) ? $data['featured_cards'] : array();
$deck_id = isset($data['deck_id']) ? intval($data['deck_id']) : null;

if (empty($deck_name)) {
    $errors[] = 'Le nom du deck est requis';
}

if (empty($cards)) {
    $errors[] = 'Le deck doit contenir au moins une carte';
}

// Validation du nombre de cartes
$main_deck_cards = array_filter($cards, function($card_id) {
    // Détection basique : les cartes extra sont généralement ID > 10000 ou types spécifiques
    // À adapter selon votre base de données
    return true; // Simplifié pour cet exemple
});

if (count($cards) < 40) {
    $errors[] = 'Le Main Deck doit contenir au minimum 40 cartes';
}

if (count($cards) > 75) {
    $errors[] = 'Le deck ne peut pas contenir plus de 75 cartes au total';
}

if (!empty($featured_cards) && count($featured_cards) !== 5) {
    $errors[] = 'Vous devez sélectionner exactement 5 cartes à mettre en avant';
}

if (!empty($errors)) {
    echo json_encode(['error' => implode(', ', $errors)]);
    exit;
}

// Connexion à la base de données
$conn = db_connect();

if (!$conn) {
    echo json_encode(['error' => 'Erreur de connexion à la base de données']);
    exit;
}

$user_id = get_user_id();

// Début de la transaction
mysqli_begin_transaction($conn);

try {
    // Création ou mise à jour du deck
    if ($deck_id) {
        // Vérification que le deck appartient à l'utilisateur
        $stmt = db_prepare($conn, "SELECT id_deck FROM T_deck WHERE id_deck = ? AND id_user = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $deck_id, $user_id);
        $result = db_execute($stmt);
        
        if (mysqli_num_rows($result) === 0) {
            throw new Exception('Deck non trouvé ou non autorisé');
        }
        
        // Mise à jour du deck existant
        $stmt = db_prepare($conn, "UPDATE T_deck SET name_deck = ?, is_public = ? WHERE id_deck = ?");
        mysqli_stmt_bind_param($stmt, 'sii', $deck_name, $is_public, $deck_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Erreur lors de la mise à jour du deck');
        }
        
        // Suppression des anciennes cartes
        $stmt = db_prepare($conn, "DELETE FROM T_card_deck WHERE id_deck = ?");
        mysqli_stmt_bind_param($stmt, 'i', $deck_id);
        mysqli_stmt_execute($stmt);
        
    } else {
        // Création d'un nouveau deck
        $stmt = db_prepare($conn, "INSERT INTO T_deck (name_deck, id_user, is_public, number_like) VALUES (?, ?, ?, 0)");
        mysqli_stmt_bind_param($stmt, 'sii', $deck_name, $user_id, $is_public);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Erreur lors de la création du deck');
        }
        
        $deck_id = db_insert_id($conn);
    }
    
    // Insertion des cartes dans le deck
    $stmt = db_prepare($conn, "INSERT INTO T_card_deck (id_card, id_deck) VALUES (?, ?)");
    
    foreach ($cards as $card_id) {
        $card_id_int = intval($card_id);
        
        // Vérification que la carte existe
        if (!card_exists($conn, $card_id_int)) {
            continue; // Skip les cartes invalides
        }
        
        mysqli_stmt_bind_param($stmt, 'ii', $card_id_int, $deck_id);
        mysqli_stmt_execute($stmt);
    }
    
    // Validation du deck (règles Yu-Gi-Oh!)
    $validation = validate_deck($conn, $deck_id);
    
    if (!$validation['valid']) {
        throw new Exception(implode(', ', array_filter($validation['errors'])));
    }
    
    // Commit de la transaction
    mysqli_commit($conn);
    
    // Fermeture de la connexion
    db_close($conn);
    
    // Retour de succès
    echo json_encode([
        'success' => true,
        'deck_id' => $deck_id,
        'message' => 'Deck sauvegardé avec succès',
        'validation' => $validation
    ]);
    
} catch (Exception $e) {
    // Rollback en cas d'erreur
    mysqli_rollback($conn);
    db_close($conn);
    
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}