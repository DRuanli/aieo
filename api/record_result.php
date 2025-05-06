<?php
require_once '../includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Return JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Expect JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate request
if (!$input || 
    !isset($input['word_id']) || 
    !isset($input['result']) || 
    !isset($input['mode']) ||
    !in_array($input['result'], ['correct', 'incorrect']) ||
    !in_array($input['mode'], ['flashcard', 'quiz', 'matching', 'collocations', 'writing'])
) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Get additional IELTS-specific data
$word = getVocabularyById($input['word_id']);
$ielts_band = $word['ielts_band'] ?? null;
$category = !empty($word['category']) ? $word['category'][0] : null;

// Record the review with IELTS metadata
$success = recordReviewActivity(
    $input['word_id'], 
    $input['result'], 
    $input['mode'], 
    [
        'ielts_band' => $ielts_band,
        'category' => $category
    ]
);

// Return response
if ($success) {
    // Get user's target IELTS band
    $user = getUserById($_SESSION['user_id']);
    $targetBand = $user['settings']['target_ielts_band'] ?? 7.0;
    
    // Check if this was a significant achievement
    $message = '';
    $achievement = false;
    
    // If it was a correct answer for a word at or above the target band
    if ($input['result'] === 'correct' && $ielts_band !== null && $ielts_band >= $targetBand) {
        $achievement = true;
        $message = "Great job! You're mastering vocabulary for Band $ielts_band.";
    }
    
    // Get current SRS level after update
    $updatedWord = getVocabularyById($input['word_id']);
    $srsLevel = $updatedWord['srs_level'] ?? 0;
    
    // If the word just reached mastery level (4 or 5)
    if ($srsLevel >= 4 && ($updatedWord['srs_level'] > ($word['srs_level'] ?? 0))) {
        $achievement = true;
        $message = "Congratulations! You've mastered \"" . $updatedWord['english'] . "\".";
    }
    
    // Calculate next review date
    $nextReview = isset($updatedWord['next_review']) ? 
        date('M j, Y', strtotime($updatedWord['next_review'])) : 
        'soon';
    
    echo json_encode([
        'success' => true, 
        'message' => 'Result recorded successfully', 
        'achievement' => $achievement,
        'achievement_message' => $message,
        'next_review' => $nextReview,
        'srs_level' => $srsLevel
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to record result']);
}