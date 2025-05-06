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

$errors = [];
$success = '';

// Get user data
$userId = $_SESSION['user_id'];
$user = getUserById($userId);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_settings'])) {
        // Update user settings
        $notifications = isset($_POST['notifications']) ? true : false;
        $dailyGoal = (int)$_POST['daily_goal'];
        $targetIeltsBand = (float)$_POST['target_ielts_band'];
        
        // Validate inputs
        if ($dailyGoal < 1) {
            $errors[] = "Daily goal must be at least 1";
        }
        
        if ($targetIeltsBand < 5 || $targetIeltsBand > 9) {
            $errors[] = "Target IELTS band must be between 5.0 and 9.0";
        }
        
        if (empty($errors)) {
            $settings = [
                'notifications' => $notifications,
                'daily_goal' => $dailyGoal,
                'target_ielts_band' => $targetIeltsBand,
                'study_preference' => $_POST['study_preference'] ?? 'balanced'
            ];
            
            if (updateUserSettings($userId, $settings)) {
                $success = "Settings updated successfully";
                $user = getUserById($userId); // Refresh user data
            } else {
                $errors[] = "Failed to update settings";
            }
        }
    } elseif (isset($_POST['update_password'])) {
        // Update password
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate inputs
        if (empty($currentPassword)) {
            $errors[] = "Current password is required";
        }
        
        if (empty($newPassword)) {
            $errors[] = "New password is required";
        } elseif (strlen($newPassword) < 6) {
            $errors[] = "New password must be at least 6 characters";
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = "New passwords do not match";
        }
        
        // Verify current password
        if (empty($errors)) {
            $jsonData = file_get_contents(DATA_DIR . '/users.json');
            $users = json_decode($jsonData, true);
            
            $passwordCorrect = false;
            foreach ($users as $key => $userData) {
                if ($userData['id'] == $userId && password_verify($currentPassword, $userData['password'])) {
                    $passwordCorrect = true;
                    
                    // Update password
                    $users[$key]['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                    
                    // Save updated users data
                    $jsonData = json_encode($users, JSON_PRETTY_PRINT);
                    if (file_put_contents(DATA_DIR . '/users.json', $jsonData) !== false) {
                        $success = "Password updated successfully";
                    } else {
                        $errors[] = "Failed to update password";
                    }
                    
                    break;
                }
            }
            
            if (!$passwordCorrect) {
                $errors[] = "Current password is incorrect";
            }
        }
    }
}

