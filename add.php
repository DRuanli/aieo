<?php
require_once 'includes/functions.php';

// Initialize variables
$editIndex = null;
$formData = [
    'english' => '',
    'vietnamese' => '',
    'context' => '',
    'synonyms' => '',
    'antonyms' => ''
];

// Check if editing existing entry
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editIndex = (int)$_GET['edit'];
    $vocabulary = getVocabulary();
    
    if (isset($vocabulary[$editIndex])) {
        $formData = $vocabulary[$editIndex];
        // Convert arrays to comma-separated strings for form
        $formData['synonyms'] = implode(', ', $formData['synonyms']);
        $formData['antonyms'] = implode(', ', $formData['antonyms']);
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $english = trim($_POST['english'] ?? '');
    $vietnamese = trim($_POST['vietnamese'] ?? '');
    $context = trim($_POST['context'] ?? '');
    $synonyms = array_map('trim', explode(',', $_POST['synonyms'] ?? ''));
    $antonyms = array_map('trim', explode(',', $_POST['antonyms'] ?? ''));
    
    // Filter out empty values
    $synonyms = array_filter($synonyms);
    $antonyms = array_filter($antonyms);
    
    $errors = [];
    
    // Validate inputs
    if (empty($english)) {
        $errors[] = "English word is required";
    }
    
    if (empty($vietnamese)) {
        $errors[] = "Vietnamese meaning is required";
    }
    
    if (empty($errors)) {
        $newEntry = [
            'english' => $english,
            'vietnamese' => $vietnamese,
            'context' => $context,
            'synonyms' => $synonyms,
            'antonyms' => $antonyms
        ];
        
        $vocabulary = getVocabulary();
        
        if ($editIndex !== null) {
            // Update existing entry
            $vocabulary[$editIndex] = $newEntry;
        } else {
            // Add new entry
            $vocabulary[] = $newEntry;
        }
        
        if (saveVocabulary($vocabulary)) {
            header('Location: index.php');
            exit;
        } else {
            $errors[] = "Failed to save vocabulary data";
        }
    }
    
    // Keep submitted data in case of errors
    $formData = [
        'english' => $english,
        'vietnamese' => $vietnamese,
        'context' => $context,
        'synonyms' => $_POST['synonyms'] ?? '',
        'antonyms' => $_POST['antonyms'] ?? ''
    ];
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3><?php echo $editIndex !== null ? 'Edit' : 'Add New'; ?> Vocabulary</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="english" class="form-label">English Word*</label>
                            <input type="text" class="form-control" id="english" name="english" required value="<?php echo htmlspecialchars($formData['english']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="vietnamese" class="form-label">Vietnamese Meaning*</label>
                            <input type="text" class="form-control" id="vietnamese" name="vietnamese" required value="<?php echo htmlspecialchars($formData['vietnamese']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="context" class="form-label">Example Context</label>
                            <textarea class="form-control" id="context" name="context" rows="2"><?php echo htmlspecialchars($formData['context']); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="synonyms" class="form-label">Synonyms (comma-separated)</label>
                            <input type="text" class="form-control" id="synonyms" name="synonyms" placeholder="e.g. word1, word2, word3" value="<?php echo htmlspecialchars($formData['synonyms']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="antonyms" class="form-label">Antonyms (comma-separated)</label>
                            <input type="text" class="form-control" id="antonyms" name="antonyms" placeholder="e.g. word1, word2, word3" value="<?php echo htmlspecialchars($formData['antonyms']); ?>">
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary"><?php echo $editIndex !== null ? 'Update' : 'Add'; ?> Vocabulary</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>