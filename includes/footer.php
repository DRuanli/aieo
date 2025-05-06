<footer class="mt-5 py-4 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5 class="mb-3">IELTS Vocabulary Builder</h5>
                    <p class="text-muted mb-0">Enhance your English-Vietnamese vocabulary with spaced repetition to boost your IELTS score.</p>
                    <p class="text-muted mb-0 mt-2">&copy; <?php echo date('Y'); ?> IELTS Vocabulary App</p>
                </div>
                <div class="col-md-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-decoration-none"><i class="fas fa-book me-2"></i>Vocabulary List</a></li>
                        <li class="mb-2"><a href="playground.php" class="text-decoration-none"><i class="fas fa-graduation-cap me-2"></i>Study Tools</a></li>
                        <li class="mb-2"><a href="add.php" class="text-decoration-none"><i class="fas fa-plus me-2"></i>Add New Word</a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="mb-2"><a href="stats.php" class="text-decoration-none"><i class="fas fa-chart-bar me-2"></i>Statistics</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="mb-3">Study Streak</h5>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="fas fa-fire text-danger fs-3"></i>
                            </div>
                            <div>
                                <div class="fw-bold"><?php echo getUserById($_SESSION['user_id'])['streak']['current']; ?> day streak</div>
                                <div class="small text-muted">Keep it going!</div>
                            </div>
                        </div>
                        <div class="small">
                            <div class="mb-2">Best streak: <?php echo getUserById($_SESSION['user_id'])['streak']['max']; ?> days</div>
                            <div>Last study: <?php echo date('F j, Y', strtotime(getUserById($_SESSION['user_id'])['streak']['last_login'])); ?></div>
                        </div>
                    <?php else: ?>
                        <p><a href="login.php" class="btn btn-outline-primary btn-sm"><i class="fas fa-sign-in-alt me-2"></i>Login to track your progress</a></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- IELTS Resources Links -->
            <div class="row mt-4 pt-3 border-top">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="small text-muted">
                            Designed for IELTS preparation
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#resourcesOffcanvas" aria-controls="resourcesOffcanvas">
                                <i class="fas fa-book-reader me-1"></i> IELTS Resources
                            </button>
                        </div>
                    </div>
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