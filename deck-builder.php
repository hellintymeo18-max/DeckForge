<?php
/**
 * Page de construction de deck
 * 
 * Permet de créer et éditer des decks Yu-Gi-Oh!
 * avec système de drag & drop et validation
 * 
 * @author DeckForge Team
 * @version 1.0
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

// Vérification de la connexion
require_login();

$conn = db_connect();
$user_id = get_user_id();

// Vérification si édition d'un deck existant
$editing_deck = false;
$deck_data = null;
$deck_cards = array();

if (isset($_GET['deck_id'])) {
    $deck_id = intval($_GET['deck_id']);
    
    // Vérification que le deck appartient à l'utilisateur
    $stmt = db_prepare($conn, "SELECT * FROM T_deck WHERE id_deck = ? AND id_user = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $deck_id, $user_id);
    $result = db_execute($stmt);
    $deck_data = db_fetch_one($result);
    
    if ($deck_data) {
        $editing_deck = true;
        
        // Récupération des cartes du deck
        $stmt = db_prepare($conn, "
            SELECT c.*, cd.id_card
            FROM T_card_deck cd
            JOIN T_card c ON cd.id_card = c.id_card
            WHERE cd.id_deck = ?
        ");
        mysqli_stmt_bind_param($stmt, 'i', $deck_id);
        $result = db_execute($stmt);
        $deck_cards = db_fetch_all($result);
    }
}

// Configuration SEO
$page_title = $editing_deck ? 'Éditer le deck - DeckForge' : 'Créer un deck - DeckForge';
$meta_description = 'Créez et éditez vos decks Yu-Gi-Oh! avec notre éditeur intuitif.';

// Inclusion du header
require_once __DIR__ . '/includes/header.php';
?>

<div class="deck-builder-container">
    <div class="container-fluid">
        <!-- Header du deck builder -->
        <div class="deck-builder-header">
            <div class="deck-builder-title">
                <h1><?php echo $editing_deck ? 'Éditer le deck' : 'Créer un deck'; ?></h1>
                <input 
                    type="text" 
                    id="deck-name-input" 
                    class="deck-name-input" 
                    placeholder="Nom du deck"
                    value="<?php echo $editing_deck ? htmlspecialchars($deck_data['name_deck']) : ''; ?>"
                    maxlength="50"
                >
            </div>
            
            <div class="deck-builder-actions">
                <button class="btn btn-secondary" id="btn-back">
                    ← Retour
                </button>
                <button class="btn btn-primary" id="btn-save-deck">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Sauvegarder
                </button>
            </div>
        </div>
        
        <!-- Zones de construction -->
        <div class="deck-builder-workspace">
            <!-- Zone du deck -->
            <div class="deck-zone">
                <!-- Main Deck -->
                <div class="deck-section">
                    <div class="deck-section-header">
                        <h3>Main Deck</h3>
                        <span class="card-count" id="main-deck-count">0 / 60</span>
                    </div>
                    <div 
                        class="deck-cards-grid" 
                        id="main-deck" 
                        data-deck-type="main"
                    >
                        <?php if ($editing_deck): ?>
                            <?php foreach ($deck_cards as $card): ?>
                                <?php if (!in_array($card['type'], ['Xyz', 'Synchro', 'Fusion', 'Link'])): ?>
                                <div class="deck-card" data-card-id="<?php echo $card['id_card']; ?>">
                                    <img src="<?php echo get_card_image_or_placeholder($card['id_card']); ?>" alt="<?php echo htmlspecialchars($card['card_name']); ?>">
                                    <button class="card-remove-btn">×</button>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Extra Deck -->
                <div class="deck-section">
                    <div class="deck-section-header">
                        <h3>Extra Deck</h3>
                        <span class="card-count" id="extra-deck-count">0 / 15</span>
                    </div>
                    <div 
                        class="deck-cards-grid deck-cards-grid-small" 
                        id="extra-deck" 
                        data-deck-type="extra"
                    >
                        <?php if ($editing_deck): ?>
                            <?php foreach ($deck_cards as $card): ?>
                                <?php if (in_array($card['type'], ['Xyz', 'Synchro', 'Fusion', 'Link'])): ?>
                                <div class="deck-card" data-card-id="<?php echo $card['id_card']; ?>">
                                    <img src="<?php echo get_card_image_or_placeholder($card['id_card']); ?>" alt="<?php echo htmlspecialchars($card['card_name']); ?>">
                                    <button class="card-remove-btn">×</button>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Zone de recherche de cartes -->
            <div class="cards-browser">
                <div class="cards-browser-header">
                    <h3>Liste des cartes</h3>
                    
                    <div class="cards-search">
                        <input 
                            type="text" 
                            id="cards-search-input" 
                            class="form-input" 
                            placeholder="Rechercher une carte..."
                        >
                        <button class="btn btn-icon" id="btn-filters" aria-label="Filtres">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="cards-list" id="cards-list">
                    <!-- Les cartes seront chargées ici via AJAX -->
                    <div class="loading">Chargement des cartes...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de filtres -->
<div class="modal" id="modal-filters">
    <div class="modal-overlay" id="modal-filters-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2>Filtres</h2>
            <button class="modal-close" id="modal-filters-close" aria-label="Fermer">&times;</button>
        </div>
        
        <div class="modal-body">
            <form id="filters-form">
                <!-- Types de cartes -->
                <div class="filter-group">
                    <h4>Type de carte</h4>
                    <div class="filter-checkboxes">
                        <label class="filter-checkbox">
                            <input type="checkbox" name="type[]" value="Spell Card">
                            <span>Magie</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" name="type[]" value="Trap Card">
                            <span>Piège</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" name="type[]" value="Normal Monster">
                            <span>Monstre</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" name="type[]" value="Effect Monster">
                            <span>Monstre a effet</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" name="type[]" value="Tuner Monster">
                            <span>syntoniseur</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" name="type[]" value="Pendulum Effect Monster">
                            <span>Monstre pendule</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" name="type[]" value="Fusion Monster">
                            <span>Monstre fusion</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" name="type[]" value="XYZ Monster">
                            <span>Monstre xyz</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" name="type[]" value="link Monster">
                            <span>Monstre xyz</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" name="type[]" value="synchro Monster">
                            <span>Monstre synchro</span>
                        </label>
                    </div>
                </div>
                
                <!-- Attributs (pour monstres) -->
                <div class="filter-group">
                    <h4>Attribut</h4>
                    <div class="filter-checkboxes">
                        <label class="filter-checkbox">
                            <input type="checkbox" name="attribut[]" value="DARK">
                            <span>DARK</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" name="attribut[]" value="LIGHT">
                            <span>LIGHT</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" name="attribut[]" value="FIRE">
                            <span>FIRE</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" name="attribut[]" value="WATER">
                            <span>WATER</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" name="attribut[]" value="EARTH">
                            <span>EARTH</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" name="attribut[]" value="WIND">
                            <span>WIND</span>
                        </label>
                        <label class="filter-checkbox">
                            <input type="checkbox" name="attribut[]" value="DIVINE">
                            <span>DIVINE</span>
                        </label>
                    </div>
                </div>
                
                <!-- Niveau -->
                <div class="filter-group">
                    <h4>Niveau</h4>
                    <div class="filter-range">
                        <input type="number" name="level_min" min="1" max="12" placeholder="Min">
                        <span>à</span>
                        <input type="number" name="level_max" min="1" max="12" placeholder="Max">
                    </div>
                </div>
                
                <!-- ATK/DEF -->
                <div class="filter-group">
                    <h4>ATK</h4>
                    <div class="filter-range">
                        <input type="number" name="atk_min" min="0" placeholder="Min">
                        <span>à</span>
                        <input type="number" name="atk_max" placeholder="Max">
                    </div>
                </div>
                
                <div class="filter-group">
                    <h4>DEF</h4>
                    <div class="filter-range">
                        <input type="number" name="def_min" min="0" placeholder="Min">
                        <span>à</span>
                        <input type="number" name="def_max" placeholder="Max">
                    </div>
                </div>
                
                <div class="filter-actions">
                    <button type="button" class="btn btn-secondary" id="btn-reset-filters">Réinitialiser</button>
                    <button type="submit" class="btn btn-primary">Appliquer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de sauvegarde -->
<div class="modal" id="modal-save">
    <div class="modal-overlay" id="modal-save-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2>Sauvegarde du deck</h2>
            <button class="modal-close" id="modal-save-close" aria-label="Fermer">&times;</button>
        </div>
        
        <div class="modal-body">
            <form id="save-deck-form">
                <input type="hidden" id="deck-id" name="deck_id" value="<?php echo $editing_deck ? $deck_data['id_deck'] : ''; ?>">
                
                <div class="form-group">
                    <label>Visibilité du deck</label>
                    <div class="visibility-options">
                        <label class="visibility-option">
                            <input 
                                type="radio" 
                                name="visibility" 
                                value="1" 
                                <?php echo ($editing_deck && $deck_data['is_public']) ? 'checked' : ''; ?>
                            >
                            <span class="visibility-label">
                                <strong>Public</strong>
                                <small>Visible par tous les utilisateurs</small>
                            </span>
                        </label>
                        <label class="visibility-option">
                            <input 
                                type="radio" 
                                name="visibility" 
                                value="0" 
                                <?php echo ($editing_deck && !$deck_data['is_public']) ? 'checked' : 'checked'; ?>
                            >
                            <span class="visibility-label">
                                <strong>Privé</strong>
                                <small>Visible uniquement par vous</small>
                            </span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="save-deck-name">Nom du deck</label>
                    <input 
                        type="text" 
                        id="save-deck-name" 
                        name="deck_name" 
                        class="form-input" 
                        required
                        maxlength="50"
                    >
                </div>
                
                <div class="form-group">
                    <label>Sélectionnez 5 cartes à mettre en avant</label>
                    <div class="featured-cards-selector" id="featured-cards-selector">
                        <!-- Les cartes seront ajoutées dynamiquement -->
                    </div>
                    <small class="form-hint">Cliquez sur les cartes pour les sélectionner</small>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">
                        Sauvegarder le deck
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Configuration pour le deck builder
window.DECK_BUILDER_CONFIG = {
    editing: <?php echo $editing_deck ? 'true' : 'false'; ?>,
    deckId: <?php echo $editing_deck ? $deck_data['id_deck'] : 'null'; ?>,
    deckName: <?php echo $editing_deck ? json_encode($deck_data['name_deck']) : '""'; ?>
};
</script>

<?php
// Fermeture de la connexion
db_close($conn);

// Inclusion du footer
require_once __DIR__ . '/includes/footer.php';
?>