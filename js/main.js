/**
 * IELTS Vocabulary Builder - Enhanced JavaScript Functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize AJAX setup
    setupAjax();
    
    // Initialize audio players in vocabulary list
    initAudioPlayers();
    
    // Only initialize playground if we're on the playground page
    if (document.getElementById('flashcards-area')) {
        initPlayground();
    }
    
    // Initialize statistics dashboard if on the dashboard page
    if (document.getElementById('stats-dashboard')) {
        initStatsDashboard();
    }
    
    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltips.length > 0) {
        tooltips.forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });
    }
    
    // Add animation to progress bars
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        
        // Trigger reflow
        void bar.offsetWidth;
        
        bar.classList.add('with-animation');
        bar.style.setProperty('--target-width', width);
    });
});

/**
 * Set up AJAX with CSRF token
 */
function setupAjax() {
    // Add CSRF token to all AJAX requests if available
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        const token = csrfToken.getAttribute('content');
        
        // Add event listener for AJAX requests
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.getAttribute('data-ajax') === 'true') {
                e.preventDefault();
                
                const formData = new FormData(form);
                formData.append('csrf_token', token);
                
                fetch(form.action, {
                    method: form.method,
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else if (data.message) {
                        showMessage(data.message, data.type || 'info');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('An error occurred. Please try again.', 'danger');
                });
            }
        });
    }
}

/**
 * Display a message to the user
 */
function showMessage(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const container = document.querySelector('.container');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertDiv);
        bsAlert.close();
    }, 5000);
}

/**
 * Initialize audio players for pronunciation
 */
function initAudioPlayers() {
    const audioPlayers = document.querySelectorAll('.play-audio');
    if (audioPlayers.length > 0) {
        const audioElement = document.getElementById('audio-player') || document.createElement('audio');
        audioElement.id = 'audio-player';
        audioElement.style.display = 'none';
        document.body.appendChild(audioElement);
        
        audioPlayers.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const audioSrc = this.getAttribute('data-src');
                audioElement.src = audioSrc;
                audioElement.play();
            });
        });
    }
}

/**
 * Record vocabulary review results with SRS
 */
