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

// Get IELTS readiness data
$ieltsReadiness = getIELTSReadiness($userId);

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

// Calculate word counts by IELTS band
$bandCounts = [
    'band_5' => 0,
    'band_6' => 0,
    'band_7' => 0,
    'band_8' => 0
];

foreach ($vocabulary as $item) {
    if (isset($item['user_id']) && $item['user_id'] == $userId && isset($item['ielts_band'])) {
        $band = (int)$item['ielts_band'];
        if ($band >= 8) {
            $bandCounts['band_8']++;
        } elseif ($band >= 7) {
            $bandCounts['band_7']++;
        } elseif ($band >= 6) {
            $bandCounts['band_6']++;
        } else {
            $bandCounts['band_5']++;
        }
    }
}

// Calculate category distribution
$categoryStats = [];
foreach ($vocabulary as $item) {
    if (isset($item['user_id']) && $item['user_id'] == $userId && isset($item['category'])) {
        foreach ($item['category'] as $category) {
            if (!isset($categoryStats[$category])) {
                $categoryStats[$category] = [
                    'total' => 0,
                    'mastered' => 0
                ];
            }
            $categoryStats[$category]['total']++;
            
            if (isset($item['srs_level']) && $item['srs_level'] >= 4) {
                $categoryStats[$category]['mastered']++;
            }
        }
    }
}

// Sort categories by total word count
arsort($categoryStats);

// Get accuracy by study mode
$accuracy = [];
if (isset($stats['accuracy'])) {
    foreach ($stats['accuracy'] as $mode => $modeStats) {
        $total = $modeStats['correct'] + $modeStats['incorrect'];
        if ($total > 0) {
            $accuracy[$mode] = ($modeStats['correct'] / $total) * 100;
        } else {
            $accuracy[$mode] = 0;
        }
    }
}

