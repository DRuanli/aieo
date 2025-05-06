<?php
require_once 'includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize variables
$editId = null;
$formData = [
    'english' => '',
    'vietnamese' => '',
    'context' => '',
    'examples' => '',
    'synonyms' => '',
    'antonyms' => '',
    'category' => [],
    'difficulty' => 'medium'
];

// Check if editing existing entry
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $editId = $_GET['edit'];
    $vocabulary = getVocabularyById($editId);
    
    if ($vocabulary) {
        $formData = $vocabulary;
        // Convert arrays to comma-separated strings for form
        $formData['synonyms'] = is_array($vocabulary['synonyms']) ? implode(', ', $vocabulary['synonyms']) : '';
        $formData['antonyms'] = is_array($vocabulary['antonyms']) ? implode(', ', $vocabulary['antonyms']) : '';
        // Convert examples array to string for form
        $formData['examples'] = is_array($vocabulary['examples']) ? implode("\n", $vocabulary['examples']) : '';
    }
}

// Get all categories
$categories = getCategories();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $english = trim($_POST['english'] ?? '');
    $vietnamese = trim($_POST['vietnamese'] ?? '');
    $context = trim($_POST['context'] ?? '');
    $examples = trim($_POST['examples'] ?? '');
    $synonyms = trim($_POST['synonyms'] ?? '');
    $antonyms = trim($_POST['antonyms'] ?? '');
    $category = $_POST['category'] ?? [];
    $difficulty = $_POST['difficulty'] ?? 'medium';
    
    $errors = [];
    
    // Validate inputs
    if (empty($english)) {
        $errors[] = "English word is required";
    }
    
    if (empty($vietnamese)) {
        $errors[] = "Vietnamese meaning is required";
    }
    
    // Process audio files if uploaded
    $pronunciation = ['en' => '', 'vi' => ''];
    
    // Check if audio files were uploaded
    if (!empty($_FILES['audio_en']['name'])) {
        $audio_en_result = handleAudioUpload($_FILES['audio_en'], $editId ?? time(), 'en');
        if ($audio_en_result) {
            $pronunciation['en'] = $audio_en_result;
        } else {
            $errors[] = "Failed to upload English pronunciation audio";
        }
    } elseif ($editId && isset($formData['pronunciation']['en'])) {
        $pronunciation['en'] = $formData['pronunciation']['en'];
    }
    
    if (!empty($_FILES['audio_vi']['name'])) {
        $audio_vi_result = handleAudioUpload($_FILES['audio_vi'], $editId ?? time(), 'vi');
        if ($audio_vi_result) {
            $pronunciation['vi'] = $audio_vi_result;
        } else {
            $errors[] = "Failed to upload Vietnamese pronunciation audio";
        }
    } elseif ($editId && isset($formData['pronunciation']['vi'])) {
        $pronunciation['vi'] = $formData['pronunciation']['vi'];
    }
    
    if (empty($errors)) {
        $data = [
            'english' => $english,
            'vietnamese' => $vietnamese,
            'context' => $context,
            'examples' => $examples,
            'synonyms' => $synonyms,
            'antonyms' => $antonyms,
            'category' => $category,
            'difficulty' => $difficulty,
            'pronunciation' => $pronunciation
        ];
        
        if (saveVocabularyEntry($data, $editId)) {
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
        'examples' => $examples,
        'synonyms' => $synonyms,
        'antonyms' => $antonyms,
        'category' => $category,
        'difficulty' => $difficulty
    ];
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3><?php echo $editId !== null ? 'Edit' : 'Add New'; ?> Vocabulary</h3>
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
                    
                    <form method="post" action="" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="english" class="form-label">English Word*</label>
                                    <input type="text" class="form-control" id="english" name="english" required value="<?php echo htmlspecialchars($formData['english']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vietnamese" class="form-label">Vietnamese Meaning*</label>
                                    <input type="text" class="form-control" id="vietnamese" name="vietnamese" required value="<?php echo htmlspecialchars($formData['vietnamese']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="context" class="form-label">Example Context</label>
                            <textarea class="form-control" id="context" name="context" rows="2"><?php echo htmlspecialchars($formData['context']); ?></textarea>
                            <small class="text-muted">A sentence showing how to use this word.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="examples" class="form-label">Additional Examples</label>
                            <textarea class="form-control" id="examples" name="examples" rows="3"><?php echo htmlspecialchars($formData['examples']); ?></textarea>
                            <small class="text-muted">Add multiple examples, one per line.</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="synonyms" class="form-label">Synonyms (comma-separated)</label>
                                    <input type="text" class="form-control" id="synonyms" name="synonyms" placeholder="e.g. word1, word2, word3" value="<?php echo htmlspecialchars($formData['synonyms']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="antonyms" class="form-label">Antonyms (comma-separated)</label>
                                    <input type="text" class="form-control" id="antonyms" name="antonyms" placeholder="e.g. word1, word2, word3" value="<?php echo htmlspecialchars($formData['antonyms']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Categories</label>
                                    <div class="border p-2 rounded" style="max-height: 150px; overflow-y: auto;">
                                        <?php foreach ($categories as $category): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="category[]" value="<?php echo htmlspecialchars($category['name']); ?>" id="category_<?php echo $category['id']; ?>"
                                                    <?php echo in_array($category['name'], is_array($formData['category']) ? $formData['category'] : []) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="category_<?php echo $category['id']; ?>">
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Difficulty Level</label>
                                    <div class="border p-2 rounded">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="difficulty" value="easy" id="difficulty_easy"
                                                <?php echo $formData['difficulty'] === 'easy' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="difficulty_easy">
                                                Easy
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="difficulty" value="medium" id="difficulty_medium"
                                                <?php echo $formData['difficulty'] === 'medium' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="difficulty_medium">
                                                Medium
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="difficulty" value="hard" id="difficulty_hard"
                                                <?php echo $formData['difficulty'] === 'hard' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="difficulty_hard">
                                                Hard
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="audio_en" class="form-label">English Pronunciation (MP3)</label>
                                    <input type="file" class="form-control" id="audio_en" name="audio_en" accept="audio/mpeg,audio/mp3,audio/wav,audio/ogg">
                                    <?php if (!empty($formData['pronunciation']['en'])): ?>
                                        <div class="mt-2">
                                            <audio controls src="data/audio/<?php echo htmlspecialchars($formData['pronunciation']['en']); ?>"></audio>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="audio_vi" class="form-label">Vietnamese Pronunciation (MP3)</label>
                                    <input type="file" class="form-control" id="audio_vi" name="audio_vi" accept="audio/mpeg,audio/mp3,audio/wav,audio/ogg">
                                    <?php if (!empty($formData['pronunciation']['vi'])): ?>
                                        <div class="mt-2">
                                            <audio controls src="data/audio/<?php echo htmlspecialchars($formData['pronunciation']['vi']); ?>"></audio>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary"><?php echo $editId !== null ? 'Update' : 'Add'; ?> Vocabulary</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>