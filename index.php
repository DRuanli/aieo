<?php
// Main entry point and dashboard
require_once 'includes/functions.php';
require_once 'includes/data-handler.php';

// Get the latest scores
$scores = getScores();
$latestScores = !empty($scores) ? end($scores) : null;

// Get works count by category
$writingCount = count(glob("data/works/writing/*.json"));
$speakingCount = count(glob("data/works/speaking/*.json"));
$readingCount = count(glob("data/works/reading/*.json"));
$listeningCount = count(glob("data/works/listening/*.json"));

// Calculate average scores if scores exist
$averageScores = [];
if (!empty($scores)) {
    $totalListening = $totalReading = $totalWriting = $totalSpeaking = $totalOverall = 0;
    foreach ($scores as $score) {
        $totalListening += $score['listening'];
        $totalReading += $score['reading'];
        $totalWriting += $score['writing'];
        $totalSpeaking += $score['speaking'];
        $totalOverall += $score['overall'];
    }
    $count = count($scores);
    $averageScores = [
        'listening' => round($totalListening / $count, 1),
        'reading' => round($totalReading / $count, 1),
        'writing' => round($totalWriting / $count, 1),
        'speaking' => round($totalSpeaking / $count, 1),
        'overall' => round($totalOverall / $count, 1)
    ];
}

// Page title
$pageTitle = 'IELTS Study Tracker - Dashboard';

// Include header
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1>IELTS Study Tracker</h1>
            <p class="lead">Track your IELTS progress, store your work, and analyze your vocabulary.</p>
        </div>
    </div>

    <!-- Latest Scores Section -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Latest Scores</h5>
                </div>
                <div class="card-body">
                    <?php if ($latestScores): ?>
                        <div class="row">
                            <div class="col-6">
                                <p><strong>Date:</strong> <?php echo date('Y-m-d', strtotime($latestScores['date'])); ?></p>
                                <p><strong>Listening:</strong> <?php echo $latestScores['listening']; ?></p>
                                <p><strong>Reading:</strong> <?php echo $latestScores['reading']; ?></p>
                            </div>
                            <div class="col-6">
                                <p><strong>Writing:</strong> <?php echo $latestScores['writing']; ?></p>
                                <p><strong>Speaking:</strong> <?php echo $latestScores['speaking']; ?></p>
                                <p><strong>Overall:</strong> <?php echo $latestScores['overall']; ?></p>
                            </div>
                        </div>
                        <a href="scores.php" class="btn btn-primary mt-2">View All Scores</a>
                    <?php else: ?>
                        <p>No scores recorded yet.</p>
                        <a href="scores.php" class="btn btn-primary">Add Your First Score</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Average Scores</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($averageScores)): ?>
                        <div class="row">
                            <div class="col-6">
                                <p><strong>Listening:</strong> <?php echo $averageScores['listening']; ?></p>
                                <p><strong>Reading:</strong> <?php echo $averageScores['reading']; ?></p>
                            </div>
                            <div class="col-6">
                                <p><strong>Writing:</strong> <?php echo $averageScores['writing']; ?></p>
                                <p><strong>Speaking:</strong> <?php echo $averageScores['speaking']; ?></p>
                                <p><strong>Overall:</strong> <?php echo $averageScores['overall']; ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <p>No scores available yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Study Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h3><?php echo $writingCount; ?></h3>
                                    <p>Writing Tasks</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h3><?php echo $speakingCount; ?></h3>
                                    <p>Speaking Tasks</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h3><?php echo $readingCount; ?></h3>
                                    <p>Reading Tasks</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h3><?php echo $listeningCount; ?></h3>
                                    <p>Listening Tasks</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4 mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Track Scores</h5>
                    <p class="card-text">Record and visualize your IELTS scores.</p>
                    <a href="scores.php" class="btn btn-primary">Go to Scores</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Save Your Work</h5>
                    <p class="card-text">Store and organize your IELTS practice work.</p>
                    <a href="works.php" class="btn btn-primary">Manage Work</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Word Statistics</h5>
                    <p class="card-text">Analyze your vocabulary for each skill.</p>
                    <a href="statistics.php" class="btn btn-primary">View Statistics</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>