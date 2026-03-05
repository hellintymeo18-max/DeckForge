<?php
/**
 * Page de support
 * 
 * Permet à l'utilisateur de choisir entre récupération
 * de pseudo ou de mot de passe
 * 
 * @author DeckForge Team
 * @version 1.0
 */

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

// Configuration SEO
$page_title = 'Support - DeckForge';
$meta_description = 'Besoin d\'aide pour accéder à votre compte ? Récupérez votre pseudo ou réinitialisez votre mot de passe.';

// Inclusion du header
require_once __DIR__ . '/includes/header.php';
?>

<div class="support-container">
    <div class="container">
        <section class="support-section">
            <h1 class="page-title">Support DeckForge</h1>
            <p class="page-subtitle">Comment pouvons-nous vous aider ?</p>
            
            <div class="support-options">
                <a href="/forgot-username.php" class="support-card">
                    <div class="support-card-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <h2>J'ai oublié mon pseudo</h2>
                    <p>Récupérez votre nom d'utilisateur en fournissant votre email et mot de passe</p>
                    <span class="support-card-link">Récupérer mon pseudo →</span>
                </a>
                
                <a href="/forgot-password.php" class="support-card">
                    <div class="support-card-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </div>
                    <h2>J'ai oublié mon mot de passe</h2>
                    <p>Réinitialisez votre mot de passe en recevant un email de récupération</p>
                    <span class="support-card-link">Réinitialiser mon mot de passe →</span>
                </a>
            </div>
            
            <div class="support-back">
                <a href="/index.php" class="btn btn-secondary">← Retour à l'accueil</a>
            </div>
        </section>
    </div>
</div>

<?php
// Inclusion du footer
require_once __DIR__ . '/includes/footer.php';
?>