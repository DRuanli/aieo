<?php
require_once 'includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$message = '';
$messageType = '';

// Handle Import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
    if (!empty($_FILES['csv_file']['name'])) {
        $tempFile = $_FILES['csv_file']['tmp_name'];
        
        // Check file extension
        $fileExt = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));
        if ($fileExt !== 'csv') {
            $message = 'Only CSV files are allowed.';
            $messageType = 'danger';
        } else {
            $result = importVocabularyFromCSV($tempFile);
            
            if ($result['status']) {
                $message = $result['message'];
                $messageType = 'success';
            } else {
                $message = 'Import failed: ' . $result['message'];
                $messageType = 'danger';
            }
        }
    } else {
        $message = 'Please select a CSV file to import.';
        $messageType = 'warning';
    }
}

// Handle IELTS Preset Import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_preset'])) {
    $preset = $_POST['preset'] ?? '';
    
    if (empty($preset)) {
        $message = 'Please select an IELTS preset to import.';
        $messageType = 'warning';
    } else {
        // Handle different preset imports
        $presetFile = DATA_DIR . '/presets/' . $preset . '.csv';
        
        if (file_exists($presetFile)) {
            $result = importVocabularyFromCSV($presetFile);
            
            if ($result['status']) {
                $message = 'Successfully imported ' . $result['count'] . ' ' . ucfirst($preset) . ' vocabulary items.';
                $messageType = 'success';
            } else {
                $message = 'Import failed: ' . $result['message'];
                $messageType = 'danger';
            }
        } else {
            $message = 'Preset file not found.';
            $messageType = 'danger';
        }
    }
}

// Handle Export
if (isset($_GET['export'])) {
    // Get filter parameters
    $filters = [];
    if (!empty($_GET['category'])) {
        $filters['category'] = $_GET['category'];
    }
    if (!empty($_GET['difficulty'])) {
        $filters['difficulty'] = $_GET['difficulty'];
    }
    if (!empty($_GET['ielts_band'])) {
        $filters['ielts_band'] = $_GET['ielts_band'];
    }
    
    // Create temporary CSV file
    $tempFile = tempnam(sys_get_temp_dir(), 'vocab_export_');
    $success = exportVocabularyToCSV($tempFile, $filters);
    
    if ($success) {
        // Set headers for download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="ielts_vocabulary_export_' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Output file content
        readfile($tempFile);
        
        // Delete temporary file
        unlink($tempFile);
        exit;
    } else {
        $message = 'Export failed. Please try again.';
        $messageType = 'danger';
    }
}

