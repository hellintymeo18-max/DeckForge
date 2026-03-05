<?php
/**
 * Page de réinitialisation du mot de passe
 * 
 * Permet à l'utilisateur de recevoir un email pour
 * réinitialiser son mot de passe
 * 
 * @author DeckForge Team
 * @version 1.0
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? sanitize_string($_POST['email']) : '';
    
    $errors = array();
    
    if (empty($email) || !validate_email($email)) {
        $errors[] = 'Email invalide';
    }
    
    if (empty($errors)) {
        $conn = db_connect();
        
        // Recherche de l'utilisateur par email
        $stmt = db_prepare($conn, "SELECT id_user, username, email FROM T_user WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
        $result = db_execute($stmt);
        $user = db_fetch_one($result);
        
        if ($user) {
            // Génération d'un token de réinitialisation
            $reset_token = generate_token();
            $reset_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Stockage du token dans la base (simulation - à adapter selon votre schéma)
            // Note: Dans une vraie application, il faudrait une table dédiée pour les tokens de reset
            // Pour cette démo, on envoie simplement un email avec instructions
            
            // Envoi de l'email de réinitialisation
            $reset_link = get_base_url() . '/reset-password.php?token=' . $reset_token . '&email=' . urlencode($email);
            
            $subject = 'Réinitialisation de votre mot de passe - DeckForge';
            $message = "
                <html>
                <head>
                    <title>Réinitialisation de votre mot de passe</title>
                </head>
                <body>
                    <h1>Réinitialisation de votre mot de passe</h1>
                    <p>Bonjour " . htmlspecialchars($user['username']) . ",</p>
                    <p>Vous avez demandé à réinitialiser votre mot de passe.</p>
                    <p>Cliquez sur le lien ci-dessous pour créer un nouveau mot de passe :</p>
                    <p><a href='" . $reset_link . "'>Réinitialiser mon mot de passe</a></p>
                    <p>Ce lien expirera dans 24 heures.</p>
                    <p>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
                </body>
                </html>
            ";
            
            send_email($email, $subject, $message);
            
            db_close($conn);
            
            set_flash_message('success', 'Un email contenant les instructions de réinitialisation a été envoyé à votre adresse.');
            redirect('/index.php');
        } else {
            db_close($conn);
            // Pour des raisons de sécurité, on affiche le même message même si l'email n'existe pas
            set_flash_message('success', 'Si cette adresse email existe dans notre système, un email de réinitialisation a été envoyé.');
            redirect('/index.php');
        }
    }
    
    if (!empty($errors)) {
        set_flash_message('error', implode('<br>', $errors));
    }
}

// Configuration SEO
$page_title = 'Réinitialisation du mot de passe - DeckForge';
$meta_description = 'Réinitialisez votre mot de passe en recevant un email de récupération.';

// Inclusion du header
require_once __DIR__ . '/includes/header.php';
?>

<div class="recovery-container">
    <div class="container">
        <section class="recovery-section">
            <h1 class="page-title">J'ai oublié mon mot de passe</h1>
            
            <div class="recovery-form-wrapper">
                <p class="recovery-instructions">
                    Pour réinitialiser votre mot de passe, veuillez fournir votre adresse email 
                    de récupération. Nous vous enverrons un lien pour créer un nouveau mot de passe.
                </p>
                
                <form method="POST" class="recovery-form">
                    <div class="form-group">
                        <label for="recovery-email">Adresse e-mail de récupération</label>
                        <input 
                            type="email" 
                            id="recovery-email" 
                            name="email" 
                            class="form-input" 
                            required
                            autocomplete="email"
                        >
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Envoyer</button>
                    </div>
                </form>
                
                <div class="recovery-links">
                    <a href="/support.php" class="btn btn-secondary">← Retour au support</a>
                    <a href="/index.php" class="form-link">Retour à l'accueil</a>
                </div>
            </div>
        </section>
    </div>
</div>

<?php
// Inclusion du footer
require_once __DIR__ . '/includes/footer.php';
?>