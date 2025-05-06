<?php
require_once 'includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$difficulty = isset($_GET['difficulty']) ? $_GET['difficulty'] : '';
$ielts_band = isset($_GET['ielts_band']) ? $_GET['ielts_band'] : '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;

// Prepare filters
$filters = [];
if (!empty($search)) {
    $filters['search'] = $search;
}
if (!empty($category)) {
    $filters['category'] = $category;
}
if (!empty($difficulty)) {
    $filters['difficulty'] = $difficulty;
}
if (!empty($ielts_band)) {
    $filters['ielts_band'] = $ielts_band;
}

// Get vocabulary with filters and pagination
$result = getVocabulary($filters, $page, $perPage);
$vocabulary = $result['data'];
$pagination = $result['pagination'];

// Get all categories for filter dropdown
$categories = getCategories();

// Get IELTS readiness stats if user is logged in
$ieltsReadiness = null;
if (isset($_SESSION['user_id'])) {
    $ieltsReadiness = getIELTSReadiness($_SESSION['user_id']);
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <!-- IELTS Readiness Banner (for logged in users) -->
    <?php if (isset($_SESSION['user_id']) && $ieltsReadiness): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">IELTS Vocabulary Readiness: <?php echo $ieltsReadiness['overall']; ?>%</h4>
                            <div class="progress bg-light bg-opacity-25">
                                <div class="progress-bar bg-white" role="progressbar" style="width: <?php echo $ieltsReadiness['overall']; ?>%" aria-valuenow="<?php echo $ieltsReadiness['overall']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="mt-2 small">
                                <span class="me-3"><i class="fas fa-check-circle"></i> <?php echo $ieltsReadiness['mastered']; ?> mastered</span>
                                <span class="me-3"><i class="fas fa-graduation-cap"></i> <?php echo $ieltsReadiness['learning']; ?> learning</span>
                                <span><i class="fas fa-hourglass-half"></i> <?php echo $ieltsReadiness['due']; ?> due for review</span>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <a href="playground.php?use_srs=1" class="btn btn-light">
                                <i class="fas fa-play"></i> Start IELTS Practice
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Daily Word Section with IELTS Context -->
    <?php $dailyWord = getDailyWord(); ?>
    <?php if ($dailyWord): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-star"></i> IELTS Word of the Day</h4>
                    <?php if (!empty($dailyWord['ielts_band'])): ?>
                    <span class="badge bg-light text-dark">Band <?php echo $dailyWord['ielts_band']; ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h2 class="card-title"><?php echo htmlspecialchars($dailyWord['english']); ?></h2>
                            <h4 class="text-muted"><?php echo htmlspecialchars($dailyWord['vietnamese']); ?></h4>
                            
                            <?php if (!empty($dailyWord['context'])): ?>
                                <p class="border-start border-info border-3 ps-3 fst-italic">"<?php echo htmlspecialchars($dailyWord['context']); ?>"</p>
                            <?php endif; ?>
                            
                            <div class="mt-3">
                                <?php if (!empty($dailyWord['collocations'])): ?>
                                    <p><strong>Common collocations:</strong> <?php echo htmlspecialchars(implode(', ', $dailyWord['collocations'])); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($dailyWord['synonyms'])): ?>
                                    <p><strong>Synonyms:</strong> <?php echo htmlspecialchars(implode(', ', $dailyWord['synonyms'])); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($dailyWord['antonyms'])): ?>
                                    <p><strong>Antonyms:</strong> <?php echo htmlspecialchars(implode(', ', $dailyWord['antonyms'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">IELTS Example Usage</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($dailyWord['examples'])): ?>
                                        <ul class="list-group list-group-flush">
                                            <?php foreach ($dailyWord['examples'] as $example): ?>
                                                <li class="list-group-item border-0 ps-0 py-2"><?php echo htmlspecialchars($example); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($dailyWord['ielts_usage'])): ?>
                                        <div class="alert alert-info mt-2">
                                            <h6 class="alert-heading">IELTS Tip:</h6>
                                            <?php echo htmlspecialchars($dailyWord['ielts_usage']); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($dailyWord['pronunciation'])): ?>
                                        <div class="mt-3">
                                            <?php if (!empty($dailyWord['pronunciation']['en'])): ?>
                                                <div class="mb-2">
                                                    <label class="form-label">English Pronunciation:</label>
                                                    <audio controls src="data/audio/<?php echo htmlspecialchars($dailyWord['pronunciation']['en']); ?>" class="w-100"></audio>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($dailyWord['pronunciation']['vi'])): ?>
                                                <div>
                                                    <label class="form-label">Vietnamese Pronunciation:</label>
                                                    <audio controls src="data/audio/<?php echo htmlspecialchars($dailyWord['pronunciation']['vi']); ?>" class="w-100"></audio>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3>IELTS Vocabulary List</h3>
                    <div>
                        <a href="import-export.php" class="btn btn-light btn-sm me-2">
                            <i class="fas fa-file-import"></i> Import/Export
                        </a>
                        <a href="add.php" class="btn btn-light btn-sm">
                            <i class="fas fa-plus"></i> Add New
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search and Filters -->
                    <form method="get" action="" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="category" onchange="this.form.submit()">
                                    <option value="">All IELTS Topics</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo $category === $cat['name'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="difficulty" onchange="this.form.submit()">
                                    <option value="">All Difficulties</option>
                                    <option value="easy" <?php echo $difficulty === 'easy' ? 'selected' : ''; ?>>Easy</option>
                                    <option value="medium" <?php echo $difficulty === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                    <option value="hard" <?php echo $difficulty === 'hard' ? 'selected' : ''; ?>>Hard</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="ielts_band" onchange="this.form.submit()">
                                    <option value="">All Band Levels</option>
                                    <option value="5" <?php echo $ielts_band === '5' ? 'selected' : ''; ?>>Band 5</option>
                                    <option value="6" <?php echo $ielts_band === '6' ? 'selected' : ''; ?>>Band 6</option>
                                    <option value="7" <?php echo $ielts_band === '7' ? 'selected' : ''; ?>>Band 7</option>
                                    <option value="8" <?php echo $ielts_band === '8' ? 'selected' : ''; ?>>Band 8+</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <?php if (!empty($search) || !empty($category) || !empty($difficulty) || !empty($ielts_band)): ?>
                                    <a href="index.php" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-times"></i> Clear Filters
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                    
                    <?php if (empty($vocabulary)): ?>
                        <div class="alert alert-info">
                            <?php if (!empty($search) || !empty($category) || !empty($difficulty) || !empty($ielts_band)): ?>
                                No vocabulary items found matching your filters. <a href="index.php">Clear filters</a> or <a href="add.php">add new items</a>.
                            <?php else: ?>
                                No vocabulary items found. Please <a href="add.php">add some</a>!
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>English</th>
                                        <th>Vietnamese</th>
                                        <th>Context</th>
                                        <th>IELTS Topics</th>
                                        <th>Band</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vocabulary as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['english']); ?></strong>
                                            <?php if (!empty($item['pronunciation']) && !empty($item['pronunciation']['en'])): ?>
                                                <a href="#" class="play-audio ms-2 text-primary" data-src="data/audio/<?php echo htmlspecialchars($item['pronunciation']['en']); ?>">
                                                    <i class="fas fa-volume-up"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($item['collocations'])): ?>
                                                <div class="small text-muted mt-1">
                                                    <?php echo htmlspecialchars(implode(', ', array_slice($item['collocations'], 0, 2))); ?>
                                                    <?php if (count($item['collocations']) > 2): ?>...<?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($item['vietnamese']); ?>
                                            <?php if (!empty($item['pronunciation']) && !empty($item['pronunciation']['vi'])): ?>
                                                <a href="#" class="play-audio ms-2 text-primary" data-src="data/audio/<?php echo htmlspecialchars($item['pronunciation']['vi']); ?>">
                                                    <i class="fas fa-volume-up"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-truncate" style="max-width: 250px;">
                                            <?php echo htmlspecialchars($item['context']); ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($item['category'])): ?>
                                                <?php foreach ($item['category'] as $cat): ?>
                                                    <span class="badge rounded-pill bg-light text-dark border"><?php echo htmlspecialchars($cat); ?></span>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($item['ielts_band'])): ?>
                                                <span class="badge <?php 
                                                    echo $item['ielts_band'] >= 8 ? 'bg-danger' : 
                                                        ($item['ielts_band'] >= 7 ? 'bg-warning text-dark' : 
                                                         ($item['ielts_band'] >= 6 ? 'bg-info text-dark' : 'bg-success')); 
                                                ?>">
                                                    Band <?php echo htmlspecialchars($item['ielts_band']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="view.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="add.php?edit=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="#" class="btn btn-sm btn-outline-danger delete-vocab" data-id="<?php echo $item['id']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($pagination['total_pages'] > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php echo !$pagination['has_previous'] ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo !$pagination['has_previous'] ? '#' : '?page=' . ($pagination['current_page'] - 1) . '&search=' . urlencode($search) . '&category=' . urlencode($category) . '&difficulty=' . urlencode($difficulty) . '&ielts_band=' . urlencode($ielts_band); ?>">
                                            Previous
                                        </a>
                                    </li>
                                    
                                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                        <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&difficulty=<?php echo urlencode($difficulty); ?>&ielts_band=<?php echo urlencode($ielts_band); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?php echo !$pagination['has_next'] ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo !$pagination['has_next'] ? '#' : '?page=' . ($pagination['current_page'] + 1) . '&search=' . urlencode($search) . '&category=' . urlencode($category) . '&difficulty=' . urlencode($difficulty) . '&ielts_band=' . urlencode($ielts_band); ?>">
                                            Next
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden audio player for pronunciation -->
<audio id="audio-player" style="display: none;"></audio>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this vocabulary item?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" class="btn btn-danger" id="confirmDelete">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Pronunciation audio player
        const audioPlayer = document.getElementById('audio-player');
        const playButtons = document.querySelectorAll('.play-audio');
        
        playButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const audioSrc = this.getAttribute('data-src');
                audioPlayer.src = audioSrc;
                audioPlayer.play();
            });
        });
        
        // Delete confirmation
        const deleteButtons = document.querySelectorAll('.delete-vocab');
        const confirmDeleteButton = document.getElementById('confirmDelete');
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                confirmDeleteButton.setAttribute('href', `index.php?delete=${id}`);
                deleteModal.show();
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>