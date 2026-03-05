<?php
/**
 * Page d'accueil du site (utilisateurs non connectés)
 * 
 * Affiche les decks publics populaires et permet l'accès
 * aux modals de connexion et inscription
 * 
 * @author DeckForge Team
 * @version 1.0
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

// Redirection si déjà connecté
redirect_if_logged_in();

// Connexion à la base de données
$conn = db_connect();

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

// Configuration SEO
$page_title = 'DeckForge - Yu-Gi-Oh! Deck Manager';
$meta_description = 'Créez, gérez et partagez vos decks Yu-Gi-Oh! en ligne. Explorez les decks populaires de la communauté.';

// Inclusion du header
require_once __DIR__ . '/includes/header.php';
?>

<div class="home-container">
    <!-- Section héro -->
    <section class="hero-section">
        <div class="container">
            <h1 class="hero-title">Bienvenue sur DeckForge</h1>
            <p class="hero-subtitle">Créez et gérez vos decks Yu-Gi-Oh! comme un pro</p>
            <button class="btn btn-primary btn-large" id="btn-get-started">Commencer maintenant</button>
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
                        <!-- Placeholder pour les 5 cartes mises en avant -->
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
                    <p>Soyez le premier à créer et partager un deck !</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<!-- Modal de connexion -->
<div class="modal" id="modal-login">
    <div class="modal-overlay" id="modal-login-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2>Connexion</h2>
            <button class="modal-close" id="modal-login-close" aria-label="Fermer">&times;</button>
        </div>
        
        <div class="modal-body">
            <form id="form-login" method="POST" action="/api/login.php">

            <form id="form-login" method="POST" action="/DeckForgeV2/api/login.php">
                <div class="form-group">
                    <label for="login-username">Nom d'utilisateur</label>
                    <input 
                        type="text" 
                        id="login-username" 
                        name="username" 
                        class="form-input" 
                        required
                        autocomplete="username"
                    >
                </div>
                
                <div class="form-group">
                    <label for="login-password">Mot de passe</label>
                    <input 
                        type="password" 
                        id="login-password" 
                        name="password" 
                        class="form-input" 
                        required
                        autocomplete="current-password"
                    >
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
                </div>
                
                <div class="form-links">
                    <a href="#" id="link-register" class="form-link">Je n'ai pas encore de compte</a>
                    <a href="/support.php" class="form-link">
                    J'ai besoin d'aide pour accéder à mon compte
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal d'inscription -->
<div class="modal" id="modal-register">
    <div class="modal-overlay" id="modal-register-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2>Créer un compte</h2>
            <button class="modal-close" id="modal-register-close" aria-label="Fermer">&times;</button>
        </div>
        
        <div class="modal-body">
            <form id="form-register" method="POST" action="/api/register.php">

            <form id="form-register" method="POST" action="/DeckForgeV2/api/register.php">
                <div class="form-group">
                    <label for="register-username">Nom d'utilisateur</label>
                    <input 
                        type="text" 
                        id="register-username" 
                        name="username" 
                        class="form-input" 
                        required
                        pattern="[a-zA-Z0-9_]{3,20}"
                        title="3-20 caractères alphanumériques et underscore uniquement"
                        autocomplete="username"
                    >
                    <small class="form-hint">3-20 caractères alphanumériques et underscore</small>
                </div>
                
                <div class="form-group">
                    <label for="register-email">Email</label>
                    <input 
                        type="email" 
                        id="register-email" 
                        name="email" 
                        class="form-input" 
                        required
                        autocomplete="email"
                    >
                </div>
                
                <div class="form-group">
                    <label for="register-password">Mot de passe</label>
                    <input 
                        type="password" 
                        id="register-password" 
                        name="password" 
                        class="form-input" 
                        required
                        minlength="8"
                        autocomplete="new-password"
                    >
                    <small class="form-hint">Minimum 8 caractères</small>
                </div>
                
                <div class="form-group">
                    <label for="register-confirm-password">Confirmer le mot de passe</label>
                    <input 
                        type="password" 
                        id="register-confirm-password" 
                        name="confirm_password" 
                        class="form-input" 
                        required
                        minlength="8"
                        autocomplete="new-password"
                    >
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">Créer mon compte</button>
                </div>
                
                <div class="form-links">
                    <a href="#" id="link-login" class="form-link">J'ai déjà un compte</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Fermeture de la connexion
db_close($conn);

// Inclusion du footer
require_once __DIR__ . '/includes/footer.php';
?>