function recordVocabularyResult(wordId, result, mode) {
    // Check if user is logged in and SRS is enabled
    const srsData = document.getElementById('srs-data');
    if (!srsData || srsData.getAttribute('data-user-id') === '0') {
        return; // Not logged in, don't record
    }
    
    // Send AJAX request to record result
    fetch('api/record_result.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            word_id: wordId,
            result: result,
            mode: mode
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Result recorded successfully');
        } else {
            console.error('Failed to record result');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

/**
 * Initialize the study playground
 */
function initPlayground() {
    // Check if vocabulary data exists and is not empty
    if (typeof vocabularyData === 'undefined' || vocabularyData.length === 0) {
        document.querySelectorAll('.playground-mode').forEach(mode => {
            mode.innerHTML = `
                <div class="alert alert-warning">
                    <p>No vocabulary items found matching your criteria. Please <a href="add.php">add some vocabulary</a> first or adjust your filters.</p>
                </div>
            `;
        });
        return;
    }

    // Initialize each mode based on what's visible
    if (document.getElementById('flashcards-area').style.display !== 'none') {
        initFlashcards();
    }
    
    if (document.getElementById('quiz-area').style.display !== 'none') {
        initQuiz();
    }
    
    if (document.getElementById('matching-area').style.display !== 'none') {
        initMatching();
    }
    
    if (document.getElementById('collocations-area') && 
        document.getElementById('collocations-area').style.display !== 'none') {
        initCollocations();
    }
    
    if (document.getElementById('writing-area') && 
        document.getElementById('writing-area').style.display !== 'none') {
        initWritingPractice();
    }
}

// Flashcards Mode
function initFlashcards() {
    let currentIndex = 0;
    let shuffledVocabulary = [];
    let currentWord = null;
    const flashcard = document.querySelector('.flashcard');
    const startBtn = document.getElementById('start-flashcards');
    const flipBtn = document.getElementById('flip-flashcard');
    const progressBar = document.getElementById('flashcard-progress');
    const frontAudio = document.getElementById('flashcard-front-audio');
    const backAudio = document.getElementById('flashcard-back-audio');
    const bandLevelEl = document.getElementById('flashcard-band-level');
    
    // Check if we have SRS buttons or regular next button
    const correctBtn = document.getElementById('correct-flashcard');
    const incorrectBtn = document.getElementById('incorrect-flashcard');
    const nextBtn = document.getElementById('next-flashcard');
    
    const useSRS = document.getElementById('srs-data') && 
                   document.getElementById('srs-data').getAttribute('data-use-srs') === '1';

    startBtn.addEventListener('click', function() {
        shuffledVocabulary = [...vocabularyData].sort(() => Math.random() - 0.5);
        currentIndex = 0;
        updateFlashcard();
        this.disabled = true;
        flipBtn.disabled = false;
        
        if (correctBtn && incorrectBtn) {
            correctBtn.disabled = true;
            incorrectBtn.disabled = true;
        } else if (nextBtn) {
            nextBtn.disabled = true;
        }
    });

    flipBtn.addEventListener('click', function() {
        flashcard.classList.toggle('flipped');
        
        // Enable result buttons when card is flipped to back
        if (flashcard.classList.contains('flipped')) {
            if (correctBtn && incorrectBtn) {
                correctBtn.disabled = false;
                incorrectBtn.disabled = false;
            } else if (nextBtn) {
                nextBtn.disabled = false;
            }
        }
    });
    
    // SRS buttons
    if (correctBtn && incorrectBtn) {
        correctBtn.addEventListener('click', function() {
            // Record result if using SRS
            if (useSRS && currentWord) {
                recordVocabularyResult(currentWord.id, 'correct', 'flashcard');
            }
            
            nextCard();
        });
        
        incorrectBtn.addEventListener('click', function() {
            // Record result if using SRS
            if (useSRS && currentWord) {
                recordVocabularyResult(currentWord.id, 'incorrect', 'flashcard');
            }
            
            nextCard();
        });
    } else if (nextBtn) {
        nextBtn.addEventListener('click', nextCard);
    }
    
    function nextCard() {
        flashcard.classList.remove('flipped');
        currentIndex++;
        if (currentIndex >= shuffledVocabulary.length) {
            currentIndex = 0;
        }
        updateFlashcard();
        
        // Disable result buttons until card is flipped again
        if (correctBtn && incorrectBtn) {
            correctBtn.disabled = true;
            incorrectBtn.disabled = true;
        } else if (nextBtn) {
            nextBtn.disabled = true;
        }
    }

    function updateFlashcard() {
        currentWord = shuffledVocabulary[currentIndex];
        document.getElementById('flashcard-front-text').textContent = currentWord.english;
        document.getElementById('flashcard-back-text').textContent = currentWord.vietnamese;
        document.getElementById('flashcard-context').textContent = currentWord.context || '';
        
        // Add collocations if available
        const collocationsEl = document.getElementById('flashcard-collocations');
        if (collocationsEl) {
            collocationsEl.textContent = currentWord.collocations && currentWord.collocations.length > 0 ? 
                'Collocations: ' + currentWord.collocations.join(', ') : '';
        }
        
        document.getElementById('flashcard-synonyms').textContent = currentWord.synonyms && currentWord.synonyms.length > 0 ? 
            'Synonyms: ' + currentWord.synonyms.join(', ') : '';
        document.getElementById('flashcard-antonyms').textContent = currentWord.antonyms && currentWord.antonyms.length > 0 ? 
            'Antonyms: ' + currentWord.antonyms.join(', ') : '';
        
        // Display IELTS band level if available
        if (bandLevelEl) {
            if (currentWord.ielts_band) {
                bandLevelEl.textContent = 'Band ' + currentWord.ielts_band;
                bandLevelEl.style.display = 'inline-block';
                
                // Add appropriate color class
                bandLevelEl.className = 'badge';
                if (currentWord.ielts_band >= 8) {
                    bandLevelEl.classList.add('bg-danger');
                } else if (currentWord.ielts_band >= 7) {
                    bandLevelEl.classList.add('bg-warning', 'text-dark');
                } else if (currentWord.ielts_band >= 6) {
                    bandLevelEl.classList.add('bg-info', 'text-dark');
                } else {
                    bandLevelEl.classList.add('bg-success');
                }
                
            } else {
                bandLevelEl.style.display = 'none';
            }
        }
        
        // Display categories as badges if available
        const categoriesEl = document.getElementById('flashcard-categories');
        categoriesEl.innerHTML = '';
        if (currentWord.category && currentWord.category.length > 0) {
            currentWord.category.forEach(cat => {
                const badge = document.createElement('span');
                badge.className = 'badge bg-light text-dark border me-1 mb-1';
                badge.textContent = cat;
                categoriesEl.appendChild(badge);
            });
        }
        
        // Update audio if available
        if (currentWord.pronunciation) {
            if (currentWord.pronunciation.en) {
                frontAudio.src = 'data/audio/' + currentWord.pronunciation.en;
                frontAudio.style.display = 'block';
            } else {
                frontAudio.style.display = 'none';
            }
            
            if (currentWord.pronunciation.vi) {
                backAudio.src = 'data/audio/' + currentWord.pronunciation.vi;
                backAudio.style.display = 'block';
            } else {
                backAudio.style.display = 'none';
            }
        } else {
            frontAudio.style.display = 'none';
            backAudio.style.display = 'none';
        }
        
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
    let currentWordId = null;
    const startBtn = document.getElementById('start-quiz');
    const nextBtn = document.getElementById('next-question');
    const questionText = document.getElementById('quiz-question-text');
    const questionAudio = document.getElementById('quiz-question-audio');
    const optionsContainer = document.getElementById('quiz-options');
    const feedbackContainer = document.getElementById('quiz-feedback');
    const progressBar = document.getElementById('quiz-progress');
    const bandLevelEl = document.getElementById('quiz-band-level');
    
    const useSRS = document.getElementById('srs-data') && 
                   document.getElementById('srs-data').getAttribute('data-use-srs') === '1';

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
            if (bandLevelEl) bandLevelEl.style.display = 'none';
            optionsContainer.innerHTML = '';
            feedbackContainer.innerHTML = `
                <div class="alert alert-success">
                    <h5>Great job!</h5>
                    <p>Your score: ${score} out of ${quizQuestions.length}</p>
                    ${score === quizQuestions.length ? 
                        '<p>Perfect score! Your IELTS vocabulary is improving!</p>' : 
                        '<p>Keep practicing to improve your IELTS vocabulary skills.</p>'}
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
                    options: generateOptions(item.vietnamese, 'vietnamese'),
                    audio: item.pronunciation ? item.pronunciation.en : null,
                    wordId: item.id,
                    ielts_band: item.ielts_band
                });
            } else {
                // Vietnamese to English
                quizQuestions.push({
                    question: `What is the English word for "${item.vietnamese}"?`,
                    correctAnswer: item.english,
                    options: generateOptions(item.english, 'english'),
                    audio: item.pronunciation ? item.pronunciation.vi : null,
                    wordId: item.id,
                    ielts_band: item.ielts_band
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
        currentWordId = currentQuestion.wordId;
        
        // Display IELTS band level if available
        if (bandLevelEl) {
            if (currentQuestion.ielts_band) {
                bandLevelEl.textContent = 'Band ' + currentQuestion.ielts_band;
                bandLevelEl.style.display = 'inline-block';
                
                // Add appropriate color class
                bandLevelEl.className = 'badge';
                if (currentQuestion.ielts_band >= 8) {
                    bandLevelEl.classList.add('bg-danger');
                } else if (currentQuestion.ielts_band >= 7) {
                    bandLevelEl.classList.add('bg-warning', 'text-dark');
                } else if (currentQuestion.ielts_band >= 6) {
                    bandLevelEl.classList.add('bg-info', 'text-dark');
                } else {
                    bandLevelEl.classList.add('bg-success');
                }
                
            } else {
                bandLevelEl.style.display = 'none';
            }
        }
        
        // Update audio if available
        if (currentQuestion.audio) {
            questionAudio.src = 'data/audio/' + currentQuestion.audio;
            questionAudio.style.display = 'block';
        } else {
            questionAudio.style.display = 'none';
        }
        
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
                    feedbackContainer.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Correct!</div>';
                    score++;
                    
                    // Record result if using SRS
                    if (useSRS && currentWordId) {
                        recordVocabularyResult(currentWordId, 'correct', 'quiz');
                    }
                } else {
                    feedbackContainer.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle me-2"></i>Incorrect. The correct answer is: "${currentQuestion.correctAnswer}"
                        </div>
                    `;
                    
                    // Record result if using SRS
                    if (useSRS && currentWordId) {
                        recordVocabularyResult(currentWordId, 'incorrect', 'quiz');
                    }
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
    let wordIds = {}; // Store word IDs for SRS
    const startBtn = document.getElementById('start-matching');
    const checkBtn = document.getElementById('check-matching');
    const leftContainer = document.getElementById('matching-left');
    const rightContainer = document.getElementById('matching-right');
    const feedbackContainer = document.getElementById('matching-feedback');
    
    const useSRS = document.getElementById('srs-data') && 
                   document.getElementById('srs-data').getAttribute('data-use-srs') === '1';

    startBtn.addEventListener('click', function() {
        // Shuffle and pick 5 items (or less if vocabularyData has fewer items)
        const count = Math.min(5, vocabularyData.length);
        const shuffled = [...vocabularyData]
            .sort(() => Math.random() - 0.5)
            .slice(0, count);
        
        leftItems = shuffled.map(item => ({ 
            id: Math.random().toString(36).substr(2, 9), 
            text: item.english,
            wordId: item.id,
            audio: item.pronunciation ? item.pronunciation.en : null,
            ielts_band: item.ielts_band
        }));
        
        rightItems = shuffled.map(item => ({
            id: Math.random().toString(36).substr(2, 9),
            text: item.vietnamese,
            wordId: item.id,
            audio: item.pronunciation ? item.pronunciation.vi : null
        }));
        
        // Store word IDs for SRS
        shuffled.forEach((item, index) => {
            wordIds[index] = item.id;
        });
        
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
            const leftItem = leftItems.find(item => item.id === leftId);
            const rightItem = rightItems.find(item => item.id === rightId);
            
            // If the word IDs match, the match is correct
            if (leftItem.wordId === rightItem.wordId) {
                correctMatches++;
                
                // Record as correct in SRS if enabled
                if (useSRS) {
                    recordVocabularyResult(leftItem.wordId, 'correct', 'matching');
                }
            } else if (useSRS) {
                // Record as incorrect in SRS if enabled
                recordVocabularyResult(leftItem.wordId, 'incorrect', 'matching');
            }
        }
        
        // Show feedback
        feedbackContainer.innerHTML = `
            <div class="alert alert-${correctMatches === totalMatches ? 'success' : 'info'}">
                <h5>${correctMatches === totalMatches ? 'Excellent!' : 'Good effort!'}</h5>
                <p>You matched ${correctMatches} out of ${totalMatches} words correctly.</p>
                ${correctMatches !== totalMatches ? '<p>Review the incorrect matches and try again.</p>' : ''}
            </div>
        `;
        
        // Mark correct and incorrect matches
        for (const leftId in matches) {
            const rightId = matches[leftId];
            const leftItem = leftItems.find(item => item.id === leftId);
            const rightItem = rightItems.find(item => item.id === rightId);
            
            const leftElement = document.querySelector(`#matching-left [data-id="${leftId}"]`);
            const rightElement = document.querySelector(`#matching-right [data-id="${rightId}"]`);
            
            if (leftItem.wordId === rightItem.wordId) {
                leftElement.classList.add('matched');
                rightElement.classList.add('matched');
            } else {
                leftElement.classList.add('incorrect');
                rightElement.classList.add('incorrect');
            }
        }
        
        // Enable start button for a new round
        startBtn.disabled = false;
    });

    function renderMatchingItems() {
        // Clear containers
        leftContainer.innerHTML = '';
        rightContainer.innerHTML = '';
        feedbackContainer.innerHTML = '';
        
        // Render left items
        leftItems.forEach(item => {
            const element = document.createElement('div');
            element.className = 'matching-item d-flex justify-content-between align-items-center';
            element.dataset.id = item.id;
            
            const content = document.createElement('div');
            
            const textSpan = document.createElement('span');
            textSpan.textContent = item.text;
            content.appendChild(textSpan);
            
            // Add IELTS band badge if available
            if (item.ielts_band) {
                const badge = document.createElement('span');
                badge.className = 'badge ms-2';
                
                if (item.ielts_band >= 8) {
                    badge.classList.add('bg-danger');
                } else if (item.ielts_band >= 7) {
                    badge.classList.add('bg-warning', 'text-dark');
                } else if (item.ielts_band >= 6) {
                    badge.classList.add('bg-info', 'text-dark');
                } else {
                    badge.classList.add('bg-success');
                }
                
                badge.textContent = 'B' + item.ielts_band; // B7, B8, etc.
                content.appendChild(badge);
            }
            
            element.appendChild(content);
            
            // Add audio button if available
            if (item.audio) {
                const audioBtn = document.createElement('button');
                audioBtn.className = 'btn btn-sm btn-link text-primary';
                audioBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
                audioBtn.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent triggering the parent's click event
                    const audioPlayer = document.createElement('audio');
                    audioPlayer.src = 'data/audio/' + item.audio;
                    audioPlayer.play();
                });
                element.appendChild(audioBtn);
            }
            
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
            element.className = 'matching-item d-flex justify-content-between align-items-center';
            element.dataset.id = item.id;
            
            const textSpan = document.createElement('span');
            textSpan.textContent = item.text;
            textSpan.className = 'vietnamese'; // Add Vietnamese font support
            element.appendChild(textSpan);
            
            // Add audio button if available
            if (item.audio) {
                const audioBtn = document.createElement('button');
                audioBtn.className = 'btn btn-sm btn-link text-primary';
                audioBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
                audioBtn.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent triggering the parent's click event
                    const audioPlayer = document.createElement('audio');
                    audioPlayer.src = 'data/audio/' + item.audio;
                    audioPlayer.play();
                });
                element.appendChild(audioBtn);
            }
            
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
        
        // Add visual indicator for match
        const leftElement = document.querySelector(`#matching-left [data-id="${selectedLeft}"]`);
        const rightElement = document.querySelector(`#matching-right [data-id="${selectedRight}"]`);
        
        leftElement.classList.add('connected');
        rightElement.classList.add('connected');
        
        // Reset selection
        selectedLeft = null;
        selectedRight = null;
    }
}

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
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>No Collocations Available</h5>
                    <p>No vocabulary items with collocations found. Please add collocations to your vocabulary or adjust your filters.</p>
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
            bandLevelElement.style.display = 'none';
            
            feedbackElement.innerHTML = `
                <div class="alert alert-success">
                    <h5><i class="fas fa-trophy me-2"></i>Practice Complete!</h5>
                    <p>Your score: ${score} out of ${shuffledVocabulary.length}</p>
                    <p>Using collocations correctly will improve your IELTS Writing and Speaking scores!</p>
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
        
        // Display IELTS band level if available
        if (currentWord.ielts_band) {
            bandLevelElement.textContent = `Band ${currentWord.ielts_band}`;
            bandLevelElement.style.display = 'inline-block';
            
            // Add appropriate class
            bandLevelElement.className = 'badge';
            if (currentWord.ielts_band >= 8) {
                bandLevelElement.classList.add('bg-danger');
            } else if (currentWord.ielts_band >= 7) {
                bandLevelElement.classList.add('bg-warning', 'text-dark');
            } else if (currentWord.ielts_band >= 6) {
                bandLevelElement.classList.add('bg-info', 'text-dark');
            } else {
                bandLevelElement.classList.add('bg-success');
            }
            
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
                            <h5><i class="fas fa-check-circle me-2"></i>Correct!</h5>
                            <p>"${currentCollocation}" is a common collocation.</p>
                            ${currentWord.context ? `<p><em>Example: "${currentWord.context}"</em></p>` : ''}
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
                            <h5><i class="fas fa-times-circle me-2"></i>Incorrect</h5>
                            <p>The correct collocation is "${currentCollocation}".</p>
                            ${currentWord.context ? `<p><em>Example: "${currentWord.context}"</em></p>` : ''}
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
        if (vocabularyData.length === 0 || typeof writingTasks === 'undefined' || writingTasks.length === 0) {
            feedbackElement.innerHTML = `
                <div class="alert alert-warning">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>No Content Available</h5>
                    <p>No vocabulary items or writing tasks available. Please add vocabulary or adjust your filters.</p>
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
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Response Too Short</h5>
                    <p>Please write a longer response using the target vocabulary words.</p>
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
                    <h5><i class="fas fa-trophy me-2"></i>Excellent!</h5>
                    <p>You used all ${score} target vocabulary words correctly. Great job!</p>
                    <p>This level of vocabulary usage would help achieve a higher band score in IELTS Writing.</p>
                </div>
            `;
        } else if (score >= selectedWords.length / 2) {
            feedback = `
                <div class="alert alert-info">
                    <h5><i class="fas fa-thumbs-up me-2"></i>Good effort!</h5>
                    <p>You used ${score} out of ${selectedWords.length} target vocabulary words.</p>
                    <p>Missing words: ${missingWords.join(', ')}</p>
                    <p>Try to incorporate all target words naturally in your response.</p>
                </div>
            `;
        } else {
            feedback = `
                <div class="alert alert-warning">
                    <h5><i class="fas fa-info-circle me-2"></i>Keep practicing!</h5>
                    <p>You used ${score} out of ${selectedWords.length} target vocabulary words.</p>
                    <p>Missing words: ${missingWords.join(', ')}</p>
                    <p>Using a wide range of vocabulary is important for achieving higher band scores in IELTS.</p>
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
            <p><strong>Instructions:</strong> Write a response to the prompt above using all the following target vocabulary words.</p>
            <p>Aim to write at least 150 words for Task 1 or 250 words for Task 2.</p>
        `;
        
        // Update keywords
        let keywordsHtml = '<h5 class="mb-3">Target Vocabulary:</h5><div class="row">';
        
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
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
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
                </div>
            `;
        });
        
        keywordsHtml += '</div>';
        keywordsElement.innerHTML = keywordsHtml;
        
        // Enable textarea
        responseTextarea.disabled = false;
        responseTextarea.focus();
    }
}

