<?php
// Score tracking and visualization
require_once 'includes/functions.php';
require_once 'includes/data-handler.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_score'])) {
        $newScore = [
            'date' => $_POST['date'],
            'listening' => (float) $_POST['listening'],
            'reading' => (float) $_POST['reading'],
            'writing' => (float) $_POST['writing'],
            'speaking' => (float) $_POST['speaking'],
            'overall' => (float) $_POST['overall'],
            'notes' => $_POST['notes']
        ];
        
        addScore($newScore);
        $successMessage = "Score added successfully!";
    }
    
    if (isset($_POST['delete_score'])) {
        $scoreIndex = (int) $_POST['score_index'];
        deleteScore($scoreIndex);
        $successMessage = "Score deleted successfully!";
    }
}

// Get all scores
$scores = getScores();

// Page title
$pageTitle = 'IELTS Study Tracker - Scores';

// Include header
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1>IELTS Scores</h1>
            <p class="lead">Track and visualize your IELTS test scores</p>
        </div>
    </div>

    <?php if (isset($successMessage)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $successMessage; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row mt-4">
        <div class="col-md-4">
            <!-- Add Score Form -->
            <?php include 'components/score-form.php'; ?>
        </div>
        <div class="col-md-8">
            <!-- Score Charts -->
            <?php include 'components/score-charts.php'; ?>
        </div>
    </div>

    <!-- Score History Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Score History</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($scores)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Listening</th>
                                    <th>Reading</th>
                                    <th>Writing</th>
                                    <th>Speaking</th>
                                    <th>Overall</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_reverse($scores) as $index => $score): ?>
                                <tr>
                                    <td><?php echo date('Y-m-d', strtotime($score['date'])); ?></td>
                                    <td><?php echo $score['listening']; ?></td>
                                    <td><?php echo $score['reading']; ?></td>
                                    <td><?php echo $score['writing']; ?></td>
                                    <td><?php echo $score['speaking']; ?></td>
                                    <td><?php echo $score['overall']; ?></td>
                                    <td><?php echo htmlspecialchars($score['notes'] ?? ''); ?></td>
                                    <td>
                                        <form method="post" onsubmit="return confirm('Are you sure you want to delete this score?');">
                                            <input type="hidden" name="score_index" value="<?php echo count($scores) - 1 - $index; ?>">
                                            <button type="submit" name="delete_score" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p>No scores recorded yet. Use the form to add your first score.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>