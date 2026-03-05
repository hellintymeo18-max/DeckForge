<?php
/**
 * API de suppression de deck
 * 
 * Supprime un deck et toutes ses cartes associées
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

// Récupération des données
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!$data || !isset($data['deck_id'])) {
    echo json_encode(['error' => 'Données invalides']);
    exit;
}

$deck_id = intval($data['deck_id']);
$user_id = get_user_id();

// Connexion à la base de données
$conn = db_connect();

if (!$conn) {
    echo json_encode(['error' => 'Erreur de connexion à la base de données']);
    exit;
}

// Vérification que le deck appartient à l'utilisateur
$stmt = db_prepare($conn, "SELECT id_deck FROM T_deck WHERE id_deck = ? AND id_user = ?");
mysqli_stmt_bind_param($stmt, 'ii', $deck_id, $user_id);
$result = db_execute($stmt);

if (mysqli_num_rows($result) === 0) {
    db_close($conn);
    echo json_encode(['error' => 'Deck non trouvé ou non autorisé']);
    exit;
}

// Début de la transaction
mysqli_begin_transaction($conn);

try {
    // Suppression des cartes du deck
    $stmt = db_prepare($conn, "DELETE FROM T_card_deck WHERE id_deck = ?");
    mysqli_stmt_bind_param($stmt, 'i', $deck_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Erreur lors de la suppression des cartes');
    }
    
    // Suppression du deck
    $stmt = db_prepare($conn, "DELETE FROM T_deck WHERE id_deck = ?");
    mysqli_stmt_bind_param($stmt, 'i', $deck_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Erreur lors de la suppression du deck');
    }
    
    // Commit de la transaction
    mysqli_commit($conn);
    
    // Fermeture de la connexion
    db_close($conn);
    
    // Retour de succès
    echo json_encode([
        'success' => true,
        'message' => 'Deck supprimé avec succès'
    ]);
    
} catch (Exception $e) {
    // Rollback en cas d'erreur
    mysqli_rollback($conn);
    db_close($conn);
    
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}