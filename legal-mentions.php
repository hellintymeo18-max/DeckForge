<?php
/**
 * Page des mentions légales
 * 
 * Affiche les informations légales obligatoires du site
 * 
 * @author DeckForge Team
 * @version 1.0
 */

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

// Configuration SEO
$page_title = 'Mentions légales - DeckForge';
$meta_description = 'Mentions légales et conditions d\'utilisation de DeckForge.';

// Inclusion du header
require_once __DIR__ . '/includes/header.php';
?>

<div class="legal-container">
    <div class="container">
        <section class="legal-section">
            <h1 class="page-title">Mentions légales</h1>
            
            <div class="legal-content">
                <h2>1. Présentation du site</h2>
                <p>
                    Le site web <strong>DeckForge</strong> est accessible à l'adresse suivante : 
                    <?php echo htmlspecialchars(get_base_url()); ?>
                </p>
                <p>
                    DeckForge est un outil de gestion de decks Yu-Gi-Oh! en ligne permettant 
                    aux joueurs de créer, éditer et partager leurs decks.
                </p>
                
                <h2>2. Éditeur du site</h2>
                <p>
                    <strong>Nom :</strong> DeckForge<br>
                    <strong>Contact :</strong> <a href="mailto:contact@deckforge.local">contact@deckforge.local</a>
                </p>
                
                <h2>3. Hébergement</h2>
                <p>
                    Le site est hébergé localement en environnement de développement.<br>
                    <strong>Hébergeur :</strong> Laragon (développement local)
                </p>
                
                <h2>4. Propriété intellectuelle</h2>
                <p>
                    L'ensemble du contenu du site (structure, textes, logos, boutons, images) 
                    est la propriété exclusive de DeckForge, à l'exception du contenu 
                    fourni par les utilisateurs.
                </p>
                <p>
                    Yu-Gi-Oh! est une marque déposée de KONAMI DIGITAL ENTERTAINMENT. 
                    DeckForge n'est pas affilié à KONAMI et n'est pas un produit officiel.
                </p>
                <p>
                    Toute reproduction, distribution, modification, adaptation, retransmission 
                    ou publication de ces différents éléments est strictement interdite sans 
                    l'accord exprès par écrit de DeckForge.
                </p>
                
                <h2>5. Données personnelles</h2>
                <p>
                    Conformément à la loi Informatique et Libertés du 6 janvier 1978, 
                    vous disposez d'un droit d'accès, de rectification et de suppression 
                    des données vous concernant.
                </p>
                <p>
                    Les informations recueillies sur ce site sont enregistrées dans un fichier 
                    informatisé par DeckForge pour la gestion des comptes utilisateurs. 
                    Elles sont conservées pendant la durée de vie du compte et sont destinées 
                    au service de gestion du site.
                </p>
                <p>
                    Les données collectées sont :
                </p>
                <ul>
                    <li>Nom d'utilisateur</li>
                    <li>Adresse email</li>
                    <li>Mot de passe (crypté)</li>
                    <li>Decks créés et leurs cartes</li>
                </ul>
                <p>
                    Pour exercer vos droits, vous pouvez nous contacter à l'adresse : 
                    <a href="mailto:contact@deckforge.local">contact@deckforge.local</a>
                </p>
                
                <h2>6. Cookies</h2>
                <p>
                    Le site utilise des cookies de session pour assurer le fonctionnement 
                    de l'authentification. Ces cookies sont nécessaires au bon fonctionnement 
                    du site et ne peuvent pas être désactivés.
                </p>
                
                <h2>7. Responsabilité</h2>
                <p>
                    DeckForge s'efforce d'assurer au mieux l'exactitude et la mise à jour 
                    des informations diffusées sur ce site. Toutefois, DeckForge ne peut 
                    garantir l'exactitude, la précision ou l'exhaustivité des informations 
                    mises à disposition sur ce site.
                </p>
                <p>
                    DeckForge décline toute responsabilité :
                </p>
                <ul>
                    <li>Pour toute imprécision, inexactitude ou omission portant sur des 
                        informations disponibles sur le site</li>
                    <li>Pour tous dommages résultant d'une intrusion frauduleuse d'un tiers 
                        ayant entraîné une modification des informations mises à disposition sur le site</li>
                    <li>Pour tous dommages directs ou indirects, quelles qu'en soient les causes, 
                        origines, natures ou conséquences, provoqués à raison de l'accès de quiconque au site</li>
                </ul>
                
                <h2>8. Liens hypertextes</h2>
                <p>
                    Le site peut contenir des liens hypertextes vers d'autres sites. 
                    DeckForge n'exerce aucun contrôle sur ces sites et décline toute 
                    responsabilité quant à leur contenu.
                </p>
                
                <h2>9. Droit applicable</h2>
                <p>
                    Le présent site et les présentes mentions légales sont soumis au droit français.
                </p>
                
                <h2>10. Contact</h2>
                <p>
                    Pour toute question ou demande d'information concernant le site, 
                    ou pour signaler tout contenu ou activité illicite, l'utilisateur peut 
                    contacter l'éditeur à l'adresse suivante : 
                    <a href="mailto:contact@deckforge.local">contact@deckforge.local</a>
                </p>
                
                <p class="legal-footer">
                    <em>Dernière mise à jour : <?php echo date('d/m/Y'); ?></em>
                </p>
            </div>
            
            <div class="legal-back">
                <a href="javascript:history.back()" class="btn btn-secondary">← Retour</a>
            </div>
        </section>
    </div>
</div>

<?php
// Inclusion du footer
require_once __DIR__ . '/includes/footer.php';
?>