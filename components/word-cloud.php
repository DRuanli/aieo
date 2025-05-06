<div class="card">
    <div class="card-header">
        <h5>Word Cloud</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($wordStats)): ?>
            <div id="wordCloudContainer" style="height: 300px; position: relative;"></div>
        <?php else: ?>
            <p>No word statistics available for <?php echo $skill; ?> yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($wordStats)): ?>
<script src="https://cdn.jsdelivr.net/npm/d3@7"></script>
<script src="https://cdn.jsdelivr.net/npm/d3-cloud@1.2.5/build/d3.layout.cloud.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Word cloud data
    const words = <?php 
        // Limit to top 100 words for performance
        $cloudWords = array_slice($wordStats, 0, 100, true);
        $cloudData = [];
        foreach ($cloudWords as $word => $count) {
            $cloudData[] = [
                'text' => $word,
                'size' => min(max(12, $count * 3), 80) // Scale font size between 12 and 80
            ];
        }
        echo json_encode($cloudData);
    ?>;
    
    // Set up D3 cloud layout
    const width = document.getElementById('wordCloudContainer').offsetWidth;
    const height = 300;
    
    const layout = d3.layout.cloud()
        .size([width, height])
        .words(words)
        .padding(5)
        .rotate(() => ~~(Math.random() * 2) * 90)
        .fontSize(d => d.size)
        .on("end", draw);
    
    layout.start();
    
    function draw(words) {
        d3.select("#wordCloudContainer").append("svg")
            .attr("width", layout.size()[0])
            .attr("height", layout.size()[1])
            .append("g")
            .attr("transform", "translate(" + layout.size()[0] / 2 + "," + layout.size()[1] / 2 + ")")
            .selectAll("text")
            .data(words)
            .enter().append("text")
            .style("font-size", d => d.size + "px")
            .style("font-family", "Impact")
            .style("fill", () => d3.schemeCategory10[Math.floor(Math.random() * 10)])
            .attr("text-anchor", "middle")
            .attr("transform", d => "translate(" + [d.x, d.y] + ")rotate(" + d.rotate + ")")
            .text(d => d.text);
    }
});
</script>
<?php endif; ?>