// Prepare stats data for charts
$statsData = [
    'srs_levels' => $srsLevels,
    'band_counts' => $bandCounts,
    'accuracy' => $accuracy,
    'category_stats' => $categoryStats,
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
                    <h3>IELTS Learning Statistics</h3>
                </div>
                <div class="card-body">
                    <!-- IELTS Readiness Banner -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <h4 class="mb-1">IELTS Vocabulary Readiness: <?php echo $ieltsReadiness['overall']; ?>%</h4>
                                            <div class="progress bg-light bg-opacity-25">
                                                <div class="progress-bar bg-white" role="progressbar" style="width: <?php echo $ieltsReadiness['overall']; ?>%" aria-valuenow="<?php echo $ieltsReadiness['overall']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="mt-2 small">
                                                <span class="me-3"><i class="fas fa-check-circle"></i> <?php echo $ieltsReadiness['mastered']; ?> mastered</span>
                                                <span class="me-3"><i class="fas fa-graduation-cap"></i> <?php echo $ieltsReadiness['learning']; ?> learning</span>
                                                <span><i class="fas fa-hourglass-half"></i> <?php echo $ieltsReadiness['due']; ?> due for review</span>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="row text-center">
                                                <div class="col-md-3">
                                                    <h5>Band 5</h5>
                                                    <h3><?php echo $ieltsReadiness['by_band'][5]; ?></h3>
                                                    <div class="progress bg-light bg-opacity-25">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo min(100, ($ieltsReadiness['by_band'][5] / 10)); ?>%"></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5>Band 6</h5>
                                                    <h3><?php echo $ieltsReadiness['by_band'][6]; ?></h3>
                                                    <div class="progress bg-light bg-opacity-25">
                                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo min(100, ($ieltsReadiness['by_band'][6] / 15)); ?>%"></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5>Band 7</h5>
                                                    <h3><?php echo $ieltsReadiness['by_band'][7]; ?></h3>
                                                    <div class="progress bg-light bg-opacity-25">
                                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo min(100, ($ieltsReadiness['by_band'][7] / 20)); ?>%"></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <h5>Band 8+</h5>
                                                    <h3><?php echo $ieltsReadiness['by_band'][8]; ?></h3>
                                                    <div class="progress bg-light bg-opacity-25">
                                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo min(100, ($ieltsReadiness['by_band'][8] / 10)); ?>%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Learning Streak -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <h5>Current Streak</h5>
                                            <h3><?php echo $user['streak']['current']; ?> days</h3>
                                        </div>
                                        <div class="col-md-3">
                                            <h5>Longest Streak</h5>
                                            <h3><?php echo $user['streak']['max']; ?> days</h3>
                                        </div>
                                        <div class="col-md-3">
                                            <h5>Last Study Session</h5>
                                            <h3><?php echo date('F j, Y', strtotime($user['streak']['last_login'])); ?></h3>
                                        </div>
                                        <div class="col-md-3">
                                            <h5>Target IELTS Band</h5>
                                            <h3><?php echo $user['settings']['target_ielts_band'] ?? '7.0'; ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- IELTS Topic Distribution -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5>IELTS Topic Distribution</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Topic</th>
                                                    <th>Words</th>
                                                    <th>Mastered</th>
                                                    <th>Progress</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (array_slice($categoryStats, 0, 8) as $category => $counts): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($category); ?></td>
                                                    <td><?php echo $counts['total']; ?></td>
                                                    <td><?php echo $counts['mastered']; ?></td>
                                                    <td class="w-25">
                                                        <div class="progress">
                                                            <div class="progress-bar bg-success" role="progressbar" 
                                                                style="width: <?php echo ($counts['total'] > 0) ? ($counts['mastered'] / $counts['total']) * 100 : 0; ?>%" 
                                                                aria-valuenow="<?php echo $counts['mastered']; ?>" 
                                                                aria-valuemin="0" 
                                                                aria-valuemax="<?php echo $counts['total']; ?>">
                                                                <?php echo ($counts['total'] > 0) ? round(($counts['mastered'] / $counts['total']) * 100) : 0; ?>%
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Summary Stats -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card text-white bg-primary h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Total Vocabulary</h5>
                                    <h2><?php echo count($vocabulary); ?></h2>
                                    <div class="small mt-2">
                                        <span class="badge bg-light text-dark">Band 5: <?php echo $bandCounts['band_5']; ?></span>
                                        <span class="badge bg-light text-dark">Band 6: <?php echo $bandCounts['band_6']; ?></span>
                                        <span class="badge bg-light text-dark">Band 7: <?php echo $bandCounts['band_7']; ?></span>
                                        <span class="badge bg-light text-dark">Band 8+: <?php echo $bandCounts['band_8']; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-success h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Mastered Words</h5>
                                    <h2><?php echo $srsLevels['level_4'] + $srsLevels['level_5']; ?></h2>
                                    <div class="small mt-2">
                                        <div class="progress bg-light bg-opacity-25">
                                            <div class="progress-bar bg-white" role="progressbar" 
                                                style="width: <?php echo count($vocabulary) > 0 ? (($srsLevels['level_4'] + $srsLevels['level_5']) / count($vocabulary)) * 100 : 0; ?>%" 
                                                aria-valuenow="<?php echo $srsLevels['level_4'] + $srsLevels['level_5']; ?>" 
                                                aria-valuemin="0" 
                                                aria-valuemax="<?php echo count($vocabulary); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-warning h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Accuracy Rate</h5>
                                    <h2><?php echo number_format($stats['quiz_accuracy'] ?? 0, 1); ?>%</h2>
                                    <div class="small mt-2 text-dark">
                                        <?php foreach ($accuracy as $mode => $rate): ?>
                                            <span class="badge bg-light"><?php echo ucfirst($mode); ?>: <?php echo number_format($rate, 1); ?>%</span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-info h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Due for Review</h5>
                                    <h2><?php echo count(getDueWordsForReview(100)); ?></h2>
                                    <div class="small mt-2">
                                        <a href="playground.php?use_srs=1" class="btn btn-sm btn-light">
                                            <i class="fas fa-play"></i> Start Review
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Charts Row -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5>Daily Learning Activity</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="activity-chart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5>IELTS Band Distribution</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="band-chart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- SRS and Accuracy Charts -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5>Study Progress by SRS Level</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="progress-chart" height="250"></canvas>
                                </div>
                                <div class="card-footer">
                                    <div class="row text-center small">
                                        <div class="col-md-2">
                                            <span class="badge bg-danger">New</span>
                                            <div><?php echo $srsLevels['level_0']; ?></div>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="badge bg-warning text-dark">Learning 1</span>
                                            <div><?php echo $srsLevels['level_1']; ?></div>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="badge bg-warning text-dark">Learning 2</span>
                                            <div><?php echo $srsLevels['level_2']; ?></div>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="badge bg-info text-dark">Review 1</span>
                                            <div><?php echo $srsLevels['level_3']; ?></div>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="badge bg-primary">Review 2</span>
                                            <div><?php echo $srsLevels['level_4']; ?></div>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="badge bg-success">Mastered</span>
                                            <div><?php echo $srsLevels['level_5']; ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5>Accuracy by Study Mode</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="accuracy-chart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Study Recommendations -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5>Study Recommendations</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php
                                        // Determine weakest topic
                                        $minMasteryRate = 100;
                                        $weakestTopic = '';
                                        
                                        foreach ($categoryStats as $category => $counts) {
                                            if ($counts['total'] >= 10) {  // Only consider topics with enough words
                                                $masteryRate = ($counts['total'] > 0) ? ($counts['mastered'] / $counts['total']) * 100 : 0;
                                                if ($masteryRate < $minMasteryRate) {
                                                    $minMasteryRate = $masteryRate;
                                                    $weakestTopic = $category;
                                                }
                                            }
                                        }
                                        
                                        // Determine rarest IELTS band level
                                        $minBandCount = PHP_INT_MAX;
                                        $rarestBand = '';
                                        
                                        foreach ($bandCounts as $band => $count) {
                                            $bandNumber = (int)substr($band, 5);
                                            $targetCount = ($bandNumber == 5) ? 1000 : 
                                                          (($bandNumber == 6) ? 1500 : 
                                                          (($bandNumber == 7) ? 2000 : 1000));
                                            
                                            $percentage = ($targetCount > 0) ? ($count / $targetCount) * 100 : 0;
                                            if ($percentage < $minBandCount) {
                                                $minBandCount = $percentage;
                                                $rarestBand = $bandNumber;
                                            }
                                        }
                                        
                                        // Determine due for review count
                                        $dueForReview = count(getDueWordsForReview(100));
                                        ?>
                                        
                                        <div class="col-md-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title">Improve Your Weakest Topic</h5>
                                                    <?php if (!empty($weakestTopic)): ?>
                                                        <p>Your weakest IELTS topic is <strong><?php echo htmlspecialchars($weakestTopic); ?></strong> with only <?php echo round($minMasteryRate); ?>% mastery.</p>
                                                        <a href="playground.php?category=<?php echo urlencode($weakestTopic); ?>" class="btn btn-primary">
                                                            <i class="fas fa-play"></i> Practice <?php echo htmlspecialchars($weakestTopic); ?> Vocabulary
                                                        </a>
                                                    <?php else: ?>
                                                        <p>You need to study more vocabulary across different IELTS topics.</p>
                                                        <a href="add.php" class="btn btn-primary">
                                                            <i class="fas fa-plus"></i> Add New Vocabulary
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title">Expand Your Band <?php echo $rarestBand; ?> Vocabulary</h5>
                                                    <p>You need more Band <?php echo $rarestBand; ?> vocabulary to reach your target IELTS score.</p>
                                                    <a href="playground.php?ielts_band=<?php echo $rarestBand; ?>" class="btn btn-primary">
                                                        <i class="fas fa-graduation-cap"></i> Study Band <?php echo $rarestBand; ?> Words
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <h5 class="card-title">Review Due Words</h5>
                                                    <?php if ($dueForReview > 0): ?>
                                                        <p>You have <strong><?php echo $dueForReview; ?></strong> words due for review today.</p>
                                                        <a href="playground.php?use_srs=1" class="btn btn-primary">
                                                            <i class="fas fa-sync"></i> Start SRS Review
                                                        </a>
                                                    <?php else: ?>
                                                        <p>Great job! You don't have any words due for review today.</p>
                                                        <a href="playground.php?mode=writing" class="btn btn-primary">
                                                            <i class="fas fa-pen"></i> Practice Writing
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get stats data from the element
    const statsDataEl = document.getElementById('stats-data');
    if (!statsDataEl) return;
    
    const statsData = JSON.parse(statsDataEl.getAttribute('data-stats'));
    
    // Initialize charts
    initAccuracyChart(statsData);
    initActivityChart(statsData);
    initProgressChart(statsData);
    initBandChart(statsData);
});

