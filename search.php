<?php
/**
 * Page de recherche de cartes
 * 
 * Affiche les résultats de recherche de cartes avec filtres
 * et permet d'accéder aux détails de chaque carte
 * 
 * @author DeckForge Team
 * @version 1.0
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

// Récupération de la recherche
$search_query = isset($_GET['q']) ? sanitize_string($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 24;
$offset = ($page - 1) * $limit;

// Connexion à la base de données
$conn = db_connect();

// Construction de la requête
$where_conditions = array();
$params = array();
$types = '';

if (!empty($search_query)) {
    $search_like = '%' . $search_query . '%';
    $where_conditions[] = 'card_name LIKE ?';
    $params[] = $search_like;
    $types .= 's';
}

// Filtres additionnels (optionnels)
if (isset($_GET['type']) && !empty($_GET['type'])) {
    $where_conditions[] = 'type = ?';
    $params[] = sanitize_string($_GET['type']);
    $types .= 's';
}

if (isset($_GET['level']) && is_numeric($_GET['level'])) {
    $where_conditions[] = 'level = ?';
    $params[] = intval($_GET['level']);
    $types .= 'i';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Comptage total
$count_query = "SELECT COUNT(*) as total FROM T_card $where_clause";

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
    $where_clause
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
    $query_with_limit = "SELECT * FROM T_card ORDER BY card_name ASC LIMIT $limit OFFSET $offset";
    $result = mysqli_query($conn, $query_with_limit);
}

$cards = db_fetch_all($result);

// Configuration SEO
$page_title = !empty($search_query) 
    ? "Recherche : {$search_query} - DeckForge" 
    : "Recherche de cartes - DeckForge";
$meta_description = "Recherchez parmi toutes les cartes Yu-Gi-Oh! disponibles.";

// Inclusion du header
require_once __DIR__ . '/includes/header.php';
?>

<div class="search-container">
    <div class="container">
        <!-- Barre de recherche principale -->
        <section class="search-header">
            <h1 class="search-title">
                <?php if (!empty($search_query)): ?>
                    Résultats pour "<?php echo htmlspecialchars($search_query); ?>"
                <?php else: ?>
                    Recherche de cartes
                <?php endif; ?>
            </h1>
            
            <form action="/search.php" method="GET" class="search-main-form">
                <div class="search-input-wrapper">
                    <input 
                        type="text" 
                        name="q" 
                        class="search-main-input" 
                        placeholder="Rechercher une carte..."
                        value="<?php echo htmlspecialchars($search_query); ?>"
                        autofocus
                    >
                    <button type="submit" class="search-main-button">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        Rechercher
                    </button>
                </div>
            </form>
            
            <!-- Filtres rapides -->
            <div class="quick-filters">
                <a href="/search.php?q=<?php echo urlencode($search_query); ?>&type=Monstre" 
                   class="filter-tag <?php echo (isset($_GET['type']) && $_GET['type'] === 'Monstre') ? 'active' : ''; ?>">
                    Monstres
                </a>
                <a href="/search.php?q=<?php echo urlencode($search_query); ?>&type=Spell card" 
                   class="filter-tag <?php echo (isset($_GET['type']) && $_GET['type'] === 'Spell card') ? 'active' : ''; ?>">
                    Magies
                </a>
                <a href="/search.php?q=<?php echo urlencode($search_query); ?>&type=Trap card" 
                   class="filter-tag <?php echo (isset($_GET['type']) && $_GET['type'] === 'Trap') ? 'active' : ''; ?>">
                    Pièges
                </a>
                <?php if (isset($_GET['type']) || isset($_GET['level'])): ?>
                <a href="/search.php?q=<?php echo urlencode($search_query); ?>" class="filter-tag reset">
                    ✕ Réinitialiser
                </a>
                <?php endif; ?>
            </div>
            
            <!-- Nombre de résultats -->
            <div class="search-results-count">
                <?php if ($total_cards > 0): ?>
                    <strong><?php echo $total_cards; ?></strong> 
                    <?php echo $total_cards > 1 ? 'cartes trouvées' : 'carte trouvée'; ?>
                <?php else: ?>
                    Aucune carte trouvée
                <?php endif; ?>
            </div>
        </section>
        
        <!-- Résultats -->
        <section class="search-results">
            <?php if (!empty($cards)): ?>
            <div class="cards-grid">
                <?php foreach ($cards as $card): ?>
                <article class="card-result">
                    <div class="card-result-image">
                        <img 
                            src="<?php echo get_card_image_or_placeholder($card['id_card']); ?>" 
                            alt="<?php echo htmlspecialchars($card['card_name']); ?>"
                            loading="lazy"
                        >
                    </div>
                    
                    <div class="card-result-content">
                        <h3 class="card-result-name">
                            <?php echo htmlspecialchars($card['card_name']); ?>
                        </h3>
                        
                        <div class="card-result-meta">
                            <span class="card-type-badge">
                                <?php echo htmlspecialchars($card['type'] ?? 'N/A'); ?>
                            </span>
                            
                            <?php if (isset($card['level']) && $card['level'] > 0): ?>
                            <span class="card-level">
                                ★ Niveau <?php echo $card['level']; ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (isset($card['atk']) || isset($card['def'])): ?>
                        <div class="card-stats">
                            <?php if (isset($card['atk']) && $card['atk'] !== null): ?>
                            <span class="stat">ATK: <?php echo $card['atk']; ?></span>
                            <?php endif; ?>
                            
                            <?php if (isset($card['def']) && $card['def'] !== null): ?>
                            <span class="stat">DEF: <?php echo $card['def']; ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($card['description'])): ?>
                        <p class="card-description">
                            <?php 
                            $desc = htmlspecialchars($card['description']);
                            echo strlen($desc) > 150 ? substr($desc, 0, 150) . '...' : $desc;
                            ?>
                        </p>
                        <?php endif; ?>
                        
                        <?php if (is_logged_in()): ?>
                        <div class="card-result-actions">
                            <button 
                                class="btn btn-sm btn-primary btn-add-to-deck" 
                                data-card-id="<?php echo $card['id_card']; ?>"
                                data-card-name="<?php echo htmlspecialchars($card['card_name']); ?>"
                            >
                                + Ajouter au deck
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav class="pagination">
                <?php if ($page > 1): ?>
                <a href="/search.php?q=<?php echo urlencode($search_query); ?>&page=<?php echo $page - 1; ?><?php echo isset($_GET['type']) ? '&type=' . urlencode($_GET['type']) : ''; ?>" 
                   class="pagination-link">
                    ← Précédent
                </a>
                <?php endif; ?>
                
                <div class="pagination-numbers">
                    <?php
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    if ($start > 1): ?>
                        <a href="/search.php?q=<?php echo urlencode($search_query); ?>&page=1<?php echo isset($_GET['type']) ? '&type=' . urlencode($_GET['type']) : ''; ?>" 
                           class="pagination-link">1</a>
                        <?php if ($start > 2): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                    <?php endif;
                    
                    for ($i = $start; $i <= $end; $i++): ?>
                        <a href="/search.php?q=<?php echo urlencode($search_query); ?>&page=<?php echo $i; ?><?php echo isset($_GET['type']) ? '&type=' . urlencode($_GET['type']) : ''; ?>" 
                           class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor;
                    
                    if ($end < $total_pages): ?>
                        <?php if ($end < $total_pages - 1): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                        <a href="/search.php?q=<?php echo urlencode($search_query); ?>&page=<?php echo $total_pages; ?><?php echo isset($_GET['type']) ? '&type=' . urlencode($_GET['type']) : ''; ?>" 
                           class="pagination-link"><?php echo $total_pages; ?></a>
                    <?php endif; ?>
                </div>
                
                <?php if ($page < $total_pages): ?>
                <a href="/search.php?q=<?php echo urlencode($search_query); ?>&page=<?php echo $page + 1; ?><?php echo isset($_GET['type']) ? '&type=' . urlencode($_GET['type']) : ''; ?>" 
                   class="pagination-link">
                    Suivant →
                </a>
                <?php endif; ?>
            </nav>
            <?php endif; ?>
            
            <?php else: ?>
            <!-- Aucun résultat -->
            <div class="no-results-container">
                <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                    <line x1="8" y1="11" x2="14" y2="11"></line>
                </svg>
                
                <h2>Aucune carte trouvée</h2>
                <p>Essayez avec un autre terme de recherche ou modifiez vos filtres.</p>
                
                <div class="no-results-suggestions">
                    <h3>Suggestions :</h3>
                    <ul>
                        <li>Vérifiez l'orthographe</li>
                        <li>Utilisez des termes plus généraux</li>
                        <li>Essayez de rechercher par type de carte</li>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<!-- Modal pour ajouter rapidement au deck -->
<?php if (is_logged_in()): ?>
<div class="modal" id="modal-quick-add">
    <div class="modal-overlay" id="modal-quick-add-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2>Ajouter au deck</h2>
            <button class="modal-close" id="modal-quick-add-close" aria-label="Fermer">&times;</button>
        </div>
        
        <div class="modal-body">
            <p id="quick-add-card-name" class="quick-add-info"></p>
            <p class="quick-add-message">
                Cette fonctionnalité nécessite d'être dans le deck builder.
                Souhaitez-vous y accéder maintenant ?
            </p>
            <div class="modal-actions">
                <a href="/deck-builder.php" class="btn btn-primary btn-block" id="quick-add-goto-builder">
                    Ouvrir le Deck Builder
                </a>
                <button class="btn btn-secondary btn-block" id="quick-add-cancel">
                    Annuler
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addButtons = document.querySelectorAll('.btn-add-to-deck');
    const modal = document.getElementById('modal-quick-add');
    const modalOverlay = document.getElementById('modal-quick-add-overlay');
    const modalClose = document.getElementById('modal-quick-add-close');
    const cancelBtn = document.getElementById('quick-add-cancel');
    const cardNameEl = document.getElementById('quick-add-card-name');
    
    addButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const cardName = this.getAttribute('data-card-name');
            cardNameEl.textContent = 'Carte : ' + cardName;
            modal.classList.add('active');
        });
    });
    
    function closeModal() {
        modal.classList.remove('active');
    }
    
    modalOverlay.addEventListener('click', closeModal);
    modalClose.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
});
</script>
<?php endif; ?>

<style>
/* Styles spécifiques à la page de recherche */
.search-container {
    padding: var(--spacing-xl) 0;
}

