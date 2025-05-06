<?php
require_once 'includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if word ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$wordId = $_GET['id'];
$word = getVocabularyById($wordId);

// If word not found, redirect to index
if (!$word) {
    header('Location: index.php');
    exit;
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3>Word Details</h3>
                    <div>
                        <a href="add.php?edit=<?php echo $wordId; ?>" class="btn btn-light btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="index.php" class="btn btn-light btn-sm ms-2">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- IELTS Band Level Badge -->
                    <?php if (!empty($word['ielts_band'])): ?>
                    <div class="mb-3 text-center">
                        <span class="badge <?php 
                            echo $word['ielts_band'] >= 8 ? 'bg-danger' : 
                                ($word['ielts_band'] >= 7 ? 'bg-warning text-dark' : 
                                 ($word['ielts_band'] >= 6 ? 'bg-info text-dark' : 'bg-success')); 
                        ?> p-2 fs-5">
                            IELTS Band <?php echo htmlspecialchars($word['ielts_band']); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h4>English</h4>
                                </div>
                                <div class="card-body">
                                    <h2><?php echo htmlspecialchars($word['english']); ?></h2>
                                    
                                    <?php if (!empty($word['pronunciation']) && !empty($word['pronunciation']['en'])): ?>
                                        <div class="mt-3">
                                            <h5>Pronunciation</h5>
                                            <audio controls src="data/audio/<?php echo htmlspecialchars($word['pronunciation']['en']); ?>" class="w-100"></audio>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($word['collocations'])): ?>
                                        <div class="mt-3">
                                            <h5>Common Collocations</h5>
                                            <p>
                                                <?php foreach ($word['collocations'] as $collocation): ?>
                                                    <span class="badge bg-light text-dark border mb-1 me-1"><?php echo htmlspecialchars($collocation); ?></span>
                                                <?php endforeach; ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($word['synonyms'])): ?>
                                        <div class="mt-3">
                                            <h5>Synonyms</h5>
                                            <p>
                                                <?php foreach ($word['synonyms'] as $synonym): ?>
                                                    <span class="badge bg-secondary me-1 mb-1"><?php echo htmlspecialchars($synonym); ?></span>
                                                <?php endforeach; ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($word['antonyms'])): ?>
                                        <div class="mt-3">
                                            <h5>Antonyms</h5>
                                            <p>
                                                <?php foreach ($word['antonyms'] as $antonym): ?>
                                                    <span class="badge bg-danger me-1 mb-1"><?php echo htmlspecialchars($antonym); ?></span>
                                                <?php endforeach; ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h4>Vietnamese</h4>
                                </div>
                                <div class="card-body">
                                    <h2 class="vietnamese"><?php echo htmlspecialchars($word['vietnamese']); ?></h2>
                                    
                                    <?php if (!empty($word['pronunciation']) && !empty($word['pronunciation']['vi'])): ?>
                                        <div class="mt-3">
                                            <h5>Pronunciation</h5>
                                            <audio controls src="data/audio/<?php echo htmlspecialchars($word['pronunciation']['vi']); ?>" class="w-100"></audio>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($word['category'])): ?>
                                        <div class="mt-3">
                                            <h5>IELTS Topics</h5>
                                            <p>
                                                <?php foreach ($word['category'] as $category): ?>
                                                    <span class="badge bg-info text-dark me-1 mb-1"><?php echo htmlspecialchars($category); ?></span>
                                                <?php endforeach; ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-3">
                                        <h5>Difficulty</h5>
                                        <span class="badge <?php 
                                            echo $word['difficulty'] === 'easy' ? 'bg-success' : 
                                                ($word['difficulty'] === 'medium' ? 'bg-warning text-dark' : 'bg-danger'); 
                                        ?>">
                                            <?php echo ucfirst(htmlspecialchars($word['difficulty'] ?? 'medium')); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h4>IELTS Usage</h4>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($word['context'])): ?>
                                        <div class="mb-4">
                                            <h5>Primary Context</h5>
                                            <p class="p-3 bg-light rounded border-start border-primary border-3"><?php echo htmlspecialchars($word['context']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($word['examples'])): ?>
                                        <div class="mb-4">
                                            <h5>IELTS Example Sentences</h5>
                                            <ul class="list-group">
                                                <?php foreach ($word['examples'] as $example): ?>
                                                    <li class="list-group-item"><?php echo htmlspecialchars($example); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($word['ielts_usage'])): ?>
                                        <div class="mb-4">
                                            <h5>IELTS Usage Tips</h5>
                                            <div class="alert alert-info">
                                                <?php echo nl2br(htmlspecialchars($word['ielts_usage'])); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mb-4">
                                        <h5>Practice Suggestions</h5>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="card h-100">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Writing Practice</h6>
                                                        <p class="card-text">Use this word in an IELTS Task 2 essay about 
                                                            <?php 
                                                            if (!empty($word['category']) && count($word['category']) > 0) {
                                                                echo htmlspecialchars($word['category'][0]);
                                                            } else {
                                                                echo 'a relevant topic';
                                                            }
                                                            ?>.
                                                        </p>
                                                        <a href="playground.php?mode=writing&english=<?php echo urlencode($word['english']); ?>" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-pen"></i> Practice
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card h-100">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Collocation Exercise</h6>
                                                        <p class="card-text">Practice using this word with its common collocations in context.</p>
                                                        <a href="playground.php?mode=collocations&english=<?php echo urlencode($word['english']); ?>" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-link"></i> Practice
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card h-100">
                                                    <div class="card-body">
                                                        <h6 class="card-title">Speaking Practice</h6>
                                                        <p class="card-text">Use this word in speaking responses about <?php 
                                                            if (!empty($word['category']) && count($word['category']) > 0) {
                                                                echo htmlspecialchars($word['category'][0]);
                                                            } else {
                                                                echo 'a relevant topic';
                                                            }
                                                            ?>.
                                                        </p>
                                                        <a href="#" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#speakingModal">
                                                            <i class="fas fa-microphone"></i> Practice
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Study History Section -->
                    <?php if (isset($_SESSION['user_id']) && isset($word['review_history']) && !empty($word['review_history'])): ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h4>Study History</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <h5>SRS Status</h5>
                                                        <p><strong>Current Level:</strong> <?php echo $word['srs_level'] ?? 0; ?> / 5</p>
                                                        <p><strong>Next Review:</strong> <?php echo isset($word['next_review']) ? date('F j, Y', strtotime($word['next_review'])) : 'Not scheduled'; ?></p>
                                                        
                                                        <div class="progress mt-2">
                                                            <div class="progress-bar bg-success" role="progressbar" 
                                                                style="width: <?php echo (($word['srs_level'] ?? 0) / 5) * 100; ?>%" 
                                                                aria-valuenow="<?php echo $word['srs_level'] ?? 0; ?>" 
                                                                aria-valuemin="0" aria-valuemax="5">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h5>Review History</h5>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Date</th>
                                                                <th>Study Mode</th>
                                                                <th>Result</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php 
                                                            $history = $word['review_history'];
                                                            // Sort by date, most recent first
                                                            usort($history, function($a, $b) {
                                                                return strtotime($b['date']) - strtotime($a['date']);
                                                            });
                                                            
                                                            foreach ($history as $review): 
                                                            ?>
                                                                <tr>
                                                                    <td><?php echo date('M j, Y', strtotime($review['date'])); ?></td>
                                                                    <td><?php echo ucfirst($review['mode']); ?></td>
                                                                    <td>
                                                                        <span class="badge <?php echo $review['result'] === 'correct' ? 'bg-success' : 'bg-danger'; ?>">
                                                                            <?php echo ucfirst($review['result']); ?>
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3 text-center">
                                            <a href="playground.php?mode=flashcards&english=<?php echo urlencode($word['english']); ?>" class="btn btn-primary">
                                                <i class="fas fa-play"></i> Review This Word
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Speaking Practice Modal -->
<div class="modal fade" id="speakingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Speaking Practice: <?php echo htmlspecialchars($word['english']); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6>IELTS Speaking Question</h6>
                    <div class="alert alert-primary">
                        <?php 
                        $topics = $word['category'] ?? ['general'];
                        $topic = $topics[array_rand($topics)];
                        
                        $questions = [
                            'Education' => [
                                "Describe a teacher who has influenced you. Use the word \"" . htmlspecialchars($word['english']) . "\" in your response.",
                                "What changes would you like to see in the education system? Use the word \"" . htmlspecialchars($word['english']) . "\" in your answer."
                            ],
                            'Environment' => [
                                "How has the environment in your city changed in recent years? Use the word \"" . htmlspecialchars($word['english']) . "\" in your response.",
                                "What can individuals do to protect the environment? Include the word \"" . htmlspecialchars($word['english']) . "\" in your answer."
                            ],
                            'Technology' => [
                                "How has technology changed the way we communicate? Use the word \"" . htmlspecialchars($word['english']) . "\" in your response.",
                                "Describe a piece of technology that has improved your life. Include the word \"" . htmlspecialchars($word['english']) . "\" in your answer."
                            ],
                            'Health' => [
                                "What do you do to stay healthy? Use the word \"" . htmlspecialchars($word['english']) . "\" in your response.",
                                "How important is mental health compared to physical health? Include the word \"" . htmlspecialchars($word['english']) . "\" in your answer."
                            ],
                            'Work' => [
                                "What skills are important in your field of work or study? Use the word \"" . htmlspecialchars($word['english']) . "\" in your response.",
                                "Describe your ideal work environment. Include the word \"" . htmlspecialchars($word['english']) . "\" in your answer."
                            ],
                            'general' => [
                                "What changes would you like to see in your community? Use the word \"" . htmlspecialchars($word['english']) . "\" in your response.",
                                "Describe a challenge you've overcome in your life. Include the word \"" . htmlspecialchars($word['english']) . "\" in your answer."
                            ]
                        ];
                        
                        // Get questions for the chosen topic or use general questions
                        $topicQuestions = isset($questions[$topic]) ? $questions[$topic] : $questions['general'];
                        
                        // Randomly select one question
                        echo $topicQuestions[array_rand($topicQuestions)];
                        ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <h6>Record Your Response</h6>
                    <div class="text-center">
                        <button id="startRecording" class="btn btn-danger">
                            <i class="fas fa-microphone"></i> Start Recording
                        </button>
                        <button id="stopRecording" class="btn btn-secondary" disabled>
                            <i class="fas fa-stop"></i> Stop Recording
                        </button>
                        
                        <div class="mt-3" id="recordingStatus"></div>
                        
                        <div class="mt-3" id="audioPlayback" style="display: none;">
                            <audio id="recordedAudio" controls></audio>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <h6>Tips for Using "<?php echo htmlspecialchars($word['english']); ?>" in Speaking</h6>
                    <ul>
                        <?php if (!empty($word['collocations'])): ?>
                            <li>Try using common collocations like: <?php echo htmlspecialchars(implode(', ', array_slice($word['collocations'], 0, 3))); ?></li>
                        <?php endif; ?>
                        <li>Make sure to pronounce the word clearly and confidently.</li>
                        <li>Use the word in a relevant context that showcases your understanding of its meaning.</li>
                        <li>If appropriate, use this word alongside related vocabulary from the same topic.</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Audio recording functionality
    document.addEventListener('DOMContentLoaded', function() {
        const startButton = document.getElementById('startRecording');
        const stopButton = document.getElementById('stopRecording');
        const recordingStatus = document.getElementById('recordingStatus');
        const audioPlayback = document.getElementById('audioPlayback');
        const recordedAudio = document.getElementById('recordedAudio');
        
        let mediaRecorder;
        let audioChunks = [];
        
        startButton.addEventListener('click', async function() {
            audioChunks = [];
            
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                
                mediaRecorder = new MediaRecorder(stream);
                
                mediaRecorder.addEventListener('dataavailable', event => {
                    audioChunks.push(event.data);
                });
                
                mediaRecorder.addEventListener('stop', () => {
                    const audioBlob = new Blob(audioChunks, { type: 'audio/mp3' });
                    const audioUrl = URL.createObjectURL(audioBlob);
                    recordedAudio.src = audioUrl;
                    audioPlayback.style.display = 'block';
                    recordingStatus.innerHTML = '<span class="text-success">Recording complete!</span>';
                    
                    // Stop all tracks on the stream to release the microphone
                    stream.getTracks().forEach(track => track.stop());
                });
                
                mediaRecorder.start();
                
                startButton.disabled = true;
                stopButton.disabled = false;
                recordingStatus.innerHTML = '<span class="text-danger">Recording... (speak now)</span>';
                
            } catch (err) {
                console.error('Error accessing microphone:', err);
                recordingStatus.innerHTML = '<span class="text-danger">Error accessing microphone. Please check your browser permissions.</span>';
            }
        });
        
        stopButton.addEventListener('click', function() {
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
                startButton.disabled = false;
                stopButton.disabled = true;
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>