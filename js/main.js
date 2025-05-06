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
});

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
        document.getElementById('flashcard-synonyms').textContent = currentWord.synonyms && currentWord.synonyms.length > 0 ? 
            'Synonyms: ' + currentWord.synonyms.join(', ') : '';
        document.getElementById('flashcard-antonyms').textContent = currentWord.antonyms && currentWord.antonyms.length > 0 ? 
            'Antonyms: ' + currentWord.antonyms.join(', ') : '';
        
        // Display categories as badges if available
        const categoriesEl = document.getElementById('flashcard-categories');
        categoriesEl.innerHTML = '';
        if (currentWord.category && currentWord.category.length > 0) {
            currentWord.category.forEach(cat => {
                const badge = document.createElement('span');
                badge.className = 'badge bg-info text-dark me-1';
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
                    options: generateOptions(item.vietnamese, 'vietnamese'),
                    audio: item.pronunciation ? item.pronunciation.en : null,
                    wordId: item.id
                });
            } else {
                // Vietnamese to English
                quizQuestions.push({
                    question: `What is the English word for "${item.vietnamese}"?`,
                    correctAnswer: item.english,
                    options: generateOptions(item.english, 'english'),
                    audio: item.pronunciation ? item.pronunciation.vi : null,
                    wordId: item.id
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
                    feedbackContainer.innerHTML = '<div class="alert alert-success">Correct!</div>';
                    score++;
                    
                    // Record result if using SRS
                    if (useSRS && currentWordId) {
                        recordVocabularyResult(currentWordId, 'correct', 'quiz');
                    }
                } else {
                    feedbackContainer.innerHTML = `
                        <div class="alert alert-danger">
                            Incorrect. The correct answer is: "${currentQuestion.correctAnswer}"
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
        const shuffled = [...vocabularyData].sort(() => Math.random() - 0.5).slice(0, count);
        
        leftItems = shuffled.map(item => ({ 
            id: Math.random().toString(36).substr(2, 9), 
            text: item.english,
            wordId: item.id,
            audio: item.pronunciation ? item.pronunciation.en : null
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
                <p>You got ${correctMatches} out of ${totalMatches} matches correct!</p>
                ${correctMatches !== totalMatches ? '<p>Try again or start a new game.</p>' : ''}
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
            
            const textSpan = document.createElement('span');
            textSpan.textContent = item.text;
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

function initStatsDashboard() {
    // Get stats data from the element
    const statsDataEl = document.getElementById('stats-data');
    if (!statsDataEl) return;
    
    const statsData = JSON.parse(statsDataEl.getAttribute('data-stats'));
    
    // Initialize charts
    initAccuracyChart(statsData);
    initActivityChart(statsData);
    initProgressChart(statsData);
}

function initAccuracyChart(statsData) {
    const ctx = document.getElementById('accuracy-chart').getContext('2d');
    
    // Prepare data
    const modes = ['flashcard', 'quiz', 'matching'];
    const accuracyData = modes.map(mode => {
        return statsData.accuracy && statsData.accuracy[mode] 
            ? statsData.accuracy[mode].correct / (statsData.accuracy[mode].correct + statsData.accuracy[mode].incorrect) * 100
            : 0;
    });
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Flashcards', 'Quiz', 'Matching'],
            datasets: [{
                label: 'Accuracy %',
                data: accuracyData,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(153, 102, 255, 0.5)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(153, 102, 255, 1)'
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
                    display: true,
                    text: 'Accuracy by Study Mode'
                }
            }
        }
    });
}

function initActivityChart(statsData) {
    const ctx = document.getElementById('activity-chart').getContext('2d');
    
    // Prepare data
    const dailyActivity = statsData.daily_activity || [];
    const sortedActivity = [...dailyActivity].sort((a, b) => new Date(a.date) - new Date(b.date));
    
    const labels = sortedActivity.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
    
    const data = sortedActivity.map(item => item.words_studied);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Words Studied',
                data: data,
                fill: false,
                borderColor: 'rgba(75, 192, 192, 1)',
                tension: 0.1
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
                    display: true,
                    text: 'Daily Learning Activity'
                }
            }
        }
    });
}

function initProgressChart(statsData) {
    const ctx = document.getElementById('progress-chart').getContext('2d');
    
    // Prepare data for SRS levels
    const srsLevels = statsData.srs_levels || {
        'level_0': 0,
        'level_1': 0,
        'level_2': 0,
        'level_3': 0,
        'level_4': 0,
        'level_5': 0
    };
    
    new Chart(ctx, {
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
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(255, 159, 64, 0.5)',
                    'rgba(255, 205, 86, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(153, 102, 255, 0.5)'
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
            plugins: {
                title: {
                    display: true,
                    text: 'Vocabulary Progress by SRS Level'
                }
            }
        }
    });
}