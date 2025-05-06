<?php
// Store and manage IELTS work
require_once 'includes/functions.php';
require_once 'includes/data-handler.php';

// Get the skill type from the query string, default to 'writing'
$skill = isset($_GET['skill']) ? $_GET['skill'] : 'writing';
$validSkills = ['writing', 'speaking', 'reading', 'listening'];
if (!in_array($skill, $validSkills)) {
    $skill = 'writing';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_work'])) {
        $content = $_POST['content'];
        $title = $_POST['title'];
        $date = $_POST['date'];
        $notes = $_POST['notes'];
        
        // Process the content for word statistics
        processWordStatistics($content, $skill);
        
        // Save the work
        $workData = [
            'title' => $title,
            'date' => $date,
            'content' => $content,
            'notes' => $notes
        ];
        
        addWork($workData, $skill);
        $successMessage = "Work saved successfully!";
    }
    
    if (isset($_POST['delete_work'])) {
        $workId = $_POST['work_id'];
        deleteWork($workId, $skill);
        $successMessage = "Work deleted successfully!";
    }
}

// Get all works for the selected skill
$works = getWorks($skill);

// Page title
$pageTitle = 'IELTS Study Tracker - Works';

// Include header
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1>IELTS Work Samples</h1>
            <p class="lead">Store and manage your IELTS practice work</p>
        </div>
    </div>

    <?php if (isset($successMessage)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $successMessage; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

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

    <div class="row mt-4">
        <div class="col-md-4">
            <!-- Add Work Form -->
            <div class="card">
                <div class="card-header">
                    <h5>Add New <?php echo ucfirst($skill); ?> Work</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                            <small class="text-muted">Enter your <?php echo $skill; ?> task content here.</small>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                        <button type="submit" name="add_work" class="btn btn-primary">Save Work</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <!-- Work History List -->
            <div class="card">
                <div class="card-header">
                    <h5><?php echo ucfirst($skill); ?> History</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($works)): ?>
                        <div class="accordion" id="workAccordion">
                            <?php foreach ($works as $index => $work): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="false" aria-controls="collapse<?php echo $index; ?>">
                                            <strong><?php echo htmlspecialchars($work['title']); ?></strong> - <?php echo date('Y-m-d', strtotime($work['date'])); ?>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#workAccordion">
                                        <div class="accordion-body">
                                            <div class="mb-3">
                                                <h6>Content:</h6>
                                                <div class="content-box p-3 bg-light">
                                                    <?php echo nl2br(htmlspecialchars($work['content'])); ?>
                                                </div>
                                            </div>
                                            <?php if (!empty($work['notes'])): ?>
                                                <div class="mb-3">
                                                    <h6>Notes:</h6>
                                                    <div class="notes-box p-3 bg-light">
                                                        <?php echo nl2br(htmlspecialchars($work['notes'])); ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <div class="d-flex justify-content-between">
                                                <form method="post" onsubmit="return confirm('Are you sure you want to delete this work?');">
                                                    <input type="hidden" name="work_id" value="<?php echo $work['id']; ?>">
                                                    <button type="submit" name="delete_work" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                                <span class="text-muted small">ID: <?php echo $work['id']; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No <?php echo $skill; ?> work samples recorded yet. Use the form to add your first one.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>