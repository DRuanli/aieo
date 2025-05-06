document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we're on the playground page
    if (document.getElementById('flashcards-area')) {
        initPlayground();
    }
});

function initPlayground() {
    // Check if vocabulary data exists and is not empty
    if (typeof vocabularyData === 'undefined' || vocabularyData.length === 0) {
        document.querySelectorAll('.playground-mode').forEach(mode => {
            mode.innerHTML = `
                <div class="alert alert-warning">
                    <p>No vocabulary items found. Please <a href="add.php">add some vocabulary</a> first.</p>
                </div>
            `;
        });
        return;
    }

    // Mode switching
    const modes = ['flashcards', 'quiz', 'matching'];
    modes.forEach(mode => {
        document.getElementById(`mode-${mode}`).addEventListener('click', function() {
            // Toggle active state on buttons
            modes.forEach(m => {
                document.getElementById(`mode-${m}`).classList.remove('active', 'btn-primary');
                document.getElementById(`mode-${m}`).classList.add('btn-outline-primary');
                document.getElementById(`${m}-area`).style.display = 'none';
            });
            this.classList.remove('btn-outline-primary');
            this.classList.add('active', 'btn-primary');
            document.getElementById(`${mode}-area`).style.display = 'block';
        });
    });

    // Set flashcards as default active mode
    document.getElementById('mode-flashcards').click();

    // Initialize each mode
    initFlashcards();
    initQuiz();
    initMatching();
}

// Flashcards Mode
function initFlashcards() {
    let currentIndex = 0;
    let shuffledVocabulary = [];
    const flashcard = document.querySelector('.flashcard');
    const startBtn = document.getElementById('start-flashcards');
    const flipBtn = document.getElementById('flip-flashcard');
    const nextBtn = document.getElementById('next-flashcard');
    const progressBar = document.getElementById('flashcard-progress');

    startBtn.addEventListener('click', function() {
        shuffledVocabulary = [...vocabularyData].sort(() => Math.random() - 0.5);
        currentIndex = 0;
        updateFlashcard();
        this.disabled = true;
        flipBtn.disabled = false;
        nextBtn.disabled = false;
    });

    flipBtn.addEventListener('click', function() {
        flashcard.classList.toggle('flipped');
    });

    nextBtn.addEventListener('click', function() {
        flashcard.classList.remove('flipped');
        currentIndex++;
        if (currentIndex >= shuffledVocabulary.length) {
            currentIndex = 0;
        }
        updateFlashcard();
    });

    function updateFlashcard() {
        const item = shuffledVocabulary[currentIndex];
        document.getElementById('flashcard-front-text').textContent = item.english;
        document.getElementById('flashcard-back-text').textContent = item.vietnamese;
        document.getElementById('flashcard-context').textContent = item.context || '';
        document.getElementById('flashcard-synonyms').textContent = item.synonyms.length > 0 ? 
            'Synonyms: ' + item.synonyms.join(', ') : '';
        document.getElementById('flashcard-antonyms').textContent = item.antonyms.length > 0 ? 
            'Antonyms: ' + item.antonyms.join(', ') : '';
        
        // Update progress
        const progress = ((currentIndex + 1) / shuffledVocabulary.length) * 100;
        progressBar.style.width = `${progress}%`;
        progressBar.setAttribute('aria-valuenow', progress);
    }
}

