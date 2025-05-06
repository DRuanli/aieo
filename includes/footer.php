<footer class="mt-5 py-3 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Vocabulary App</p>
                    <p class="text-muted small mb-0">Enhance your English-Vietnamese vocabulary with spaced repetition</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="mb-2">
                        <a href="index.php" class="text-decoration-none me-3">Home</a>
                        <a href="playground.php" class="text-decoration-none me-3">Study</a>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="stats.php" class="text-decoration-none me-3">Statistics</a>
                            <a href="logout.php" class="text-decoration-none">Logout</a>
                        <?php else: ?>
                            <a href="login.php" class="text-decoration-none">Login</a>
                        <?php endif; ?>
                    </div>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="small text-muted">
                            Current streak: <?php echo getUserById($_SESSION['user_id'])['streak']['current']; ?> days
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>

    <!-- Hidden audio player for pronunciation -->
    <audio id="audio-player" style="display: none;"></audio>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="js/main.js"></script>
</body>
</html>