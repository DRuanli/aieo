<?php
require_once 'includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get parameters
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'flashcards';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : '';
$use_srs = isset($_GET['use_srs']) ? (bool)$_GET['use_srs'] : false;

// Get categories for filter
$categories = getCategories();

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3>Vocabulary Playground</h3>
                </div>
                <div class="card-body">
                    <!-- Stats Summary -->
                    <?php if (getCurrentUserId()): ?>
                        <?php $stats = getUserStatistics(); ?>
                        <?php if (!empty($stats)): ?>
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="row text-center">
                                                <div class="col-md-3">
                                                    <h5>Daily Streak</h5>
                                                    <h3><?php echo getUserById(getCurrentUserId())['streak']['current']; ?> days</h3>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5>Words Studied</h5>
                                                    <h3><?php echo $stats['words_learned'] ?? 0; ?></h3>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5>Quiz Accuracy</h5>
                                                    <h3><?php echo number_format($stats['quiz_accuracy'] ?? 0, 1); ?>%</h3>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5>Due for Review</h5>
                                                    <h3><?php echo count(getDueWordsForReview(100)); ?></h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <form method="get" action="" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Study Mode</label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="mode" id="mode-flashcards" value="flashcards" <?php echo $mode === 'flashcards' ? 'checked' : ''; ?>>
                                        <label class="btn btn-outline-primary" for="mode-flashcards">Flashcards</label>
                                        
                                        <input type="radio" class="btn-check" name="mode" id="mode-quiz" value="quiz" <?php echo $mode === 'quiz' ? 'checked' : ''; ?>>
                                        <label class="btn btn-outline-primary" for="mode-quiz">Quiz</label>
                                        
                                        <input type="radio" class="btn-check" name="mode" id="mode-matching" value="matching" <?php echo $mode === 'matching' ? 'checked' : ''; ?>>
                                        <label class="btn btn-outline-primary" for="mode-matching">Matching</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Category</label>
                                    <select class="form-select" name="category">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo $category === $cat['name'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Difficulty</label>
                                    <select class="form-select" name="difficulty">
                                        <option value="">All Difficulties</option>
                                        <option value="easy" <?php echo $difficulty === 'easy' ? 'selected' : ''; ?>>Easy</option>
                                        <option value="medium" <?php echo $difficulty === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                        <option value="hard" <?php echo $difficulty === 'hard' ? 'selected' : ''; ?>>Hard</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Study Method</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="use_srs" name="use_srs" value="1" <?php echo $use_srs ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="use_srs">Use Spaced Repetition (SRS)</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-12 text-center">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-play"></i> Start Learning
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <?php
                    // Prepare filters
                    $filters = [];
                    if (!empty($category)) {
                        $filters['category'] = $category;
                    }
                    if (!empty($difficulty)) {
                        $filters['difficulty'] = $difficulty;
                    }
                    
                    // Get vocabulary based on filters and SRS setting
                    if ($use_srs && getCurrentUserId()) {
                        $vocabulary = getDueWordsForReview(20);
                    } else {
                        $vocabulary = getVocabulary($filters)['data'];
                    }
                    ?>
                    
                    <!-- Flashcards Mode -->
                    <div id="flashcards-area" class="playground-mode" <?php echo $mode !== 'flashcards' ? 'style="display: none;"' : ''; ?>>
                        <div class="d-flex justify-content-center align-items-center flex-column">
                            <div class="flashcard mb-3">
                                <div class="flashcard-inner">
                                    <div class="flashcard-front">
                                        <h2 id="flashcard-front-text">Click Start</h2>
                                        <div class="mt-2">
                                            <span id="flashcard-categories"></span>
                                        </div>
                                        <div class="mt-2">
                                            <audio id="flashcard-front-audio" controls style="display: none;"></audio>
                                        </div>
                                    </div>
                                    <div class="flashcard-back">
                                        <h3 id="flashcard-back-text"></h3>
                                        <p class="context-text" id="flashcard-context"></p>
                                        <div class="synonyms-antonyms">
                                            <small id="flashcard-synonyms"></small>
                                            <br>
                                            <small id="flashcard-antonyms"></small>
                                        </div>
                                        <div class="mt-2">
                                            <audio id="flashcard-back-audio" controls style="display: none;"></audio>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="controls">
                                <button class="btn btn-primary" id="start-flashcards">Start</button>
                                <button class="btn btn-secondary" id="flip-flashcard" disabled>Flip</button>
                                <?php if (getCurrentUserId()): ?>
                                    <button class="btn btn-success" id="correct-flashcard" disabled>I Know This</button>
                                    <button class="btn btn-danger" id="incorrect-flashcard" disabled>Still Learning</button>
                                <?php else: ?>
                                    <button class="btn btn-success" id="next-flashcard" disabled>Next</button>
                                <?php endif; ?>
                            </div>
                            <div class="progress mt-3 w-100">
                                <div class="progress-bar" id="flashcard-progress" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quiz Mode -->
                    <div id="quiz-area" class="playground-mode" <?php echo $mode !== 'quiz' ? 'style="display: none;"' : ''; ?>>
                        <div class="quiz-container">
                            <div class="quiz-question mb-3">
                                <h4 id="quiz-question-text">Click Start to begin the quiz</h4>
                                <div class="mt-2">
                                    <audio id="quiz-question-audio" controls style="display: none;"></audio>
                                </div>
                            </div>
                            <div class="quiz-options mb-3" id="quiz-options">
                                <!-- Options will be added here -->
                            </div>
                            <div class="quiz-feedback mb-3" id="quiz-feedback"></div>
                            <div class="controls">
                                <button class="btn btn-primary" id="start-quiz">Start</button>
                                <button class="btn btn-success" id="next-question" disabled>Next Question</button>
                            </div>
                            <div class="progress mt-3">
                                <div class="progress-bar" id="quiz-progress" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Matching Mode -->
                    <div id="matching-area" class="playground-mode" <?php echo $mode !== 'matching' ? 'style="display: none;"' : ''; ?>>
                        <div class="matching-container">
                            <div class="row mb-3">
                                <div class="col-md-5" id="matching-left">
                                    <!-- Left items will be added here -->
                                </div>
                                <div class="col-md-2 d-flex align-items-center justify-content-center">
                                    <div class="arrow-container">
                                        <i class="fas fa-arrows-alt-h"></i>
                                    </div>
                                </div>
                                <div class="col-md-5" id="matching-right">
                                    <!-- Right items will be added here -->
                                </div>
                            </div>
                            <div class="controls text-center">
                                <button class="btn btn-primary" id="start-matching">Start</button>
                                <button class="btn btn-success" id="check-matching" disabled>Check Matches</button>
                            </div>
                            <div class="matching-feedback mt-3" id="matching-feedback"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Session Storage for SRS -->
<div id="srs-data" data-use-srs="<?php echo $use_srs ? '1' : '0'; ?>"
     data-user-id="<?php echo getCurrentUserId() ?: '0'; ?>"></div>

<!-- Pass vocabulary data to JavaScript -->
<script>
    const vocabularyData = <?php echo json_encode($vocabulary); ?>;
</script>

<?php include 'includes/footer.php'; ?>