<?php
/**
 * Helper functions for IELTS Study Tracker
 */

/**
 * Ensure all required data directories exist
 */
function ensureDirectoriesExist() {
    $directories = [
        'data',
        'data/works',
        'data/works/writing',
        'data/works/speaking',
        'data/works/reading',
        'data/works/listening',
        'data/statistics'
    ];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}

/**
 * Generate a unique ID
 * 
 * @return string Unique ID
 */
function generateUniqueId() {
    return uniqid() . '_' . time();
}

/**
 * Process text to extract word statistics
 * 
 * @param string $text The text to process
 * @param string $skill The skill category (writing, speaking, reading, listening)
 * @return void
 */
function processWordStatistics($text, $skill) {
    // Convert to lowercase
    $text = strtolower($text);
    
    // Remove punctuation and numbers
    $text = preg_replace('/[^\p{L}\s]/u', ' ', $text);
    
    // Split into words
    $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    
    // Load existing statistics
    $statsFile = "data/statistics/{$skill}-words.json";
    $stats = [];
    
    if (file_exists($statsFile)) {
        $stats = json_decode(file_get_contents($statsFile), true) ?: [];
    }
    
    // Common English stopwords to exclude
    $stopwords = [
        'a', 'an', 'the', 'and', 'or', 'but', 'if', 'then', 'else', 'when',
        'at', 'from', 'by', 'for', 'with', 'about', 'against', 'between',
        'into', 'through', 'during', 'before', 'after', 'above', 'below',
        'to', 'of', 'in', 'on', 'off', 'over', 'under', 'again', 'further',
        'then', 'once', 'here', 'there', 'when', 'where', 'why', 'how',
        'all', 'any', 'both', 'each', 'few', 'more', 'most', 'other',
        'some', 'such', 'no', 'nor', 'not', 'only', 'own', 'same', 'so',
        'than', 'too', 'very', 's', 't', 'can', 'will', 'just', 'don',
        'should', 'now', 'i', 'me', 'my', 'myself', 'we', 'our', 'ours',
        'ourselves', 'you', 'your', 'yours', 'yourself', 'yourselves',
        'he', 'him', 'his', 'himself', 'she', 'her', 'hers', 'herself',
        'it', 'its', 'itself', 'they', 'them', 'their', 'theirs', 'themselves',
        'what', 'which', 'who', 'whom', 'this', 'that', 'these', 'those',
        'am', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have',
        'has', 'had', 'having', 'do', 'does', 'did', 'doing', 'would',
        'could', 'should', 'ought', 'i\'m', 'you\'re', 'he\'s', 'she\'s',
        'it\'s', 'we\'re', 'they\'re', 'i\'ve', 'you\'ve', 'we\'ve',
        'they\'ve', 'i\'d', 'you\'d', 'he\'d', 'she\'d', 'we\'d', 'they\'d',
        'i\'ll', 'you\'ll', 'he\'ll', 'she\'ll', 'we\'ll', 'they\'ll',
        'isn\'t', 'aren\'t', 'wasn\'t', 'weren\'t', 'hasn\'t', 'haven\'t',
        'hadn\'t', 'doesn\'t', 'don\'t', 'didn\'t', 'won\'t', 'wouldn\'t',
        'shan\'t', 'shouldn\'t', 'can\'t', 'cannot', 'couldn\'t', 'mustn\'t',
        'let\'s', 'that\'s', 'who\'s', 'what\'s', 'here\'s', 'there\'s',
        'when\'s', 'where\'s', 'why\'s', 'how\'s'
    ];
    
    // Count words that are not stopwords and are at least 2 characters
    foreach ($words as $word) {
        if (strlen($word) >= 2 && !in_array($word, $stopwords)) {
            if (isset($stats[$word])) {
                $stats[$word]++;
            } else {
                $stats[$word] = 1;
            }
        }
    }
    
    // Save statistics
    file_put_contents($statsFile, json_encode($stats, JSON_PRETTY_PRINT));
}

/**
 * Format a float score to display with consistent decimal places
 * 
 * @param float $score The score to format
 * @return string Formatted score
 */
function formatScore($score) {
    return number_format($score, 1);
}

// Ensure directories exist when including this file
ensureDirectoriesExist();