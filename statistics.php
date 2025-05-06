<?php
// Word statistics for each skill
require_once 'includes/functions.php';
require_once 'includes/data-handler.php';

// Get the skill type from the query string, default to 'writing'
$skill = isset($_GET['skill']) ? $_GET['skill'] : 'writing';
$validSkills = ['writing', 'speaking', 'reading', 'listening'];
if (!in_array($skill, $validSkills)) {
    $skill = 'writing';
}

// Get word statistics for the selected skill
$wordStats = getWordStatistics($skill);

// Sort word stats by frequency (descending)
if (!empty($wordStats)) {
    arsort($wordStats);
}

// Get top 50 words
$topWords = array_slice($wordStats, 0, 50, true);

// Prepare data for charts
$labels = array_keys($topWords);
$data = array_values($topWords);

// Categorize words by frequency
$highFrequency = [];
$mediumFrequency = [];
$lowFrequency = [];

foreach ($wordStats as $word => $count) {
    if ($count >= 10) {
        $highFrequency[$word] = $count;
    } elseif ($count >= 5) {
        $mediumFrequency[$word] = $count;
    } else {
        $lowFrequency[$word] = $count;
    }
}

// Total word count
$totalWords = array_sum($wordStats);
$uniqueWords = count($wordStats);

// Page title
$pageTitle = 'IELTS Study Tracker - Word Statistics';

// Include header
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1>Word Statistics</h1>
            <p class="lead">Analyze your vocabulary usage for each IELTS skill</p>
        </div>
    </div>

    <!-- Skill Type Tabs -->
    <div class="row mt-4">
        <div class="col-12">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo $skill === 'writing' ? 'active' : ''; ?>" href="?skill=writing">Writing</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $skill === 'speaking' ? 'active' : ''; ?>" href="?skill=speaking">Speaking</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $skill === 'reading' ? 'active' : ''; ?>" href="?skill=reading">Reading</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $skill === 'listening' ? 'active' : ''; ?>" href="?skill=listening">Listening</a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Word Usage Summary</h5>
                </div>
                <div class="card-body">
                    <p><strong>Total Words:</strong> <?php echo $totalWords; ?></p>
                    <p><strong>Unique Words:</strong> <?php echo $uniqueWords; ?></p>
                    <p><strong>High Frequency Words (10+ uses):</strong> <?php echo count($highFrequency); ?></p>
                    <p><strong>Medium Frequency Words (5-9 uses):</strong> <?php echo count($mediumFrequency); ?></p>
                    <p><strong>Low Frequency Words (1-4 uses):</strong> <?php echo count($lowFrequency); ?></p>
                    <p><strong>Vocabulary Variety:</strong> <?php echo $totalWords > 0 ? round(($uniqueWords / $totalWords) * 100, 2) : 0; ?>%</p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <!-- Word Cloud Visualization -->
            <?php include 'components/word-cloud.php'; ?>
        </div>
    </div>

    <!-- Top Words Bar Chart -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Top 20 Most Frequent Words</h5>
                </div>
                <div class="card-body">
                    <canvas id="topWordsChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Word Frequency Table -->
    <div class="row mt-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Word Frequency Table</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($wordStats)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped" id="wordTable">
                                <thead>
                                    <tr>
                                        <th>Word</th>
                                        <th>Frequency</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($wordStats as $word => $count): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($word); ?></td>
                                            <td><?php echo $count; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No word statistics available for <?php echo $skill; ?> yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Chart for top words
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('topWordsChart').getContext('2d');
    
    // Get top 20 words for the chart
    var labels = <?php echo json_encode(array_slice($labels, 0, 20)); ?>;
    var data = <?php echo json_encode(array_slice($data, 0, 20)); ?>;
    
    var chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Word Frequency',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Frequency'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Words'
                    }
                }
            }
        }
    });
    
    // Initialize DataTable for word frequency table
    $(document).ready(function() {
        $('#wordTable').DataTable({
            "order": [[1, "desc"]],
            "pageLength": 25
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>