function initStatsDashboard() {
    // Get stats data from the element
    const statsDataEl = document.getElementById('stats-data');
    if (!statsDataEl) return;
    
    const statsData = JSON.parse(statsDataEl.getAttribute('data-stats'));
    
    // Initialize charts
    initAccuracyChart(statsData);
    initActivityChart(statsData);
    initProgressChart(statsData);
    initBandChart(statsData);
}

function initAccuracyChart(statsData) {
    const ctx = document.getElementById('accuracy-chart');
    if (!ctx) return;
    
    const ctxObj = ctx.getContext('2d');
    
    // Prepare data
    const modes = [];
    const accuracyData = [];
    
    for (let mode in statsData.accuracy) {
        modes.push(mode.charAt(0).toUpperCase() + mode.slice(1));
        accuracyData.push(statsData.accuracy[mode]);
    }
    
    new Chart(ctxObj, {
        type: 'bar',
        data: {
            labels: modes,
            datasets: [{
                label: 'Accuracy %',
                data: accuracyData,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(255, 99, 132, 0.7)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Percentage'
                    }
                }
            },
            plugins: {
                title: {
                    display: false
                },
                legend: {
                    display: false
                }
            }
        }
    });
}

function initActivityChart(statsData) {
    const ctx = document.getElementById('activity-chart');
    if (!ctx) return;
    
    const ctxObj = ctx.getContext('2d');
    
    // Prepare data
    const dailyActivity = statsData.daily_activity || [];
    const sortedActivity = [...dailyActivity].sort((a, b) => new Date(a.date) - new Date(b.date));
    
    // Limit to last 14 days
    const recentActivity = sortedActivity.slice(-14);
    
    const labels = recentActivity.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
    
    const data = recentActivity.map(item => item.words_studied);
    
    new Chart(ctxObj, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Words Studied',
                data: data,
                fill: true,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                tension: 0.3,
                pointBackgroundColor: 'rgba(75, 192, 192, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Words'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                }
            },
            plugins: {
                title: {
                    display: false
                }
            }
        }
    });
}

