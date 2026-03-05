<?php
/**
 * Page de récupération du pseudo
 * 
 * Permet à l'utilisateur de récupérer son nom d'utilisateur
 * en fournissant son email et son mot de passe
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
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    $errors = array();
    
    if (empty($email) || !validate_email($email)) {
        $errors[] = 'Email invalide';
    }
    
    if (empty($password)) {
        $errors[] = 'Le mot de passe est requis';
    }
    
    if (empty($errors)) {
        $conn = db_connect();
        
        // Recherche de l'utilisateur par email
        $stmt = db_prepare($conn, "SELECT username, password FROM T_user WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
        $result = db_execute($stmt);
        $user = db_fetch_one($result);
        
        if ($user && verify_password($password, $user['password'])) {
            // Envoi du pseudo par email
            $subject = 'Récupération de votre nom d\'utilisateur - DeckForge';
            $message = "
                <html>
                <head>
                    <title>Récupération de votre nom d'utilisateur</title>
                </head>
                <body>
                    <h1>Récupération de votre nom d'utilisateur</h1>
                    <p>Votre nom d'utilisateur est : <strong>" . htmlspecialchars($user['username']) . "</strong></p>
                    <p>Vous pouvez maintenant vous connecter à votre compte.</p>
                    <p><a href='" . get_base_url() . "/index.php'>Se connecter</a></p>
                </body>
                </html>
            ";
            
            send_email($email, $subject, $message);
            
            db_close($conn);
            
            set_flash_message('success', 'Un email contenant votre nom d\'utilisateur a été envoyé à votre adresse.');
            redirect('/index.php');
        } else {
            db_close($conn);
            $errors[] = 'Email ou mot de passe incorrect';
        }
    }
    
    if (!empty($errors)) {
        set_flash_message('error', implode('<br>', $errors));
    }
}

// Configuration SEO
$page_title = 'Récupération du pseudo - DeckForge';
$meta_description = 'Récupérez votre nom d\'utilisateur en fournissant votre email et mot de passe.';

// Inclusion du header
require_once __DIR__ . '/includes/header.php';
?>

<div class="recovery-container">
    <div class="container">
        <section class="recovery-section">
            <h1 class="page-title">J'ai oublié mon pseudo</h1>
            
            <div class="recovery-form-wrapper">
                <p class="recovery-instructions">
                    Pour récupérer votre nom d'utilisateur, veuillez fournir votre adresse email 
                    de récupération et votre mot de passe. Nous vous enverrons votre pseudo par email.
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
                        <label for="recovery-password">Mot de passe</label>
                        <input 
                            type="password" 
                            id="recovery-password" 
                            name="password" 
                            class="form-input" 
                            required
                            autocomplete="current-password"
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