// Get IELTS readiness data
$ieltsReadiness = getIELTSReadiness($userId);

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4><i class="fas fa-user-circle"></i> Profile</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="display-1 text-primary mb-2">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="small text-muted">Member since: <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                    </div>
                    
                    <div class="list-group list-group-flush">
                        <a href="#profile-settings" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                            <i class="fas fa-cog me-2"></i> IELTS Settings
                        </a>
                        <a href="#change-password" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-key me-2"></i> Change Password
                        </a>
                        <a href="stats.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-bar me-2"></i> My Statistics
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Learning Streak Card -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4><i class="fas fa-fire me-2"></i> Learning Streak</h4>
                </div>
                <div class="card-body text-center">
                    <h2 class="display-1 fw-bold"><?php echo $user['streak']['current']; ?></h2>
                    <p class="lead mb-1">days</p>
                    <p class="text-muted">Longest streak: <?php echo $user['streak']['max']; ?> days</p>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between text-muted small mb-1">
                            <span>Progress today:</span>
                            <span>
                                <?php
                                // Calculate progress towards daily goal
                                $stats = getUserStatistics($userId);
                                $today = date('Y-m-d');
                                $todayWords = 0;
                                
                                if (isset($stats['daily_activity'])) {
                                    foreach ($stats['daily_activity'] as $activity) {
                                        if ($activity['date'] === $today) {
                                            $todayWords = $activity['words_studied'];
                                            break;
                                        }
                                    }
                                }
                                
                                $dailyGoal = $user['settings']['daily_goal'] ?? 10;
                                $progress = min(100, ($todayWords / $dailyGoal) * 100);
                                
                                echo $todayWords . '/' . $dailyGoal . ' words';
                                ?>
                            </span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progress; ?>%" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
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
            
            <div class="tab-content">
                <!-- IELTS Profile Settings -->
                <div class="tab-pane fade show active" id="profile-settings">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h4><i class="fas fa-graduation-cap me-2"></i> IELTS Goals & Settings</h4>
                        </div>
                        <div class="card-body">
                            <!-- IELTS Readiness Banner -->
                            <div class="alert alert-primary mb-4">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="fas fa-chart-line fa-2x"></i>
                                    </div>
                                    <div>
                                        <h5 class="alert-heading">Your IELTS Vocabulary Readiness: <?php echo $ieltsReadiness['overall']; ?>%</h5>
                                        <div class="progress mb-2" style="height: 10px;">
                                            <div class="progress-bar" role="progressbar" style="width: <?php echo $ieltsReadiness['overall']; ?>%" aria-valuenow="<?php echo $ieltsReadiness['overall']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <p class="mb-0">You have mastered <?php echo $ieltsReadiness['mastered']; ?> words and are currently learning <?php echo $ieltsReadiness['learning']; ?> words.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <form method="post" action="">
                                <div class="mb-4">
                                    <label class="form-label">Target IELTS Band Score</label>
                                    <select class="form-select" name="target_ielts_band">
                                        <option value="5.0" <?php echo ($user['settings']['target_ielts_band'] ?? 7.0) == 5.0 ? 'selected' : ''; ?>>Band 5.0</option>
                                        <option value="5.5" <?php echo ($user['settings']['target_ielts_band'] ?? 7.0) == 5.5 ? 'selected' : ''; ?>>Band 5.5</option>
                                        <option value="6.0" <?php echo ($user['settings']['target_ielts_band'] ?? 7.0) == 6.0 ? 'selected' : ''; ?>>Band 6.0</option>
                                        <option value="6.5" <?php echo ($user['settings']['target_ielts_band'] ?? 7.0) == 6.5 ? 'selected' : ''; ?>>Band 6.5</option>
                                        <option value="7.0" <?php echo ($user['settings']['target_ielts_band'] ?? 7.0) == 7.0 ? 'selected' : ''; ?>>Band 7.0</option>
                                        <option value="7.5" <?php echo ($user['settings']['target_ielts_band'] ?? 7.0) == 7.5 ? 'selected' : ''; ?>>Band 7.5</option>
                                        <option value="8.0" <?php echo ($user['settings']['target_ielts_band'] ?? 7.0) == 8.0 ? 'selected' : ''; ?>>Band 8.0</option>
                                        <option value="8.5" <?php echo ($user['settings']['target_ielts_band'] ?? 7.0) == 8.5 ? 'selected' : ''; ?>>Band 8.5</option>
                                        <option value="9.0" <?php echo ($user['settings']['target_ielts_band'] ?? 7.0) == 9.0 ? 'selected' : ''; ?>>Band 9.0</option>
                                    </select>
                                    <div class="form-text">This helps us recommend appropriate vocabulary for your target score.</div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Study Focus</label>
                                    <select class="form-select" name="study_preference">
                                        <option value="balanced" <?php echo ($user['settings']['study_preference'] ?? 'balanced') == 'balanced' ? 'selected' : ''; ?>>Balanced (Reading, Writing, Speaking, Listening)</option>
                                        <option value="writing" <?php echo ($user['settings']['study_preference'] ?? 'balanced') == 'writing' ? 'selected' : ''; ?>>Writing Focus</option>
                                        <option value="speaking" <?php echo ($user['settings']['study_preference'] ?? 'balanced') == 'speaking' ? 'selected' : ''; ?>>Speaking Focus</option>
                                        <option value="academic" <?php echo ($user['settings']['study_preference'] ?? 'balanced') == 'academic' ? 'selected' : ''; ?>>Academic IELTS</option>
                                        <option value="general" <?php echo ($user['settings']['study_preference'] ?? 'balanced') == 'general' ? 'selected' : ''; ?>>General Training IELTS</option>
                                    </select>
                                    <div class="form-text">We'll prioritize vocabulary and exercises appropriate for your focus area.</div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Daily Goal (words to study)</label>
                                    <input type="number" class="form-control" name="daily_goal" min="1" max="100" value="<?php echo $user['settings']['daily_goal'] ?? 10; ?>">
                                    <div class="form-text">Set a realistic daily goal to maintain your streak.</div>
                                </div>
                                
                                <div class="mb-4 form-check">
                                    <input type="checkbox" class="form-check-input" id="notifications" name="notifications" <?php echo ($user['settings']['notifications'] ?? true) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="notifications">Enable Notifications</label>
                                    <div class="form-text">Receive reminders about words due for review</div>
                                </div>
                                
                                <button type="submit" name="update_settings" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Save IELTS Settings
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- IELTS Topic Preferences -->
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h4><i class="fas fa-tags me-2"></i> IELTS Topic Preferences</h4>
                        </div>
                        <div class="card-body">
                            <p>Select the IELTS topics you want to focus on. We'll prioritize vocabulary from these topics.</p>
                            
                            <form method="post" action="">
                                <div class="row">
                                    <?php 
                                    $categories = getCategories();
                                    $userTopics = $user['settings']['preferred_topics'] ?? [];
                                    
                                    foreach ($categories as $category): 
                                    ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="preferred_topics[]" 
                                                   value="<?php echo htmlspecialchars($category['name']); ?>" 
                                                   id="topic_<?php echo $category['id']; ?>"
                                                   <?php echo in_array($category['name'], $userTopics) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="topic_<?php echo $category['id']; ?>">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </label>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <button type="submit" name="update_topics" class="btn btn-info text-white mt-3">
                                    <i class="fas fa-save me-2"></i> Save Topic Preferences
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Change Password -->
                <div class="tab-pane fade" id="change-password">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4><i class="fas fa-key me-2"></i> Change Password</h4>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <div class="form-text">At least 6 characters</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <button type="submit" name="update_password" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>