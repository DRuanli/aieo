<?php
require_once 'includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user data
$userId = $_SESSION['user_id'];
$user = getUserById($userId);

// Get user statistics
$stats = getUserStatistics($userId);

// Calculate SRS levels distribution
$vocabulary = getVocabulary()['data'];
$srsLevels = [
    'level_0' => 0,
    'level_1' => 0,
    'level_2' => 0,
    'level_3' => 0,
    'level_4' => 0,
    'level_5' => 0
];

foreach ($vocabulary as $item) {
    if (isset($item['user_id']) && $item['user_id'] == $userId) {
        $level = isset($item['srs_level']) ? $item['srs_level'] : 0;
        $srsLevels['level_' . $level]++;
    }
}

// Calculate accuracy by mode
$accuracy = [];
if (isset($stats['total_reviews']) && $stats['total_reviews'] > 0) {
    $accuracy['quiz'] = [
        'correct' => $stats['correct_reviews'] ?? 0,
        'incorrect' => $stats['total_reviews'] - ($stats['correct_reviews'] ?? 0)
    ];
}

// Add SRS levels and accuracy to stats
$statsData = [
    'srs_levels' => $srsLevels,
    'accuracy' => $accuracy,
    'daily_activity' => $stats['daily_activity'] ?? [],
    'words_learned' => $stats['words_learned'] ?? 0,
    'quiz_accuracy' => $stats['quiz_accuracy'] ?? 0
];

include 'includes/header.php';
?>

<div class="container mt-4" id="stats-dashboard">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3>Learning Statistics</h3>
                </div>
                <div class="card-body">
                    <!-- Learning Streak -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <h5>Current Streak</h5>
                                            <h3><?php echo $user['streak']['current']; ?> days</h3>
                                        </div>
                                        <div class="col-md-4">
                                            <h5>Longest Streak</h5>
                                            <h3><?php echo $user['streak']['max']; ?> days</h3>
                                        </div>
                                        <div class="col-md-4">
                                            <h5>Last Study Session</h5>
                                            <h3><?php echo date('F j, Y', strtotime($user['streak']['last_login'])); ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Summary Stats -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card text-white bg-primary">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Total Words</h5>
                                    <h2><?php echo count($vocabulary); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-success">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Mastered Words</h5>
                                    <h2><?php echo $srsLevels['level_5']; ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-warning">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Quiz Accuracy</h5>
                                    <h2><?php echo number_format($stats['quiz_accuracy'] ?? 0, 1); ?>%</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-info">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Due for Review</h5>
                                    <h2><?php echo count(getDueWordsForReview(100)); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Charts Row -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5>Daily Learning Activity</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="activity-chart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5>Study Progress</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="progress-chart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Accuracy Chart Row -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5>Accuracy by Study Mode</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="accuracy-chart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pass stats data to JavaScript -->
<div id="stats-data" data-stats='<?php echo json_encode($statsData); ?>'></div>

<!-- Add Chart.js before including footer -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>

<?php include 'includes/footer.php'; ?>