// Quiz Mode
function initQuiz() {
    let currentIndex = 0;
    let shuffledVocabulary = [];
    let quizQuestions = [];
    let score = 0;
    const startBtn = document.getElementById('start-quiz');
    const nextBtn = document.getElementById('next-question');
    const questionText = document.getElementById('quiz-question-text');
    const optionsContainer = document.getElementById('quiz-options');
    const feedbackContainer = document.getElementById('quiz-feedback');
    const progressBar = document.getElementById('quiz-progress');

    startBtn.addEventListener('click', function() {
        shuffledVocabulary = [...vocabularyData].sort(() => Math.random() - 0.5);
        generateQuizQuestions();
        currentIndex = 0;
        score = 0;
        updateQuizQuestion();
        this.disabled = true;
        nextBtn.disabled = true;
        feedbackContainer.innerHTML = '';
    });

    nextBtn.addEventListener('click', function() {
        currentIndex++;
        if (currentIndex >= quizQuestions.length) {
            // Quiz completed
            questionText.textContent = `Quiz Completed!`;
            optionsContainer.innerHTML = '';
            feedbackContainer.innerHTML = `
                <div class="alert alert-success">
                    <p>Your score: ${score} out of ${quizQuestions.length}</p>
                </div>
            `;
            this.disabled = true;
            startBtn.disabled = false;
            return;
        }
        updateQuizQuestion();
        this.disabled = true;
        feedbackContainer.innerHTML = '';
    });

    function generateQuizQuestions() {
        quizQuestions = [];
        // Create a mix of English to Vietnamese and Vietnamese to English questions
        shuffledVocabulary.forEach((item, index) => {
            if (index % 2 === 0) {
                // English to Vietnamese
                quizQuestions.push({
                    question: `What is the Vietnamese meaning of "${item.english}"?`,
                    correctAnswer: item.vietnamese,
                    options: generateOptions(item.vietnamese, 'vietnamese')
                });
            } else {
                // Vietnamese to English
                quizQuestions.push({
                    question: `What is the English word for "${item.vietnamese}"?`,
                    correctAnswer: item.english,
                    options: generateOptions(item.english, 'english')
                });
            }
        });
        
        // Shuffle quiz questions
        quizQuestions = quizQuestions.sort(() => Math.random() - 0.5);
    }

    function generateOptions(correctAnswer, field) {
        let options = [correctAnswer];
        
        // Add 3 more random options
        while (options.length < 4) {
            const randomItem = vocabularyData[Math.floor(Math.random() * vocabularyData.length)];
            const randomOption = randomItem[field];
            
            if (!options.includes(randomOption)) {
                options.push(randomOption);
            }
        }
        
        // Shuffle options
        return options.sort(() => Math.random() - 0.5);
    }

    function updateQuizQuestion() {
        const currentQuestion = quizQuestions[currentIndex];
        questionText.textContent = currentQuestion.question;
        
        optionsContainer.innerHTML = '';
        currentQuestion.options.forEach((option, index) => {
            const optionElement = document.createElement('div');
            optionElement.className = 'quiz-option';
            optionElement.textContent = option;
            optionElement.dataset.value = option;
            optionElement.addEventListener('click', function() {
                // Remove selected class from all options
                document.querySelectorAll('.quiz-option').forEach(el => {
                    el.classList.remove('selected');
                });
                
                // Mark this option as selected
                this.classList.add('selected');
                
                // Check answer
                const selectedAnswer = this.dataset.value;
                const isCorrect = selectedAnswer === currentQuestion.correctAnswer;
                
                // Apply styling based on correctness
                document.querySelectorAll('.quiz-option').forEach(el => {
                    if (el.dataset.value === currentQuestion.correctAnswer) {
                        el.classList.add('correct');
                    } else if (el.dataset.value === selectedAnswer && !isCorrect) {
                        el.classList.add('incorrect');
                    }
                });
                
                // Update feedback
                if (isCorrect) {
                    feedbackContainer.innerHTML = '<div class="alert alert-success">Correct!</div>';
                    score++;
                } else {
                    feedbackContainer.innerHTML = `
                        <div class="alert alert-danger">
                            Incorrect. The correct answer is: "${currentQuestion.correctAnswer}"
                        </div>
                    `;
                }
                
                // Enable next button
                nextBtn.disabled = false;
            });
            
            optionsContainer.appendChild(optionElement);
        });
        
        // Update progress
        const progress = ((currentIndex + 1) / quizQuestions.length) * 100;
        progressBar.style.width = `${progress}%`;
        progressBar.setAttribute('aria-valuenow', progress);
    }
}

