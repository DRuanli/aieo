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
    
    // Create temporary CSV file
    $tempFile = tempnam(sys_get_temp_dir(), 'vocab_export_');
    $success = exportVocabularyToCSV($tempFile, $filters);
    
    if ($success) {
        // Set headers for download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="vocabulary_export_' . date('Y-m-d') . '.csv"');
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
                    <h3>Import/Export Vocabulary</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <!-- Import Section -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-file-import"></i> Import Vocabulary</h5>
                                </div>
                                <div class="card-body">
                                    <form method="post" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="csv_file" class="form-label">Select CSV File</label>
                                            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                                            <div class="form-text">
                                                CSV must include 'english' and 'vietnamese' columns. Optional columns: 'context', 'examples', 'synonyms', 'antonyms', 'category', 'difficulty'.
                                            </div>
                                        </div>
                                        <button type="submit" name="import" class="btn btn-primary">
                                            <i class="fas fa-upload"></i> Import Data
                                        </button>
                                    </form>
                                    
                                    <div class="mt-4">
                                        <h6>CSV Format Example:</h6>
                                        <pre class="bg-light p-2 border rounded"><code>english,vietnamese,context,synonyms,antonyms,category,difficulty
hello,xin chào,"Hello, how are you?","hi, greetings",goodbye,"basics,greetings",easy
book,sách,"I read a book","text, publication",,"basics,education",medium</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Export Section -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-file-export"></i> Export Vocabulary</h5>
                                </div>
                                <div class="card-body">
                                    <form method="get" action="">
                                        <input type="hidden" name="export" value="1">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Filter by Category (Optional)</label>
                                            <select class="form-select" name="category">
                                                <option value="">All Categories</option>
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?php echo htmlspecialchars($cat['name']); ?>">
                                                        <?php echo htmlspecialchars($cat['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
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
                                            <i class="fas fa-download"></i> Export to CSV
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Vocabulary List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>