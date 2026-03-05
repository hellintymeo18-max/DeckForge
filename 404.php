<?php
/**
 * Page 404 - Page non trouvée
 * 
 * Affiche une page d'erreur personnalisée et conviviale
 * 
 * @author DeckForge Team
 * @version 1.0
 */

http_response_code(404);

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

// Configuration SEO
$page_title = '404 - Page non trouvée - DeckForge';
$meta_description = 'La page que vous recherchez est introuvable.';

// Inclusion du header
require_once __DIR__ . '/includes/header.php';
?>

<div class="error-404-container">
    <div class="container">
        <section class="error-404-section">
            <div class="error-404-content">
                <h1 class="error-404-title">404</h1>
                <p class="error-404-subtitle">Page non trouvée</p>
                <p class="error-404-description">
                    Désolé, la page que vous recherchez n'existe pas ou a été déplacée.
                </p>
                
                <div class="error-404-actions">
                    <a href="<?php echo is_logged_in() ? '/home.php' : '/index.php'; ?>" class="btn btn-primary">
                        Retour à l'accueil
                    </a>
                    <a href="javascript:history.back()" class="btn btn-secondary">
                        Page précédente
                    </a>
                </div>
            </div>
            
            <div class="error-404-image">
                <svg width="300" height="300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.5">
                    <circle cx="12" cy="12" r="10" opacity="0.2"/>
                    <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8z" opacity="0.5"/>
                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" stroke-width="1"/>
                </svg>
            </div>
        </section>
    </div>
</div>

<style>
.error-404-container {
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--spacing-2xl) 0;
}

.error-404-section {
    display: flex;
    align-items: center;
    gap: var(--spacing-2xl);
    flex-wrap: wrap;
    justify-content: center;
}

.error-404-content {
    flex: 1;
    min-width: 300px;
    text-align: center;
}

.error-404-title {
    font-size: 8rem;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1;
    margin-bottom: var(--spacing-md);
}

.error-404-subtitle {
    font-size: 2rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: var(--spacing-md);
}

.error-404-description {
    font-size: 1.125rem;
    color: var(--text-secondary);
    margin-bottom: var(--spacing-xl);
}

.error-404-actions {
    display: flex;
    gap: var(--spacing-md);
    justify-content: center;
    flex-wrap: wrap;
}

.error-404-image {
    flex: 0 0 300px;
    color: var(--text-muted);
}

@media (max-width: 768px) {
    .error-404-title {
        font-size: 5rem;
    }
    
    .error-404-image {
        flex: 0 0 200px;
    }
}
</style>

<?php
// Inclusion du footer
require_once __DIR__ . '/includes/footer.php';
?>