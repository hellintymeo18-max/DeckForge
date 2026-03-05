<?php
/**
 * Page d'accueil du site (utilisateurs connectés)
 * 
 * Affiche les decks publics et permet l'accès au profil
 * et à la création de decks
 * 
 * @author DeckForge Team
 * @version 1.0
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

// Vérification de la connexion
require_login();

// Connexion à la base de données
$conn = db_connect();

$user_id = get_user_id();

// Récupération des decks publics populaires
$query = "
    SELECT 
        d.id_deck,
        d.name_deck,
        d.number_like,
        u.username,
        (SELECT COUNT(*) FROM T_card_deck WHERE id_deck = d.id_deck) as card_count
    FROM T_deck d
    JOIN T_user u ON d.id_user = u.id_user
    WHERE d.is_public = 1
    ORDER BY d.number_like DESC
    LIMIT 12
";

$result = mysqli_query($conn, $query);
$public_decks = db_fetch_all($result);

// Récupération des decks de l'utilisateur pour le modal
$stmt = db_prepare($conn, "
    SELECT 
        id_deck,
        name_deck,
        is_public,
        number_like,
        (SELECT COUNT(*) FROM T_card_deck WHERE id_deck = d.id_deck) as card_count
    FROM T_deck d
    WHERE id_user = ?
    ORDER BY id_deck DESC
");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
$result = db_execute($stmt);
$user_decks = db_fetch_all($result);

// Récupération des informations utilisateur
$stmt = db_prepare($conn, "SELECT username, email FROM T_user WHERE id_user = ?");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
$result = db_execute($stmt);
$user_info = db_fetch_one($result);

// Configuration SEO
$page_title = 'Accueil - DeckForge';
$meta_description = 'Gérez vos decks Yu-Gi-Oh! et explorez les créations de la communauté.';

// Inclusion du header
require_once __DIR__ . '/includes/header.php';
?>

<div class="home-container">
    <!-- Section actions rapides -->
    <section class="quick-actions-section">
        <div class="container">
            <div class="quick-actions">
                <a href="/deck-builder.php" class="quick-action-card">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <h3>Créer un deck</h3>
                    <p>Construisez votre deck de rêve</p>
                </a>
                
                <button class="quick-action-card" id="btn-my-decks">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="2" y="7" width="20" height="15" rx="2" ry="2"></rect>
                        <polyline points="17 2 12 7 7 2"></polyline>
                    </svg>
                    <h3>Mes decks</h3>
                    <p>Gérez vos créations</p>
                </button>
                
                <a href="/search.php" class="quick-action-card">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <h3>Rechercher</h3>
                    <p>Trouvez des cartes</p>
                </a>
            </div>
        </div>
    </section>
    
    <!-- Section decks populaires -->
    <section class="popular-decks-section">
        <div class="container">
            <h2 class="section-title">Decks populaires de la communauté</h2>
            
            <div class="decks-grid">
                <?php foreach ($public_decks as $deck): ?>
                <article class="deck-card">
                    <div class="deck-card-header">
                        <h3 class="deck-name">
                            <a href="/deck/<?php echo $deck['id_deck'] . '-' . create_slug($deck['name_deck']); ?>">
                                <?php echo htmlspecialchars($deck['name_deck']); ?>
                            </a>
                        </h3>
                        <span class="deck-author">par <?php echo htmlspecialchars($deck['username']); ?></span>
                    </div>
                    
                    <div class="deck-card-body">
                        <div class="deck-preview-cards">
                            <?php for ($i = 0; $i < 5; $i++): ?>
                            <div class="preview-card-slot">
                                <img src="/assets/images/card-back.jpg" alt="Carte du deck" loading="lazy">
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="deck-card-footer">
                        <div class="deck-stats">
                            <span class="stat">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                                </svg>
                                <?php echo $deck['number_like']; ?>
                            </span>
                            <span class="stat">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="2" y="7" width="20" height="15" rx="2" ry="2"></rect>
                                    <polyline points="17 2 12 7 7 2"></polyline>
                                </svg>
                                <?php echo $deck['card_count']; ?> cartes
                            </span>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
                
                <?php if (empty($public_decks)): ?>
                <div class="no-decks">
                    <p>Aucun deck public disponible pour le moment.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<!-- Modal Profil -->
<div class="modal" id="modal-profile">
    <div class="modal-overlay" id="modal-profile-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2>Profil</h2>
            <button class="modal-close" id="modal-profile-close" aria-label="Fermer">&times;</button>
        </div>
        
        <div class="modal-body">
            <div class="profile-info">
                <div class="profile-field">
                    <label>Nom d'utilisateur</label>
                    <p><?php echo htmlspecialchars($user_info['username']); ?></p>
                </div>
                
                <div class="profile-field">
                    <label>Email</label>
                    <p><?php echo htmlspecialchars($user_info['email']); ?></p>
                </div>
                
                <div class="profile-field">
                    <label>Nombre de decks</label>
                    <p><?php echo count($user_decks); ?></p>
                </div>
            </div>
            
            <div class="profile-actions">
                <button class="btn btn-secondary btn-block" id="btn-view-my-decks">Mes decks</button>
                <a href="/api/logout.php" class="btn btn-danger btn-block">Se déconnecter</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Mes Decks -->
<div class="modal" id="modal-my-decks">
    <div class="modal-overlay" id="modal-my-decks-overlay"></div>
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2>Mes decks</h2>
            <button class="modal-close" id="modal-my-decks-close" aria-label="Fermer">&times;</button>
        </div>
        
        <div class="modal-body">
            <?php if (!empty($user_decks)): ?>
            <div class="my-decks-list">
                <?php foreach ($user_decks as $deck): ?>
                <div class="my-deck-item">
                    <div class="my-deck-info">
                        <h3><?php echo htmlspecialchars($deck['name_deck']); ?></h3>
                        <div class="my-deck-meta">
                            <span class="badge <?php echo $deck['is_public'] ? 'badge-success' : 'badge-secondary'; ?>">
                                <?php echo $deck['is_public'] ? 'Public' : 'Privé'; ?>
                            </span>
                            <span><?php echo $deck['card_count']; ?> cartes</span>
                            <span><?php echo $deck['number_like']; ?> likes</span>
                        </div>
                    </div>
                    <div class="my-deck-actions">
                        <a href="/deck-builder.php?deck_id=<?php echo $deck['id_deck']; ?>" class="btn btn-sm btn-primary">Éditer</a>
                        <button class="btn btn-sm btn-danger btn-delete-deck" data-deck-id="<?php echo $deck['id_deck']; ?>">Supprimer</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-decks">
                <p>Vous n'avez pas encore créé de deck.</p>
                <a href="/deck-builder.php" class="btn btn-primary">Créer mon premier deck</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Fermeture de la connexion
db_close($conn);

// Inclusion du footer
require_once __DIR__ . '/includes/footer.php';
?>