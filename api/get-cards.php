<?php
/**
 * API de récupération des cartes
 * 
 * Retourne la liste des cartes avec pagination et filtres
 * 
 * @author DeckForge Team
 * @version 1.0
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

// Vérification de la connexion
if (!is_logged_in()) {
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

// Connexion à la base de données
$conn = db_connect();

if (!$conn) {
    echo json_encode(['error' => 'Erreur de connexion à la base de données']);
    exit;
}

// Paramètres de pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 50;
$offset = ($page - 1) * $limit;

// Construction de la requête avec filtres
$where_conditions = array('1=1');
$params = array();
$types = '';

// Filtre par recherche textuelle
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $where_conditions[] = 'card_name LIKE ?';
    $params[] = $search;
    $types .= 's';
}

// Filtre par type
if (isset($_GET['types']) && is_array($_GET['types']) && !empty($_GET['types'])) {
    $type_placeholders = implode(',', array_fill(0, count($_GET['types']), '?'));
    $where_conditions[] = "type IN ($type_placeholders)";
    foreach ($_GET['types'] as $type) {
        $params[] = $type;
        $types .= 's';
    }
}

// Filtre par attribut
if (isset($_GET['attributs']) && is_array($_GET['attributs']) && !empty($_GET['attributs'])) {
    $attr_conditions = array();
    foreach ($_GET['attributs'] as $attr) {
        $attr_conditions[] = '?';
        $params[] = $attr;
        $types .= 's';
    }
    $attr_placeholders = implode(',', $attr_conditions);
    $where_conditions[] = "id_attribut IN (SELECT id_attribut FROM T_card_attribut WHERE attribut IN ($attr_placeholders))";
}

// Filtre par niveau
if (isset($_GET['level_min']) && is_numeric($_GET['level_min'])) {
    $where_conditions[] = 'level >= ?';
    $params[] = intval($_GET['level_min']);
    $types .= 'i';
}

if (isset($_GET['level_max']) && is_numeric($_GET['level_max'])) {
    $where_conditions[] = 'level <= ?';
    $params[] = intval($_GET['level_max']);
    $types .= 'i';
}

// Filtre par ATK
if (isset($_GET['atk_min']) && is_numeric($_GET['atk_min'])) {
    $where_conditions[] = 'atk >= ?';
    $params[] = intval($_GET['atk_min']);
    $types .= 'i';
}

if (isset($_GET['atk_max']) && is_numeric($_GET['atk_max'])) {
    $where_conditions[] = 'atk <= ?';
    $params[] = intval($_GET['atk_max']);
    $types .= 'i';
}

// Filtre par DEF
if (isset($_GET['def_min']) && is_numeric($_GET['def_min'])) {
    $where_conditions[] = 'def >= ?';
    $params[] = intval($_GET['def_min']);
    $types .= 'i';
}

if (isset($_GET['def_max']) && is_numeric($_GET['def_max'])) {
    $where_conditions[] = 'def <= ?';
    $params[] = intval($_GET['def_max']);
    $types .= 'i';
}

// Construction de la requête finale
$where_clause = implode(' AND ', $where_conditions);

// Comptage du total de cartes
$count_query = "SELECT COUNT(*) as total FROM T_card WHERE $where_clause";

if (!empty($params)) {
    $stmt = db_prepare($conn, $count_query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    $result = db_execute($stmt);
} else {
    $result = mysqli_query($conn, $count_query);
}

$count_row = db_fetch_one($result);
$total_cards = $count_row['total'];
$total_pages = ceil($total_cards / $limit);

// Récupération des cartes
$query = "
    SELECT 
        c.id_card,
        c.card_name,
        c.type,
        c.level,
        c.atk,
        c.def,
        c.description
    FROM T_card c
    WHERE $where_clause
    ORDER BY c.card_name ASC
    LIMIT ? OFFSET ?
";

$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

if (!empty($types)) {
    $stmt = db_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    $result = db_execute($stmt);
} else {
    $result = mysqli_query($conn, $query);
}

$cards = db_fetch_all($result);

// Ajout du chemin de l'image pour chaque carte
foreach ($cards as &$card) {
    $card['image_url'] = get_card_image_or_placeholder($card['id_card']);
}

// Fermeture de la connexion
db_close($conn);

// Retour de la réponse JSON
echo json_encode([
    'success' => true,
    'cards' => $cards,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_cards' => $total_cards,
        'per_page' => $limit
    ]
]);