// Matching Mode
function initMatching() {
    let leftItems = [];
    let rightItems = [];
    let selectedLeft = null;
    let selectedRight = null;
    let matches = {};
    const startBtn = document.getElementById('start-matching');
    const checkBtn = document.getElementById('check-matching');
    const leftContainer = document.getElementById('matching-left');
    const rightContainer = document.getElementById('matching-right');
    const feedbackContainer = document.getElementById('matching-feedback');

    startBtn.addEventListener('click', function() {
        // Shuffle and pick 5 items (or less if vocabularyData has fewer items)
        const count = Math.min(5, vocabularyData.length);
        const shuffled = [...vocabularyData].sort(() => Math.random() - 0.5).slice(0, count);
        
        leftItems = shuffled.map(item => ({ id: Math.random().toString(36).substr(2, 9), text: item.english }));
        rightItems = shuffled.map(item => ({ id: Math.random().toString(36).substr(2, 9), text: item.vietnamese }));
        
        // Shuffle right items to avoid direct 1-to-1 matching
        rightItems = rightItems.sort(() => Math.random() - 0.5);
        
        // Reset matching state
        selectedLeft = null;
        selectedRight = null;
        matches = {};
        
        // Render items
        renderMatchingItems();
        
        // Update button states
        this.disabled = true;
        checkBtn.disabled = false;
    });

    checkBtn.addEventListener('click', function() {
        let correctMatches = 0;
        const totalMatches = leftItems.length;
        
        // Check each match
        for (const leftId in matches) {
            const rightId = matches[leftId];
            const leftIndex = leftItems.findIndex(item => item.id === leftId);
            const rightIndex = rightItems.findIndex(item => item.id === rightId);
            
            // If the indices match, the match is correct
            if (leftIndex === rightIndex) {
                correctMatches++;
            }
        }
        
        // Show feedback
        feedbackContainer.innerHTML = `
            <div class="alert alert-${correctMatches === totalMatches ? 'success' : 'info'}">
                <p>You got ${correctMatches} out of ${totalMatches} matches correct!</p>
                ${correctMatches !== totalMatches ? '<p>Try again or start a new game.</p>' : ''}
            </div>
        `;
        
        // Mark correct and incorrect matches
        for (const leftId in matches) {
            const rightId = matches[leftId];
            const leftIndex = leftItems.findIndex(item => item.id === leftId);
            const rightIndex = rightItems.findIndex(item => item.id === rightId);
            
            const leftElement = document.querySelector(`#matching-left [data-id="${leftId}"]`);
            const rightElement = document.querySelector(`#matching-right [data-id="${rightId}"]`);
            
            if (leftIndex === rightIndex) {
                leftElement.classList.add('matched');
                rightElement.classList.add('matched');
            } else {
                leftElement.classList.add('incorrect');
                rightElement.classList.add('incorrect');
            }
        }
    });

    function renderMatchingItems() {
        // Clear containers
        leftContainer.innerHTML = '';
        rightContainer.innerHTML = '';
        feedbackContainer.innerHTML = '';
        
        // Render left items
        leftItems.forEach(item => {
            const element = document.createElement('div');
            element.className = 'matching-item';
            element.textContent = item.text;
            element.dataset.id = item.id;
            
            element.addEventListener('click', function() {
                // Deselect previous item if any
                if (selectedLeft) {
                    document.querySelector(`#matching-left [data-id="${selectedLeft}"]`).classList.remove('selected');
                }
                
                // Select this item
                selectedLeft = item.id;
                this.classList.add('selected');
                
                // Try to create a match if both sides are selected
                if (selectedLeft && selectedRight) {
                    createMatch();
                }
            });
            
            leftContainer.appendChild(element);
        });
        
        // Render right items
        rightItems.forEach(item => {
            const element = document.createElement('div');
            element.className = 'matching-item';
            element.textContent = item.text;
            element.dataset.id = item.id;
            
            element.addEventListener('click', function() {
                // Deselect previous item if any
                if (selectedRight) {
                    document.querySelector(`#matching-right [data-id="${selectedRight}"]`).classList.remove('selected');
                }
                
                // Select this item
                selectedRight = item.id;
                this.classList.add('selected');
                
                // Try to create a match if both sides are selected
                if (selectedLeft && selectedRight) {
                    createMatch();
                }
            });
            
            rightContainer.appendChild(element);
        });
    }

    function createMatch() {
        // Create a match between selectedLeft and selectedRight
        matches[selectedLeft] = selectedRight;
        
        // Reset selection
        selectedLeft = null;
        selectedRight = null;
    }
}