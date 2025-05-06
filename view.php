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
                                    
                                    <?php if (!empty($word['synonyms'])): ?>
                                        <div class="mt-3">
                                            <h5>Synonyms</h5>
                                            <p>
                                                <?php foreach ($word['synonyms'] as $synonym): ?>
                                                    <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($synonym); ?></span>
                                                <?php endforeach; ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($word['antonyms'])): ?>
                                        <div class="mt-3">
                                            <h5>Antonyms</h5>
                                            <p>
                                                <?php foreach ($word['antonyms'] as $antonym): ?>
                                                    <span class="badge bg-danger me-1"><?php echo htmlspecialchars($antonym); ?></span>
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
                                    <h2><?php echo htmlspecialchars($word['vietnamese']); ?></h2>
                                    
                                    <?php if (!empty($word['pronunciation']) && !empty($word['pronunciation']['vi'])): ?>
                                        <div class="mt-3">
                                            <h5>Pronunciation</h5>
                                            <audio controls src="data/audio/<?php echo htmlspecialchars($word['pronunciation']['vi']); ?>" class="w-100"></audio>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($word['category'])): ?>
                                        <div class="mt-3">
                                            <h5>Categories</h5>
                                            <p>
                                                <?php foreach ($word['category'] as $category): ?>
                                                    <span class="badge bg-info text-dark me-1"><?php echo htmlspecialchars($category); ?></span>
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
                                    <h4>Context & Examples</h4>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($word['context'])): ?>
                                        <div class="mb-3">
                                            <h5>Primary Context</h5>
                                            <p class="p-2 bg-light rounded"><?php echo htmlspecialchars($word['context']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($word['examples'])): ?>
                                        <div>
                                            <h5>Additional Examples</h5>
                                            <ul class="list-group">
                                                <?php foreach ($word['examples'] as $example): ?>
                                                    <li class="list-group-item"><?php echo htmlspecialchars($example); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
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

<?php include 'includes/footer.php'; ?>