function initAccuracyChart(statsData) {
    const ctx = document.getElementById('accuracy-chart').getContext('2d');
    
    // Prepare data
    const modes = [];
    const accuracyData = [];
    
    for (let mode in statsData.accuracy) {
        modes.push(mode.charAt(0).toUpperCase() + mode.slice(1));
        accuracyData.push(statsData.accuracy[mode]);
    }
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: modes,
            datasets: [{
                label: 'Accuracy %',
                data: accuracyData,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(255, 99, 132, 0.7)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Percentage'
                    }
                }
            },
            plugins: {
                title: {
                    display: false
                },
                legend: {
                    display: false
                }
            }
        }
    });
}

function initActivityChart(statsData) {
    const ctx = document.getElementById('activity-chart').getContext('2d');
    
    // Prepare data
    const dailyActivity = statsData.daily_activity || [];
    const sortedActivity = [...dailyActivity].sort((a, b) => new Date(a.date) - new Date(b.date));
    
    // Limit to last 14 days
    const recentActivity = sortedActivity.slice(-14);
    
    const labels = recentActivity.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
    
    const data = recentActivity.map(item => item.words_studied);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Words Studied',
                data: data,
                fill: true,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                tension: 0.2,
                pointBackgroundColor: 'rgba(75, 192, 192, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Words'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                }
            },
            plugins: {
                title: {
                    display: false
                }
            }
        }
    });
}