function initProgressChart(statsData) {
    const ctx = document.getElementById('progress-chart');
    if (!ctx) return;
    
    const ctxObj = ctx.getContext('2d');
    
    // Prepare data for SRS levels
    const srsLevels = statsData.srs_levels || {
        'level_0': 0,
        'level_1': 0,
        'level_2': 0,
        'level_3': 0,
        'level_4': 0,
        'level_5': 0
    };
    
    new Chart(ctxObj, {
        type: 'doughnut',
        data: {
            labels: [
                'New', 'Learning 1', 'Learning 2', 
                'Review 1', 'Review 2', 'Mastered'
            ],
            datasets: [{
                data: [
                    srsLevels.level_0 || 0,
                    srsLevels.level_1 || 0,
                    srsLevels.level_2 || 0,
                    srsLevels.level_3 || 0,
                    srsLevels.level_4 || 0,
                    srsLevels.level_5 || 0
                ],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(255, 205, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(153, 102, 255, 0.7)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 205, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: false
                },
                legend: {
                    position: 'right'
                }
            }
        }
    });
}

function initBandChart(statsData) {
    const ctx = document.getElementById('band-chart');
    if (!ctx) return;
    
    const ctxObj = ctx.getContext('2d');
    
    // Prepare data for IELTS bands
    const bandCounts = statsData.band_counts || {
        'band_5': 0,
        'band_6': 0,
        'band_7': 0,
        'band_8': 0
    };
    
    new Chart(ctxObj, {
        type: 'bar',
        data: {
            labels: ['Band 5', 'Band 6', 'Band 7', 'Band 8+'],
            datasets: [{
                label: 'Number of Words',
                data: [
                    bandCounts.band_5 || 0,
                    bandCounts.band_6 || 0,
                    bandCounts.band_7 || 0,
                    bandCounts.band_8 || 0
                ],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.7)',   // green for band 5
                    'rgba(23, 162, 184, 0.7)',  // info for band 6
                    'rgba(255, 193, 7, 0.7)',   // warning for band 7
                    'rgba(220, 53, 69, 0.7)'    // danger for band 8+
                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)',
                    'rgba(23, 162, 184, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(220, 53, 69, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Words'
                    }
                }
            },
            plugins: {
                title: {
                    display: false
                },
                legend: {
                    display: false
                }
            }
        }
    });
}