.search-header {
    margin-bottom: var(--spacing-2xl);
}

.search-title {
    font-size: 2.5rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: var(--spacing-xl);
    color: var(--text-primary);
}

.search-main-form {
    max-width: 600px;
    margin: 0 auto var(--spacing-lg) auto;
}

.search-input-wrapper {
    display: flex;
    gap: var(--spacing-sm);
}

.search-main-input {
    flex: 1;
    padding: var(--spacing-md) var(--spacing-lg);
    border: 2px solid var(--border-color);
    border-radius: var(--border-radius-lg);
    font-size: 1rem;
    transition: all var(--transition-fast);
}

.search-main-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
}

.search-main-button {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-md) var(--spacing-xl);
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: var(--border-radius-lg);
    font-weight: 600;
    cursor: pointer;
    transition: background-color var(--transition-fast);
}

.search-main-button:hover {
    background-color: var(--primary-hover);
}

.quick-filters {
    display: flex;
    justify-content: center;
    gap: var(--spacing-sm);
    flex-wrap: wrap;
    margin-bottom: var(--spacing-lg);
}

.filter-tag {
    padding: var(--spacing-sm) var(--spacing-lg);
    background-color: var(--bg-tertiary);
    color: var(--text-secondary);
    border-radius: var(--border-radius-md);
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all var(--transition-fast);
}