function initProgressChart(statsData) {
    const ctx = document.getElementById('progress-chart').getContext('2d');
    
    // Prepare data for SRS levels
    const srsLevels = statsData.srs_levels || {
        'level_0': 0,
        'level_1': 0,
        'level_2': 0,
        'level_3': 0,
        'level_4': 0,
        'level_5': 0
    };
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: [
                'New', 'Learning 1', 'Learning 2', 
                'Review 1', 'Review 2', 'Mastered'
            ],
            datasets: [{
                data: [
                    srsLevels.level_0 || 0,
                    srsLevels.level_1 || 0,
                    srsLevels.level_2 || 0,
                    srsLevels.level_3 || 0,
                    srsLevels.level_4 || 0,
                    srsLevels.level_5 || 0
                ],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(255, 205, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(153, 102, 255, 0.7)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 205, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: false
                },
                legend: {
                    position: 'right'
                }
            }
        }
    });
}

function initBandChart(statsData) {
    const ctx = document.getElementById('band-chart').getContext('2d');
    
    // Prepare data for IELTS bands
    const bandCounts = statsData.band_counts || {
        'band_5': 0,
        'band_6': 0,
        'band_7': 0,
        'band_8': 0
    };
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Band 5', 'Band 6', 'Band 7', 'Band 8+'],
            datasets: [{
                label: 'Number of Words',
                data: [
                    bandCounts.band_5 || 0,
                    bandCounts.band_6 || 0,
                    bandCounts.band_7 || 0,
                    bandCounts.band_8 || 0
                ],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.7)',   // green for band 5
                    'rgba(23, 162, 184, 0.7)',  // info for band 6
                    'rgba(255, 193, 7, 0.7)',   // warning for band 7
                    'rgba(220, 53, 69, 0.7)'    // danger for band 8+
                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)',
                    'rgba(23, 162, 184, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(220, 53, 69, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Words'
                    }
                }
            },
            plugins: {
                title: {
                    display: false
                },
                legend: {
                    display: false
                }
            }
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>