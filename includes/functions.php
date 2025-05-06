<?php
/**
 * Function to get vocabulary data
 * 
 * @return array Vocabulary data
 */
function getVocabulary() {
    $dataFile = __DIR__ . '/../data/vocabulary.json';
    
    // Create data directory if it doesn't exist
    if (!file_exists(__DIR__ . '/../data')) {
        mkdir(__DIR__ . '/../data', 0755, true);
    }
    
    // Create empty vocabulary file if it doesn't exist
    if (!file_exists($dataFile)) {
        file_put_contents($dataFile, json_encode([]));
    }
    
    $jsonData = file_get_contents($dataFile);
    $vocabulary = json_decode($jsonData, true);
    
    // Handle potential JSON errors
    if ($vocabulary === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON Error: ' . json_last_error_msg());
        return [];
    }
    
    return is_array($vocabulary) ? $vocabulary : [];
}

/**
 * Function to save vocabulary data
 * 
 * @param array $vocabulary Vocabulary data
 * @return bool Success status
 */
function saveVocabulary($vocabulary) {
    $dataFile = __DIR__ . '/../data/vocabulary.json';
    
    // Create data directory if it doesn't exist
    if (!file_exists(__DIR__ . '/../data')) {
        mkdir(__DIR__ . '/../data', 0755, true);
    }
    
    $jsonData = json_encode($vocabulary, JSON_PRETTY_PRINT);
    return file_put_contents($dataFile, $jsonData) !== false;
}

/**
 * Function to handle delete action
 */
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteIndex = (int)$_GET['delete'];
    $vocabulary = getVocabulary();
    
    if (isset($vocabulary[$deleteIndex])) {
        array_splice($vocabulary, $deleteIndex, 1);
        saveVocabulary($vocabulary);
    }
    
    header('Location: index.php');
    exit;
}