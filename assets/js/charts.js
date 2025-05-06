/**
 * Charts functionality for IELTS Study Tracker
 */

// Chart color palette
const chartColors = {
    listening: {
        border: 'rgba(255, 99, 132, 1)',
        background: 'rgba(255, 99, 132, 0.2)'
    },
    reading: {
        border: 'rgba(54, 162, 235, 1)',
        background: 'rgba(54, 162, 235, 0.2)'
    },
    writing: {
        border: 'rgba(255, 206, 86, 1)',
        background: 'rgba(255, 206, 86, 0.2)'
    },
    speaking: {
        border: 'rgba(75, 192, 192, 1)',
        background: 'rgba(75, 192, 192, 0.2)'
    },
    overall: {
        border: 'rgba(153, 102, 255, 1)',
        background: 'rgba(153, 102, 255, 0.2)'
    }
};

/**
 * Create a responsive chart that updates on window resize
 */
function createResponsiveChart(chartId, chartType, chartData, chartOptions) {
    // Make sure the canvas exists
    const chartCanvas = document.getElementById(chartId);
    if (!chartCanvas) return null;
    
    // Create the chart
    const chart = new Chart(chartCanvas, {
        type: chartType,
        data: chartData,
        options: {
            ...chartOptions,
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    // Make the chart responsive to container size changes
    const resizeChart = () => {
        chart.resize();
    };
    
    window.addEventListener('resize', resizeChart);
    
    return chart;
}

/**
 * Create a progress chart comparing target and current scores
 */
function createProgressChart(chartId, scores, targetScores) {
    const labels = ['Listening', 'Reading', 'Writing', 'Speaking', 'Overall'];
    const currentData = [
        scores.listening || 0,
        scores.reading || 0,
        scores.writing || 0,
        scores.speaking || 0,
        scores.overall || 0
    ];
    
    const targetData = [
        targetScores.listening || 9,
        targetScores.reading || 9,
        targetScores.writing || 9,
        targetScores.speaking || 9,
        targetScores.overall || 9
    ];
    
    return createResponsiveChart(chartId, 'bar', {
        labels: labels,
        datasets: [
            {
                label: 'Current Score',
                data: currentData,
                backgroundColor: [
                    chartColors.listening.background,
                    chartColors.reading.background,
                    chartColors.writing.background,
                    chartColors.speaking.background,
                    chartColors.overall.background
                ],
                borderColor: [
                    chartColors.listening.border,
                    chartColors.reading.border,
                    chartColors.writing.border,
                    chartColors.speaking.border,
                    chartColors.overall.border
                ],
                borderWidth: 1
            },
            {
                label: 'Target Score',
                data: targetData,
                backgroundColor: 'rgba(200, 200, 200, 0.3)',
                borderColor: 'rgba(200, 200, 200, 1)',
                borderWidth: 1,
                borderDash: [5, 5]
            }
        ]
    }, {
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
    });
}

/**
 * Create a radar chart for skills comparison
 */
function createSkillsRadarChart(chartId, scores) {
    return createResponsiveChart(chartId, 'radar', {
        labels: ['Listening', 'Reading', 'Writing', 'Speaking'],
        datasets: [
            {
                label: 'Current Scores',
                data: [
                    scores.listening || 0,
                    scores.reading || 0,
                    scores.writing || 0,
                    scores.speaking || 0
                ],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(54, 162, 235, 1)'
            }
        ]
    }, {
        scales: {
            r: {
                angleLines: {
                    display: true
                },
                suggestedMin: 0,
                suggestedMax: 9
            }
        }
    });
}

/**
 * Create a line chart for score progression over time
 */
function createScoreProgressionChart(chartId, dates, scores) {
    return createResponsiveChart(chartId, 'line', {
        labels: dates,
        datasets: [
            {
                label: 'Listening',
                data: scores.listening,
                borderColor: chartColors.listening.border,
                backgroundColor: chartColors.listening.background,
                tension: 0.2
            },
            {
                label: 'Reading',
                data: scores.reading,
                borderColor: chartColors.reading.border,
                backgroundColor: chartColors.reading.background,
                tension: 0.2
            },
            {
                label: 'Writing',
                data: scores.writing,
                borderColor: chartColors.writing.border,
                backgroundColor: chartColors.writing.background,
                tension: 0.2
            },
            {
                label: 'Speaking',
                data: scores.speaking,
                borderColor: chartColors.speaking.border,
                backgroundColor: chartColors.speaking.background,
                tension: 0.2
            },
            {
                label: 'Overall',
                data: scores.overall,
                borderColor: chartColors.overall.border,
                backgroundColor: chartColors.overall.background,
                tension: 0.2
            }
        ]
    }, {
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
    });
}