.filter-tag:hover {
    background-color: var(--border-color);
    color: var(--text-primary);
}

.filter-tag.active {
    background-color: var(--primary-color);
    color: white;
}

.filter-tag.reset {
    background-color: var(--error-color);
    color: white;
}

.search-results-count {
    text-align: center;
    color: var(--text-secondary);
    font-size: 0.9375rem;
}

.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--spacing-lg);
}

.card-result {
    background-color: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    transition: all var(--transition-fast);
}

.card-result:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-4px);
}

.card-result-image {
    width: 100%;
    aspect-ratio: 59/86;
    overflow: hidden;
    background-color: var(--bg-tertiary);
}

.card-result-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.card-result-content {
    padding: var(--spacing-md);
}

.card-result-name {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: var(--spacing-sm);
    color: var(--text-primary);
}

.card-result-meta {
    display: flex;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-sm);
    flex-wrap: wrap;
}

.card-type-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    background-color: var(--primary-color);
    color: white;
    border-radius: var(--border-radius-sm);
    font-size: 0.75rem;
    font-weight: 600;
}

.card-level {
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.card-stats {
    display: flex;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-sm);
    font-size: 0.875rem;
    font-weight: 500;
}

.card-description {
    font-size: 0.875rem;
    color: var(--text-secondary);
    line-height: 1.5;
    margin-bottom: var(--spacing-md);
}

