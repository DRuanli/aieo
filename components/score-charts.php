<?php
// Prepare data for charts
$dates = [];
$listeningScores = [];
$readingScores = [];
$writingScores = [];
$speakingScores = [];
$overallScores = [];

if (!empty($scores)) {
    foreach ($scores as $score) {
        $dates[] = date('Y-m-d', strtotime($score['date']));
        $listeningScores[] = $score['listening'];
        $readingScores[] = $score['reading'];
        $writingScores[] = $score['writing'];
        $speakingScores[] = $score['speaking'];
        $overallScores[] = $score['overall'];
    }
}
?>

<div class="card">
    <div class="card-header">
        <h5>Score Progression</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($scores)): ?>
            <ul class="nav nav-tabs" id="chartTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="line-tab" data-bs-toggle="tab" data-bs-target="#line-chart" type="button" role="tab" aria-controls="line-chart" aria-selected="true">Line Chart</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="radar-tab" data-bs-toggle="tab" data-bs-target="#radar-chart" type="button" role="tab" aria-controls="radar-chart" aria-selected="false">Radar Chart</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="bar-tab" data-bs-toggle="tab" data-bs-target="#bar-chart" type="button" role="tab" aria-controls="bar-chart" aria-selected="false">Bar Chart</button>
                </li>
            </ul>
            <div class="tab-content p-3" id="chartTabsContent">
                <div class="tab-pane fade show active" id="line-chart" role="tabpanel" aria-labelledby="line-tab">
                    <canvas id="scoreLineChart" width="400" height="250"></canvas>
                </div>
                <div class="tab-pane fade" id="radar-chart" role="tabpanel" aria-labelledby="radar-tab">
                    <canvas id="scoreRadarChart" width="400" height="300"></canvas>
                </div>
                <div class="tab-pane fade" id="bar-chart" role="tabpanel" aria-labelledby="bar-tab">
                    <canvas id="scoreBarChart" width="400" height="250"></canvas>
                </div>
            </div>
        <?php else: ?>
            <p>No scores recorded yet. Use the form to add your first score.</p>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($scores)): ?>
    // Data for charts
    const dates = <?php echo json_encode($dates); ?>;
    const listeningScores = <?php echo json_encode($listeningScores); ?>;
    const readingScores = <?php echo json_encode($readingScores); ?>;
    const writingScores = <?php echo json_encode($writingScores); ?>;
    const speakingScores = <?php echo json_encode($speakingScores); ?>;
    const overallScores = <?php echo json_encode($overallScores); ?>;
    
    // Latest scores for radar chart
    const latestListening = listeningScores[listeningScores.length - 1];
    const latestReading = readingScores[readingScores.length - 1];
    const latestWriting = writingScores[writingScores.length - 1];
    const latestSpeaking = speakingScores[speakingScores.length - 1];
    
    // Line Chart
    const lineCtx = document.getElementById('scoreLineChart').getContext('2d');
    const lineChart = new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [
                {
                    label: 'Listening',
                    data: listeningScores,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.2
                },
                {
                    label: 'Reading',
                    data: readingScores,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.2
                },
                {
                    label: 'Writing',
                    data: writingScores,
                    borderColor: 'rgba(255, 206, 86, 1)',
                    backgroundColor: 'rgba(255, 206, 86, 0.2)',
                    tension: 0.2
                },
                {
                    label: 'Speaking',
                    data: speakingScores,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.2
                },
                {
                    label: 'Overall',
                    data: overallScores,
                    borderColor: 'rgba(153, 102, 255, 1)',
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    tension: 0.2
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 9,
                    title: {
                        display: true,
                        text: 'Score'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                }
            }
        }
    });
    
    // Radar Chart (latest scores)
    const radarCtx = document.getElementById('scoreRadarChart').getContext('2d');
    const radarChart = new Chart(radarCtx, {
        type: 'radar',
        data: {
            labels: ['Listening', 'Reading', 'Writing', 'Speaking'],
            datasets: [
                {
                    label: 'Latest Scores',
                    data: [latestListening, latestReading, latestWriting, latestSpeaking],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    pointBackgroundColor: 'rgba(54, 162, 235, 1)'
                }
            ]
        },
        options: {
            scales: {
                r: {
                    angleLines: {
                        display: true
                    },
                    suggestedMin: 0,
                    suggestedMax: 9
                }
            }
        }
    });
    
    // Bar Chart (all skills average)
    const barCtx = document.getElementById('scoreBarChart').getContext('2d');
    const avgListening = listeningScores.reduce((a, b) => a + b, 0) / listeningScores.length;
    const avgReading = readingScores.reduce((a, b) => a + b, 0) / readingScores.length;
    const avgWriting = writingScores.reduce((a, b) => a + b, 0) / writingScores.length;
    const avgSpeaking = speakingScores.reduce((a, b) => a + b, 0) / speakingScores.length;
    const avgOverall = overallScores.reduce((a, b) => a + b, 0) / overallScores.length;
    
    const barChart = new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: ['Listening', 'Reading', 'Writing', 'Speaking', 'Overall'],
            datasets: [
                {
                    label: 'Average Scores',
                    data: [avgListening, avgReading, avgWriting, avgSpeaking, avgOverall],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 9,
                    title: {
                        display: true,
                        text: 'Score'
                    }
                }
            }
        }
    });
    <?php endif; ?>
});
</script>