<?php
require_once 'includes/functions.php';
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
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-outline-primary" id="mode-flashcards">Flashcards</button>
                                <button type="button" class="btn btn-outline-primary" id="mode-quiz">Quiz</button>
                                <button type="button" class="btn btn-outline-primary" id="mode-matching">Matching</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Flashcards Mode -->
                    <div id="flashcards-area" class="playground-mode">
                        <div class="d-flex justify-content-center align-items-center flex-column">
                            <div class="flashcard mb-3">
                                <div class="flashcard-inner">
                                    <div class="flashcard-front">
                                        <h2 id="flashcard-front-text">Click Start</h2>
                                    </div>
                                    <div class="flashcard-back">
                                        <h3 id="flashcard-back-text"></h3>
                                        <p class="context-text" id="flashcard-context"></p>
                                        <div class="synonyms-antonyms">
                                            <small id="flashcard-synonyms"></small>
                                            <br>
                                            <small id="flashcard-antonyms"></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="controls">
                                <button class="btn btn-primary" id="start-flashcards">Start</button>
                                <button class="btn btn-secondary" id="flip-flashcard">Flip</button>
                                <button class="btn btn-success" id="next-flashcard" disabled>Next</button>
                            </div>
                            <div class="progress mt-3 w-100">
                                <div class="progress-bar" id="flashcard-progress" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quiz Mode -->
                    <div id="quiz-area" class="playground-mode" style="display: none;">
                        <div class="quiz-container">
                            <div class="quiz-question mb-3">
                                <h4 id="quiz-question-text">Click Start to begin the quiz</h4>
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
                    <div id="matching-area" class="playground-mode" style="display: none;">
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

<!-- Pass vocabulary data to JavaScript -->
<script>
    const vocabularyData = <?php echo json_encode(getVocabulary()); ?>;
</script>

<?php include 'includes/footer.php'; ?>