.card-result-actions {
    padding-top: var(--spacing-sm);
    border-top: 1px solid var(--border-color);
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: var(--spacing-md);
    margin-top: var(--spacing-2xl);
}

.pagination-numbers {
    display: flex;
    gap: var(--spacing-xs);
}

.pagination-link {
    padding: var(--spacing-sm) var(--spacing-md);
    background-color: var(--bg-primary);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-md);
    text-decoration: none;
    font-weight: 500;
    transition: all var(--transition-fast);
}

.pagination-link:hover {
    background-color: var(--bg-tertiary);
    border-color: var(--primary-color);
}

.pagination-link.active {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.pagination-dots {
    padding: var(--spacing-sm);
    color: var(--text-muted);
}

.no-results-container {
    text-align: center;
    padding: var(--spacing-2xl);
}

.no-results-container svg {
    color: var(--text-muted);
    margin-bottom: var(--spacing-lg);
}

.no-results-container h2 {
    font-size: 1.5rem;
    margin-bottom: var(--spacing-md);
    color: var(--text-primary);
}

.no-results-container p {
    color: var(--text-secondary);
    margin-bottom: var(--spacing-lg);
}

.no-results-suggestions {
    max-width: 400px;
    margin: 0 auto;
    text-align: left;
}

.no-results-suggestions h3 {
    font-size: 1.125rem;
    margin-bottom: var(--spacing-sm);
}

.no-results-suggestions ul {
    list-style: none;
    padding: 0;
}

.no-results-suggestions li {
    padding: var(--spacing-sm) 0;
    color: var(--text-secondary);
}

.no-results-suggestions li:before {
    content: "→ ";
    color: var(--primary-color);
    font-weight: bold;
}

.quick-add-info {
    font-weight: 600;
    font-size: 1.125rem;
    margin-bottom: var(--spacing-md);
    color: var(--text-primary);
}

.quick-add-message {
    margin-bottom: var(--spacing-lg);
    color: var(--text-secondary);
}

.modal-actions {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

@media (max-width: 768px) {
    .search-title {
        font-size: 1.75rem;
    }
    
    .cards-grid {
        grid-template-columns: 1fr;
    }
    
    .pagination {
        flex-wrap: wrap;
    }
}
</style>

<?php
// Fermeture de la connexion
db_close($conn);

// Inclusion du footer
require_once __DIR__ . '/includes/footer.php';
?>