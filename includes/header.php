<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : '';

// Determine active page
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IELTS Vocabulary Builder</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    <!-- CSRF Token -->
    <?php if ($isLoggedIn): ?>
    <meta name="csrf-token" content="<?php echo hash('sha256', session_id()); ?>">
    <?php endif; ?>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-graduation-cap"></i> IELTS Vocabulary Builder
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'index.php' ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-book"></i> Vocabulary List
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array($currentPage, ['playground.php']) ? 'active' : ''; ?>" href="#" id="studyDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-graduation-cap"></i> Study
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="studyDropdown">
                            <li><a class="dropdown-item" href="playground.php?mode=flashcards"><i class="fas fa-clone"></i> Flashcards</a></li>
                            <li><a class="dropdown-item" href="playground.php?mode=quiz"><i class="fas fa-question-circle"></i> Quiz</a></li>
                            <li><a class="dropdown-item" href="playground.php?mode=matching"><i class="fas fa-project-diagram"></i> Matching</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="playground.php?mode=collocations"><i class="fas fa-link"></i> Collocations</a></li>
                            <li><a class="dropdown-item" href="playground.php?mode=writing"><i class="fas fa-pen"></i> Writing Practice</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="playground.php?use_srs=1"><i class="fas fa-sync"></i> SRS Review</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array($currentPage, ['add.php', 'import-export.php']) ? 'active' : ''; ?>" href="#" id="manageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog"></i> Manage
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="manageDropdown">
                            <li><a class="dropdown-item" href="add.php"><i class="fas fa-plus"></i> Add New Word</a></li>
                            <li><a class="dropdown-item" href="import-export.php"><i class="fas fa-file-import"></i> Import/Export</a></li>
                        </ul>
                    </li>
                    <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'stats.php' ? 'active' : ''; ?>" href="stats.php">
                            <i class="fas fa-chart-bar"></i> Statistics
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if ($isLoggedIn): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($username); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-cog"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="stats.php"><i class="fas fa-chart-bar"></i> My Progress</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage == 'login.php' ? 'active' : ''; ?>" href="login.php">
                            <i class="fas fa-sign-in-alt"></i> Login/Register
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <?php if ($isLoggedIn): ?>
    <!-- Due for review notification -->
    <?php $dueCount = count(getDueWordsForReview(100)); ?>
    <?php if ($dueCount > 0): ?>
    <div class="review-notification">
        <div class="container">
            <div class="alert alert-info alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="fas fa-bell me-2 fs-4"></i>
                <div>
                    <strong>IELTS Study Reminder:</strong> You have <?php echo $dueCount; ?> word<?php echo $dueCount != 1 ? 's' : ''; ?> due for review today. 
                    <a href="playground.php?use_srs=1" class="alert-link">Start reviewing now!</a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
    
    <!-- IELTS Resources Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="resourcesOffcanvas" aria-labelledby="resourcesOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="resourcesOffcanvasLabel">IELTS Resources</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="list-group mb-3">
                <a href="#" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">Academic Word List</h5>
                        <small>570 words</small>
                    </div>
                    <p class="mb-1">Essential academic vocabulary for IELTS Academic test.</p>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">IELTS Topic Vocabulary</h5>
                        <small>10 topics</small>
                    </div>
                    <p class="mb-1">Curated lists for common IELTS topics like Environment, Education, etc.</p>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">Writing Task 2 Templates</h5>
                        <small>5 types</small>
                    </div>
                    <p class="mb-1">Essay structure templates with advanced vocabulary for each essay type.</p>
                </a>
            </div>
            
            <div class="card mb-3">
                <div class="card-header">IELTS Study Tips</div>
                <div class="card-body">
                    <h5 class="card-title">Effective Vocabulary Learning</h5>
                    <p class="card-text">Focus on learning words in context rather than isolated definitions. Practice using new vocabulary in sentences relevant to common IELTS topics.</p>
                    <h5 class="card-title mt-3">Collocation Mastery</h5>
                    <p class="card-text">Learning which words naturally go together (collocations) will make your speaking and writing sound more natural and achieve higher band scores.</p>
                </div>
            </div>
            
            <button type="button" class="btn btn-primary w-100" data-bs-dismiss="offcanvas">
                <i class="fas fa-book"></i> Continue Studying
            </button>
        </div>
    </div>
    
    <!-- Fixed button for resources -->
    <button class="btn btn-primary position-fixed bottom-0 end-0 mb-4 me-4 rounded-circle shadow p-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#resourcesOffcanvas" aria-controls="resourcesOffcanvas">
        <i class="fas fa-book-reader"></i>
    </button>