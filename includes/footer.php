</main>
    
    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <!-- Contact -->
                <div class="footer-section">
                    <a href="mailto:contact@deckforge.local" class="footer-link">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        Nous envoyer un mail
                    </a>
                </div>
                
                <!-- Mentions légales -->
                <div class="footer-section">
                    <a href="/legal-mentions.php" class="footer-link">Mentions légales</a>
                </div>
                
                <!-- Date de création -->
                <div class="footer-section">
                    <p class="footer-text">&copy; <?php echo date('Y'); ?> DeckForge</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Scripts JavaScript -->
    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/modal.js"></script>
    
    <?php
    // Script spécifique au deck builder
    if (basename($_SERVER['PHP_SELF']) === 'deck-builder.php'):
    ?>
    <script src="/assets/js/deck-builder.js"></script>
    <?php endif; ?>
    
</body>
</html>