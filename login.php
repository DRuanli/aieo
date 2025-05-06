<?php
require_once 'includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];
$success = '';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate inputs
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    if (empty($errors)) {
        $userId = loginUser($username, $password);
        
        if ($userId) {
            // Set session variables
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;
            
            // Redirect to home page
            header('Location: index.php');
            exit;
        } else {
            $errors[] = "Invalid username or password";
        }
    }
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['reg_username'] ?? '');
    $email = trim($_POST['reg_email'] ?? '');
    $password = $_POST['reg_password'] ?? '';
    $confirmPassword = $_POST['reg_confirm_password'] ?? '';
    $targetIeltsBand = $_POST['reg_target_ielts'] ?? '7.0';
    $studyPreference = $_POST['reg_study_preference'] ?? 'balanced';
    
    // Validate inputs
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($errors)) {
        $userId = registerUser($username, $password, $email);
        
        if ($userId) {
            // Update additional IELTS settings
            $settings = [
                'target_ielts_band' => $targetIeltsBand,
                'study_preference' => $studyPreference
            ];
            
            updateUserSettings($userId, $settings);
            
            $success = "Registration successful! You can now log in to start your IELTS vocabulary journey.";
        } else {
            $errors[] = "Username already exists";
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row align-items-center">
        <div class="col-md-6 mb-5 mb-md-0">
            <h1 class="display-4 fw-bold mb-4">Boost Your IELTS Score with Vocabulary Mastery</h1>
            
            <p class="lead mb-4">Our spaced repetition system is specifically designed for IELTS preparation, helping you:</p>
            
            <ul class="list-unstyled mb-4">
                <li class="mb-3">
                    <div class="d-flex">
                        <div class="me-3 text-primary">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Master band-specific vocabulary</h5>
                            <p class="text-muted">Targeted words for bands 5-9, organized by IELTS topics</p>
                        </div>
                    </div>
                </li>
                <li class="mb-3">
                    <div class="d-flex">
                        <div class="me-3 text-primary">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Practice collocations and context</h5>
                            <p class="text-muted">Learn how to use words naturally in IELTS writing and speaking</p>
                        </div>
                    </div>
                </li>
                <li class="mb-3">
                    <div class="d-flex">
                        <div class="me-3 text-primary">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Track your progress with analytics</h5>
                            <p class="text-muted">See your readiness level for each IELTS band score</p>
                        </div>
                    </div>
                </li>
            </ul>
            
            <div class="alert alert-info d-flex align-items-center">
                <div class="me-3">
                    <i class="fas fa-graduation-cap fa-2x"></i>
                </div>
                <div>
                    <h5 class="alert-heading">Did you know?</h5>
                    <p class="mb-0">Vocabulary accounts for 25% of your IELTS score. Our app targets the exact vocabulary you need for your target band.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="card border-0 shadow">
                <div class="card-header bg-primary text-white">
                    <ul class="nav nav-tabs card-header-tabs" id="auth-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active text-dark" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab" aria-controls="login" aria-selected="true">Login</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-dark" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab" aria-controls="register" aria-selected="false">Register</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content" id="auth-tabs-content">
                        <!-- Login Form -->
                        <div class="tab-pane fade show active" id="login" role="tabpanel" aria-labelledby="login-tab">
                            <h4 class="text-center mb-4">Welcome Back!</h4>
                            
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                </div>
                                <button type="submit" name="login" class="btn btn-primary w-100 py-2">
                                    <i class="fas fa-sign-in-alt me-2"></i> Login
                                </button>
                                
                                <div class="text-center mt-4">
                                    <p class="text-muted">Don't have an account? <a href="#" onclick="document.getElementById('register-tab').click(); return false;">Register here</a></p>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Registration Form -->
                        <div class="tab-pane fade" id="register" role="tabpanel" aria-labelledby="register-tab">
                            <h4 class="text-center mb-4">Create Your IELTS Vocabulary Account</h4>
                            
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="reg_username" class="form-label">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="reg_username" name="reg_username" required>
                                    </div>
                                    <div class="form-text">At least 3 characters</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="reg_email" class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="reg_email" name="reg_email" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="reg_target_ielts" class="form-label">Your Target IELTS Band Score</label>
                                    <select class="form-select" id="reg_target_ielts" name="reg_target_ielts">
                                        <option value="5.0">Band 5.0</option>
                                        <option value="5.5">Band 5.5</option>
                                        <option value="6.0">Band 6.0</option>
                                        <option value="6.5">Band 6.5</option>
                                        <option value="7.0" selected>Band 7.0</option>
                                        <option value="7.5">Band 7.5</option>
                                        <option value="8.0">Band 8.0+</option>
                                    </select>
                                    <div class="form-text">We'll tailor vocabulary to help you reach this score</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="reg_study_preference" class="form-label">Study Focus</label>
                                    <select class="form-select" id="reg_study_preference" name="reg_study_preference">
                                        <option value="balanced" selected>Balanced (All skills)</option>
                                        <option value="academic">Academic IELTS</option>
                                        <option value="general">General Training IELTS</option>
                                        <option value="speaking">Speaking Focus</option>
                                        <option value="writing">Writing Focus</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="reg_password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="reg_password" name="reg_password" required>
                                    </div>
                                    <div class="form-text">At least 6 characters</div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="reg_confirm_password" class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="reg_confirm_password" name="reg_confirm_password" required>
                                    </div>
                                </div>
                                
                                <button type="submit" name="register" class="btn btn-success w-100 py-2">
                                    <i class="fas fa-user-plus me-2"></i> Create Account
                                </button>
                                
                                <div class="text-center mt-4">
                                    <p class="text-muted">Already have an account? <a href="#" onclick="document.getElementById('login-tab').click(); return false;">Login here</a></p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>