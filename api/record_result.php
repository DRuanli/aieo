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
    !in_array($input['mode'], ['flashcard', 'quiz', 'matching'])
) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Record the review
$success = recordReviewActivity($input['word_id'], $input['result'], $input['mode']);

// Return response
if ($success) {
    echo json_encode(['success' => true, 'message' => 'Result recorded successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to record result']);
}