// Get all categories for filter dropdown
$categories = getCategories();

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3><i class="fas fa-file-import me-2"></i> IELTS Vocabulary Import/Export</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'danger' ? 'times-circle' : 'exclamation-circle'); ?> me-2"></i>
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row mb-4">
                        <!-- IELTS Presets Card -->
                        <div class="col-md-12 mb-4">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5><i class="fas fa-graduation-cap me-2"></i> IELTS Vocabulary Presets</h5>
                                </div>
                                <div class="card-body">
                                    <p>Quickly add high-quality IELTS vocabulary to your collection with our curated presets.</p>
                                    
                                    <form method="post" action="" class="row g-3">
                                        <div class="col-md-6">
                                            <select name="preset" class="form-select">
                                                <option value="">Select an IELTS Preset</option>
                                                <optgroup label="Band-Specific Lists">
                                                    <option value="band5_essential">Band 5 Essential Vocabulary (100 words)</option>
                                                    <option value="band6_essential">Band 6 Essential Vocabulary (100 words)</option>
                                                    <option value="band7_essential">Band 7 Essential Vocabulary (100 words)</option>
                                                    <option value="band8_essential">Band 8+ Advanced Vocabulary (100 words)</option>
                                                </optgroup>
                                                <optgroup label="Topic-Specific Lists">
                                                    <option value="environment">Environment & Climate Change (50 words)</option>
                                                    <option value="education">Education & Learning (50 words)</option>
                                                    <option value="technology">Technology & Innovation (50 words)</option>
                                                    <option value="health">Health & Wellbeing (50 words)</option>
                                                    <option value="urbanization">Urbanization & Housing (50 words)</option>
                                                </optgroup>
                                                <optgroup label="Skill-Specific Lists">
                                                    <option value="writing_task2">Writing Task 2 Keywords (75 words)</option>
                                                    <option value="speaking_part3">Speaking Part 3 Discussion (75 words)</option>
                                                    <option value="academic_collocations">Academic Collocations (50 phrases)</option>
                                                </optgroup>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="submit" name="import_preset" class="btn btn-primary">
                                                <i class="fas fa-download me-2"></i> Import Selected Preset
                                            </button>
                                        </div>
                                    </form>
                                    
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle me-2"></i> Each preset includes carefully selected vocabulary with IELTS-specific contexts, examples, and collocations.
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                        <!-- Import Section -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5><i class="fas fa-file-import me-2"></i> Import Custom Vocabulary</h5>
                                </div>
                                <div class="card-body">
                                    <form method="post" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="csv_file" class="form-label">Select CSV File</label>
                                            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                                            <div class="form-text">
                                                CSV must include 'english' and 'vietnamese' columns. Optional columns: 'context', 'examples', 'synonyms', 'antonyms', 'collocations', 'ielts_band', 'ielts_usage', 'category', 'difficulty'.
                                            </div>
                                        </div>
                                        <button type="submit" name="import" class="btn btn-primary">
                                            <i class="fas fa-upload me-2"></i> Import Data
                                        </button>
                                    </form>
                                    
                                    <div class="mt-4">
                                        <h6>CSV Format Example:</h6>
                                        <div class="bg-light p-2 border rounded small">
                                            <pre>english,vietnamese,context,synonyms,antonyms,collocations,ielts_band,category,difficulty
mitigate,giảm thiểu,"Governments must mitigate the effects of climate change.","reduce, alleviate",worsen,"mitigate effects, mitigate impact",7,"environment,society",hard
paradigm,mô hình,"The discovery led to a paradigm shift in scientific thinking.","model, framework",,"paradigm shift, new paradigm",8,"education,science",hard</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Export Section -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5><i class="fas fa-file-export me-2"></i> Export Vocabulary</h5>
                                </div>
                                <div class="card-body">
                                    <form method="get" action="">
                                        <input type="hidden" name="export" value="1">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Filter by IELTS Topic (Optional)</label>
                                            <select class="form-select" name="category">
                                                <option value="">All Topics</option>
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?php echo htmlspecialchars($cat['name']); ?>">
                                                        <?php echo htmlspecialchars($cat['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Filter by IELTS Band (Optional)</label>
                                            <select class="form-select" name="ielts_band">
                                                <option value="">All Bands</option>
                                                <option value="5">Band 5</option>
                                                <option value="6">Band 6</option>
                                                <option value="7">Band 7</option>
                                                <option value="8">Band 8+</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Filter by Difficulty (Optional)</label>
                                            <select class="form-select" name="difficulty">
                                                <option value="">All Difficulties</option>
                                                <option value="easy">Easy</option>
                                                <option value="medium">Medium</option>
                                                <option value="hard">Hard</option>
                                            </select>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-download me-2"></i> Export to CSV
                                        </button>
                                    </form>
                                    
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle me-2"></i> Export your vocabulary to study offline or share with other IELTS students.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Share & Collaborate Section -->
                    <div class="card border-info mb-4">
                        <div class="card-header bg-info text-white">
                            <h5><i class="fas fa-users me-2"></i> Share & Collaborate</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-7">
                                    <h6>Share Your IELTS Vocabulary Lists</h6>
                                    <p>Create and export your own curated vocabulary lists for specific IELTS topics or band scores, then share them with fellow students.</p>
                                    <p>Join our community to access more shared vocabulary lists from teachers and high-scoring IELTS candidates.</p>
                                </div>
                                <div class="col-md-5">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6>Top Community Lists</h6>
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Academic Word List - Essential 100
                                                    <span class="badge bg-primary rounded-pill">4.8 ★</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Writing Task 2 Vocabulary - Environment
                                                    <span class="badge bg-primary rounded-pill">4.7 ★</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Speaking Advanced Phrases
                                                    <span class="badge bg-primary rounded-pill">4.6 ★</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Back to Vocabulary List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>