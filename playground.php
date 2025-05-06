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
$ielts_band = isset($_GET['ielts_band']) ? $_GET['ielts_band'] : '';
$use_srs = isset($_GET['use_srs']) ? (bool)$_GET['use_srs'] : false;
$study_type = isset($_GET['study_type']) ? $_GET['study_type'] : 'standard';

// Get categories for filter
$categories = getCategories();

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3>IELTS Vocabulary Practice</h3>
                </div>
                <div class="card-body">
                    <!-- Stats Summary for logged in users -->
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
                                                    <h5>Words Mastered</h5>
                                                    <h3><?php echo $stats['words_learned'] ?? 0; ?></h3>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5>Accuracy</h5>
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
                                <!-- Study Mode Selection -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Study Mode</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <input type="radio" class="btn-check" name="mode" id="mode-flashcards" value="flashcards" <?php echo $mode === 'flashcards' ? 'checked' : ''; ?>>
                                        <label class="btn btn-outline-primary" for="mode-flashcards">
                                            <i class="fas fa-clone"></i> Flashcards
                                        </label>
                                        
                                        <input type="radio" class="btn-check" name="mode" id="mode-quiz" value="quiz" <?php echo $mode === 'quiz' ? 'checked' : ''; ?>>
                                        <label class="btn btn-outline-primary" for="mode-quiz">
                                            <i class="fas fa-question-circle"></i> Quiz
                                        </label>
                                        
                                        <input type="radio" class="btn-check" name="mode" id="mode-matching" value="matching" <?php echo $mode === 'matching' ? 'checked' : ''; ?>>
                                        <label class="btn btn-outline-primary" for="mode-matching">
                                            <i class="fas fa-project-diagram"></i> Matching
                                        </label>
                                        
                                        <input type="radio" class="btn-check" name="mode" id="mode-collocations" value="collocations" <?php echo $mode === 'collocations' ? 'checked' : ''; ?>>
                                        <label class="btn btn-outline-primary" for="mode-collocations">
                                            <i class="fas fa-link"></i> Collocations
                                        </label>
                                        
                                        <input type="radio" class="btn-check" name="mode" id="mode-writing" value="writing" <?php echo $mode === 'writing' ? 'checked' : ''; ?>>
                                        <label class="btn btn-outline-primary" for="mode-writing">
                                            <i class="fas fa-pen"></i> Writing Practice
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-12">
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <h5>Filter Options</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label class="form-label">IELTS Topic</label>
                                                    <select class="form-select" name="category">
                                                        <option value="">All Topics</option>
                                                        <?php foreach ($categories as $cat): ?>
                                                            <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo $category === $cat['name'] ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($cat['name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                
                                                <div class="col-md-3">
                                                    <label class="form-label">IELTS Band Level</label>
                                                    <select class="form-select" name="ielts_band">
                                                        <option value="">All Bands</option>
                                                        <option value="5" <?php echo $ielts_band === '5' ? 'selected' : ''; ?>>Band 5</option>
                                                        <option value="6" <?php echo $ielts_band === '6' ? 'selected' : ''; ?>>Band 6</option>
                                                        <option value="7" <?php echo $ielts_band === '7' ? 'selected' : ''; ?>>Band 7</option>
                                                        <option value="8" <?php echo $ielts_band === '8' ? 'selected' : ''; ?>>Band 8+</option>
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
                                                    <label class="form-label">Study Type</label>
                                                    <select class="form-select" name="study_type">
                                                        <option value="standard" <?php echo $study_type === 'standard' ? 'selected' : ''; ?>>Standard Practice</option>
                                                        <option value="academic" <?php echo $study_type === 'academic' ? 'selected' : ''; ?>>Academic Word List</option>
                                                        <option value="collocations" <?php echo $study_type === 'collocations' ? 'selected' : ''; ?>>Common Collocations</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="form-check form-switch mt-3">
                                                <input class="form-check-input" type="checkbox" id="use_srs" name="use_srs" value="1" <?php echo $use_srs ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="use_srs">Use Spaced Repetition (SRS) for optimal learning</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-12 text-center">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-play"></i> Start Studying
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
                    if (!empty($ielts_band)) {
                        $filters['ielts_band'] = $ielts_band;
                    }
                    if (!empty($study_type) && $study_type !== 'standard') {
                        $filters['study_type'] = $study_type;
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
                                        <div class="mt-3 text-center">
                                            <span id="flashcard-band-level" class="badge bg-primary"></span>
                                        </div>
                                        <div class="mt-2">
                                            <audio id="flashcard-front-audio" controls style="display: none;"></audio>
                                        </div>
                                    </div>
                                    <div class="flashcard-back">
                                        <h3 id="flashcard-back-text"></h3>
                                        <p class="context-text" id="flashcard-context"></p>
                                        <div class="synonyms-antonyms">
                                            <small id="flashcard-collocations" class="d-block"></small>
                                            <small id="flashcard-synonyms" class="d-block"></small>
                                            <small id="flashcard-antonyms" class="d-block"></small>
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
                                    <span id="quiz-band-level" class="badge bg-primary"></span>
                                </div>
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
                    
                    <!-- Collocations Mode (New) -->
                    <div id="collocations-area" class="playground-mode" <?php echo $mode !== 'collocations' ? 'style="display: none;"' : ''; ?>>
                        <div class="collocations-container">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 id="collocation-word">Click Start to practice collocations</h5>
                                    <span id="collocation-band-level" class="badge bg-primary"></span>
                                </div>
                                <div class="card-body">
                                    <div id="collocation-question" class="mb-3"></div>
                                    <div id="collocation-options" class="row mb-3">
                                        <!-- Collocation options will be added here -->
                                    </div>
                                    <div id="collocation-feedback" class="mb-3"></div>
                                    <div class="controls">
                                        <button class="btn btn-primary" id="start-collocations">Start</button>
                                        <button class="btn btn-success" id="next-collocation" disabled>Next Collocation</button>
                                    </div>
                                    <div class="progress mt-3">
                                        <div class="progress-bar" id="collocation-progress" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Writing Practice Mode (New) -->
                    <div id="writing-area" class="playground-mode" <?php echo $mode !== 'writing' ? 'style="display: none;"' : ''; ?>>
                        <div class="writing-container">
                            <div class="card">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h5>IELTS Writing Practice</h5>
                                    <span id="writing-band-level" class="badge bg-primary"></span>
                                </div>
                                <div class="card-body">
                                    <div id="writing-instructions" class="alert alert-info mb-3">
                                        This exercise helps you practice using target vocabulary in IELTS-style writing. 
                                        Click Start to begin.
                                    </div>
                                    
                                    <div id="writing-task" class="mb-3">
                                        <!-- Task details will be added here -->
                                    </div>
                                    
                                    <div id="writing-keywords" class="mb-3">
                                        <!-- Target vocabulary words will be shown here -->
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="writing-response" class="form-label">Your Response</label>
                                        <textarea class="form-control" id="writing-response" rows="6" placeholder="Type your response using the target vocabulary words..." disabled></textarea>
                                    </div>
                                    
                                    <div id="writing-feedback" class="mb-3"></div>
                                    
                                    <div class="controls">
                                        <button class="btn btn-primary" id="start-writing">Start</button>
                                        <button class="btn btn-success" id="check-writing" disabled>Check Response</button>
                                        <button class="btn btn-primary" id="next-writing-task" disabled>Next Task</button>
                                    </div>
                                </div>
                            </div>
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
    
    // IELTS Writing Practice Tasks
    const writingTasks = [
        {
            type: "Task 1",
            description: "The graph below shows the population of major cities over time. Summarize the information by selecting and reporting the main features, and make comparisons where relevant.",
            topic: "Urban Population"
        },
        {
            type: "Task 2",
            description: "Some people believe that studying online is more effective than traditional classroom learning. To what extent do you agree or disagree with this statement?",
            topic: "Education"
        },
        {
            type: "Task 1",
            description: "The chart below shows the percentage of households with access to the internet in several countries between 2000 and 2020. Summarize the information by selecting and reporting the main features, and make comparisons where relevant.",
            topic: "Technology"
        },
        {
            type: "Task 2",
            description: "In many countries, the gap between the rich and poor is increasing. What problems might this cause and what solutions can you suggest?",
            topic: "Society"
        },
        {
            type: "Task 2",
            description: "Some people think that environmental problems are too big for individuals to solve, while others believe that these problems cannot be solved without individual action. Discuss both views and give your opinion.",
            topic: "Environment"
        }
    ];
</script>

<!-- Additional JavaScript for new study modes -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize collocations mode if active
    if (document.getElementById('collocations-area').style.display !== 'none') {
        initCollocations();
    }
    
    // Initialize writing practice mode if active
    if (document.getElementById('writing-area').style.display !== 'none') {
        initWritingPractice();
    }
});

// Collocations Mode
function initCollocations() {
    let currentIndex = 0;
    let shuffledVocabulary = [];
    let currentWord = null;
    let currentCollocation = null;
    let score = 0;
    
    const startBtn = document.getElementById('start-collocations');
    const nextBtn = document.getElementById('next-collocation');
    const wordElement = document.getElementById('collocation-word');
    const questionElement = document.getElementById('collocation-question');
    const optionsElement = document.getElementById('collocation-options');
    const feedbackElement = document.getElementById('collocation-feedback');
    const bandLevelElement = document.getElementById('collocation-band-level');
    const progressBar = document.getElementById('collocation-progress');
    
    const useSRS = document.getElementById('srs-data') && 
                   document.getElementById('srs-data').getAttribute('data-use-srs') === '1';
    
    startBtn.addEventListener('click', function() {
        // Filter vocabulary items that have collocations
        const vocabWithCollocations = vocabularyData.filter(item => 
            item.collocations && item.collocations.length > 0
        );
        
        if (vocabWithCollocations.length === 0) {
            feedbackElement.innerHTML = `
                <div class="alert alert-warning">
                    No vocabulary items with collocations found. Please add collocations to your vocabulary or adjust your filters.
                </div>
            `;
            return;
        }
        
        shuffledVocabulary = [...vocabWithCollocations].sort(() => Math.random() - 0.5);
        currentIndex = 0;
        score = 0;
        updateCollocationQuestion();
        this.disabled = true;
        nextBtn.disabled = true;
    });
    
    nextBtn.addEventListener('click', function() {
        currentIndex++;
        if (currentIndex >= shuffledVocabulary.length) {
            // All collocations completed
            wordElement.textContent = 'Collocations Practice Complete!';
            questionElement.textContent = '';
            optionsElement.innerHTML = '';
            bandLevelElement.textContent = '';
            
            feedbackElement.innerHTML = `
                <div class="alert alert-success">
                    <h5>Practice Complete!</h5>
                    <p>Your score: ${score} out of ${shuffledVocabulary.length}</p>
                </div>
            `;
            
            this.disabled = true;
            startBtn.disabled = false;
            return;
        }
        
        updateCollocationQuestion();
        this.disabled = true;
        feedbackElement.innerHTML = '';
    });
    
    function updateCollocationQuestion() {
        currentWord = shuffledVocabulary[currentIndex];
        const collocations = currentWord.collocations;
        
        // Randomly select one collocation to practice
        const randomIndex = Math.floor(Math.random() * collocations.length);
        currentCollocation = collocations[randomIndex];
        
        // Create a fill-in-the-blank question
        let parts = currentCollocation.split(' ');
        let wordIndex = parts.findIndex(part => 
            part.toLowerCase().includes(currentWord.english.toLowerCase())
        );
        
        if (wordIndex === -1) {
            // If the word isn't found in the collocation, just use the first position
            wordIndex = 0;
        }
        
        const blankParts = [...parts];
        blankParts[wordIndex] = '_____';
        
        // Update the UI
        wordElement.textContent = currentWord.english;
        questionElement.textContent = `Complete the collocation: "${blankParts.join(' ')}"`;
        
        if (currentWord.ielts_band) {
            bandLevelElement.textContent = `Band ${currentWord.ielts_band}`;
            bandLevelElement.style.display = 'inline-block';
        } else {
            bandLevelElement.style.display = 'none';
        }
        
        // Generate options
        const correctOption = parts[wordIndex];
        let options = [correctOption];
        
        // Add 3 distractors from vocabulary
        while (options.length < 4) {
            const randomWord = vocabularyData[Math.floor(Math.random() * vocabularyData.length)];
            if (randomWord.english !== currentWord.english && !options.includes(randomWord.english)) {
                options.push(randomWord.english);
            }
        }
        
        // Shuffle options
        options = options.sort(() => Math.random() - 0.5);
        
        // Create option buttons
        optionsElement.innerHTML = '';
        options.forEach(option => {
            const col = document.createElement('div');
            col.className = 'col-md-6 mb-2';
            
            const button = document.createElement('button');
            button.className = 'btn btn-outline-primary w-100';
            button.textContent = option;
            button.addEventListener('click', function() {
                // Disable all option buttons
                const buttons = optionsElement.querySelectorAll('button');
                buttons.forEach(btn => btn.disabled = true);
                
                // Check if answer is correct
                const isCorrect = option === correctOption;
                
                // Highlight correct and incorrect options
                buttons.forEach(btn => {
                    if (btn.textContent === correctOption) {
                        btn.classList.remove('btn-outline-primary');
                        btn.classList.add('btn-success');
                    } else if (btn === this && !isCorrect) {
                        btn.classList.remove('btn-outline-primary');
                        btn.classList.add('btn-danger');
                    }
                });
                
                // Update feedback
                if (isCorrect) {
                    feedbackElement.innerHTML = `
                        <div class="alert alert-success">
                            <p><strong>Correct!</strong> "${currentCollocation}" is a common collocation.</p>
                        </div>
                    `;
                    score++;
                    
                    // Record correct result if using SRS
                    if (useSRS) {
                        recordVocabularyResult(currentWord.id, 'correct', 'collocations');
                    }
                } else {
                    feedbackElement.innerHTML = `
                        <div class="alert alert-danger">
                            <p><strong>Incorrect.</strong> The correct collocation is "${currentCollocation}".</p>
                        </div>
                    `;
                    
                    // Record incorrect result if using SRS
                    if (useSRS) {
                        recordVocabularyResult(currentWord.id, 'incorrect', 'collocations');
                    }
                }
                
                // Enable next button
                nextBtn.disabled = false;
                
                // Update progress
                const progress = ((currentIndex + 1) / shuffledVocabulary.length) * 100;
                progressBar.style.width = `${progress}%`;
                progressBar.setAttribute('aria-valuenow', progress);
            });
            
            col.appendChild(button);
            optionsElement.appendChild(col);
        });
        
        // Update progress
        const progress = ((currentIndex + 1) / shuffledVocabulary.length) * 100;
        progressBar.style.width = `${progress}%`;
        progressBar.setAttribute('aria-valuenow', progress);
    }
}

// Writing Practice Mode
function initWritingPractice() {
    let currentTaskIndex = 0;
    let selectedWords = [];
    let currentTask = null;
    
    const startBtn = document.getElementById('start-writing');
    const checkBtn = document.getElementById('check-writing');
    const nextBtn = document.getElementById('next-writing-task');
    const instructionsElement = document.getElementById('writing-instructions');
    const taskElement = document.getElementById('writing-task');
    const keywordsElement = document.getElementById('writing-keywords');
    const responseTextarea = document.getElementById('writing-response');
    const feedbackElement = document.getElementById('writing-feedback');
    const bandLevelElement = document.getElementById('writing-band-level');
    
    startBtn.addEventListener('click', function() {
        // Check if we have vocabulary and tasks
        if (vocabularyData.length === 0 || writingTasks.length === 0) {
            feedbackElement.innerHTML = `
                <div class="alert alert-warning">
                    No vocabulary items or writing tasks available. Please add vocabulary or adjust your filters.
                </div>
            `;
            return;
        }
        
        // Select a random task
        currentTaskIndex = Math.floor(Math.random() * writingTasks.length);
        currentTask = writingTasks[currentTaskIndex];
        
        // Filter vocabulary by topic if possible
        let topicVocabulary = vocabularyData.filter(item => 
            item.category && item.category.some(cat => 
                cat.toLowerCase().includes(currentTask.topic.toLowerCase())
            )
        );
        
        // If no topic-specific vocabulary, use all vocabulary
        if (topicVocabulary.length < 5) {
            topicVocabulary = vocabularyData;
        }
        
        // Shuffle and select 5 words
        selectedWords = [...topicVocabulary]
            .sort(() => Math.random() - 0.5)
            .slice(0, 5);
        
        // Update UI
        updateWritingTask();
        
        // Update button states
        this.disabled = true;
        checkBtn.disabled = false;
        responseTextarea.disabled = false;
        
        // Display band level (using the average of selected words)
        const avgBand = selectedWords.reduce((sum, word) => 
            sum + (word.ielts_band ? parseInt(word.ielts_band) : 6), 0
        ) / selectedWords.length;
        
        bandLevelElement.textContent = `Target: Band ${Math.round(avgBand)}`;
        bandLevelElement.style.display = 'inline-block';
    });
    
    checkBtn.addEventListener('click', function() {
        const response = responseTextarea.value.trim();
        
        if (response.length < 50) {
            feedbackElement.innerHTML = `
                <div class="alert alert-warning">
                    Please write a longer response using the target vocabulary words.
                </div>
            `;
            return;
        }
        
        // Check if each target word is used
        const usedWords = [];
        const missingWords = [];
        
        for (const word of selectedWords) {
            if (response.toLowerCase().includes(word.english.toLowerCase())) {
                usedWords.push(word.english);
            } else {
                missingWords.push(word.english);
            }
        }
        
        // Calculate score and provide feedback
        const score = usedWords.length;
        let feedback = '';
        
        if (score === selectedWords.length) {
            feedback = `
                <div class="alert alert-success">
                    <h5>Excellent!</h5>
                    <p>You used all ${score} target vocabulary words correctly. Great job!</p>
                </div>
            `;
        } else if (score >= selectedWords.length / 2) {
            feedback = `
                <div class="alert alert-info">
                    <h5>Good effort!</h5>
                    <p>You used ${score} out of ${selectedWords.length} target vocabulary words.</p>
                    <p>Missing words: ${missingWords.join(', ')}</p>
                </div>
            `;
        } else {
            feedback = `
                <div class="alert alert-warning">
                    <h5>Keep practicing!</h5>
                    <p>You used ${score} out of ${selectedWords.length} target vocabulary words.</p>
                    <p>Missing words: ${missingWords.join(', ')}</p>
                    <p>Try to incorporate more of the target vocabulary in your response.</p>
                </div>
            `;
        }
        
        feedbackElement.innerHTML = feedback;
        
        // Enable next task button
        nextBtn.disabled = false;
        this.disabled = true;
    });
    
    nextBtn.addEventListener('click', function() {
        // Select a new random task
        let newTaskIndex;
        do {
            newTaskIndex = Math.floor(Math.random() * writingTasks.length);
        } while (newTaskIndex === currentTaskIndex && writingTasks.length > 1);
        
        currentTaskIndex = newTaskIndex;
        currentTask = writingTasks[currentTaskIndex];
        
        // Filter vocabulary by topic if possible
        let topicVocabulary = vocabularyData.filter(item => 
            item.category && item.category.some(cat => 
                cat.toLowerCase().includes(currentTask.topic.toLowerCase())
            )
        );
        
        // If no topic-specific vocabulary, use all vocabulary
        if (topicVocabulary.length < 5) {
            topicVocabulary = vocabularyData;
        }
        
        // Shuffle and select 5 words
        selectedWords = [...topicVocabulary]
            .sort(() => Math.random() - 0.5)
            .slice(0, 5);
        
        // Update UI
        updateWritingTask();
        
        // Reset form
        responseTextarea.value = '';
        feedbackElement.innerHTML = '';
        
        // Update button states
        this.disabled = true;
        checkBtn.disabled = false;
        
        // Display band level (using the average of selected words)
        const avgBand = selectedWords.reduce((sum, word) => 
            sum + (word.ielts_band ? parseInt(word.ielts_band) : 6), 0
        ) / selectedWords.length;
        
        bandLevelElement.textContent = `Target: Band ${Math.round(avgBand)}`;
    });
    
    function updateWritingTask() {
        // Update task description
        taskElement.innerHTML = `
            <div class="alert alert-primary">
                <h5>IELTS ${currentTask.type}</h5>
                <p>${currentTask.description}</p>
            </div>
        `;
        
        // Update instructions
        instructionsElement.innerHTML = `
            <p><strong>Instructions:</strong> Write a response to the prompt above using the following target vocabulary words.</p>
            <p>Aim to write at least 150 words for Task 1 or 250 words for Task 2.</p>
        `;
        
        // Update keywords
        let keywordsHtml = '<h5 class="mb-3">Target Vocabulary:</h5><div class="d-flex flex-wrap gap-2">';
        
        selectedWords.forEach(word => {
            let bandClass = '';
            if (word.ielts_band) {
                bandClass = word.ielts_band >= 8 ? 'bg-danger' : 
                           (word.ielts_band >= 7 ? 'bg-warning text-dark' : 
                           (word.ielts_band >= 6 ? 'bg-info text-dark' : 'bg-success'));
            } else {
                bandClass = 'bg-secondary';
            }
            
            keywordsHtml += `
                <div class="card" style="width: 18rem;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>${word.english}</strong>
                        <span class="badge ${bandClass}">Band ${word.ielts_band || 'N/A'}</span>
                    </div>
                    <div class="card-body">
                        <p class="card-text">${word.vietnamese}</p>
                        <p class="card-text small">
                            ${word.context ? `<em>"${word.context}"</em><br>` : ''}
                            ${word.collocations && word.collocations.length > 0 ? `<strong>Collocations:</strong> ${word.collocations.join(', ')}` : ''}
                        </p>
                    </div>
                </div>
            `;
        });
        
        keywordsHtml += '</div>';
        keywordsElement.innerHTML = keywordsHtml;
    }
}
</script>

<?php include 'includes/footer.php'; ?>