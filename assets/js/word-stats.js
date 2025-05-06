/**
 * Word statistics functionality for IELTS Study Tracker
 */

/**
 * Generate a color based on word frequency
 * @param {number} frequency Word frequency
 * @param {number} maxFrequency Maximum frequency in dataset
 * @returns {string} CSS color string
 */
function getColorForFrequency(frequency, maxFrequency) {
    // Normalize frequency to a value between 0 and 1
    const normalizedFreq = Math.min(frequency / (maxFrequency * 0.7), 1);
    
    // Generate color gradients from blue (low) to red (high)
    const r = Math.floor(normalizedFreq * 255);
    const g = Math.floor(100 - normalizedFreq * 50);
    const b = Math.floor(255 - normalizedFreq * 255);
    
    return `rgb(${r}, ${g}, ${b})`;
}

/**
 * Create a word cloud visualization
 * @param {string} containerId ID of the container element
 * @param {Object} wordStats Word statistics object
 */
function createWordCloud(containerId, wordStats) {
    // Make sure d3 and d3.layout.cloud are available
    if (!window.d3 || !window.d3.layout || !window.d3.layout.cloud) {
        console.error('D3 or d3.layout.cloud not available');
        return;
    }
    
    // Get container element
    const container = document.getElementById(containerId);
    if (!container) {
        console.error(`Container element with ID '${containerId}' not found`);
        return;
    }
    
    // Clear container
    container.innerHTML = '';
    
    // Convert word stats to array for d3
    const words = Object.entries(wordStats)
        .map(([text, value]) => ({
            text,
            size: Math.max(12, Math.min(80, value * 3)) // Scale font size between 12 and 80
        }))
        .slice(0, 100); // Limit to top 100 words for performance
    
    // Set up cloud layout
    const width = container.offsetWidth;
    const height = container.offsetHeight || 300;
    
    // Create the layout
    const layout = d3.layout.cloud()
        .size([width, height])
        .words(words)
        .padding(5)
        .rotate(() => ~~(Math.random() * 2) * 90)
        .fontSize(d => d.size)
        .on("end", draw);
    
    // Start the layout generation
    layout.start();
    
    // Draw the word cloud
    function draw(words) {
        // Find max frequency for color scaling
        const maxFrequency = Math.max(...Object.values(wordStats));
        
        // Create SVG
        const svg = d3.select(container).append("svg")
            .attr("width", layout.size()[0])
            .attr("height", layout.size()[1])
            .append("g")
            .attr("transform", `translate(${layout.size()[0] / 2},${layout.size()[1] / 2})`);
        
        // Add words
        svg.selectAll("text")
            .data(words)
            .enter().append("text")
            .style("font-size", d => `${d.size}px`)
            .style("font-family", "Impact")
            .style("fill", d => {
                const frequency = wordStats[d.text];
                return getColorForFrequency(frequency, maxFrequency);
            })
            .attr("text-anchor", "middle")
            .attr("transform", d => `translate(${d.x},${d.y})rotate(${d.rotate})`)
            .text(d => d.text)
            .on("click", function(d) {
                // Create tooltip with word info
                alert(`Word: ${d.text}\nFrequency: ${wordStats[d.text]}`);
            });
    }
}

/**
 * Analyze text for word statistics
 * @param {string} text Text to analyze
 * @returns {Object} Word statistics object
 */
function analyzeText(text) {
    // Convert to lowercase
    text = text.toLowerCase();
    
    // Remove punctuation and numbers
    text = text.replace(/[^\p{L}\s]/gu, ' ');
    
    // Split into words
    const words = text.split(/\s+/).filter(word => word.length > 0);
    
    // Common English stopwords to exclude
    const stopwords = [
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
        'could', 'should', 'ought'
    ];
    
    // Count words that are not stopwords and are at least 2 characters
    const wordStats = {};
    for (const word of words) {
        if (word.length >= 2 && !stopwords.includes(word)) {
            wordStats[word] = (wordStats[word] || 0) + 1;
        }
    }
    
    return wordStats;
}

/**
 * Create word frequency chart
 * @param {string} chartId ID of the chart canvas element
 * @param {Object} wordStats Word statistics object
 * @param {number} limit Maximum number of words to display
 */
function createWordFrequencyChart(chartId, wordStats, limit = 20) {
    // Sort words by frequency
    const sortedWords = Object.entries(wordStats)
        .sort((a, b) => b[1] - a[1])
        .slice(0, limit);
    
    // Extract labels and data
    const labels = sortedWords.map(entry => entry[0]);
    const data = sortedWords.map(entry => entry[1]);
    
    // Create chart
    const ctx = document.getElementById(chartId).getContext('2d');
    new Chart(ctx, {
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
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: `Top ${limit} Most Frequent Words`
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Frequency'
                    }
                }
            }
        }
    });
}

// Initialize word cloud if container exists
document.addEventListener('DOMContentLoaded', function() {
    const wordCloudContainer = document.getElementById('wordCloudContainer');
    const wordStatsElement = document.getElementById('wordStats');
    
    if (wordCloudContainer && wordStatsElement) {
        try {
            // Get word stats from data element
            const wordStats = JSON.parse(wordStatsElement.textContent);
            createWordCloud('wordCloudContainer', wordStats);
        } catch (error) {
            console.error('Error initializing word cloud:', error);
        }
    }
});