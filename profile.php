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
        
        // Validate inputs
        if ($dailyGoal < 1) {
            $errors[] = "Daily goal must be at least 1";
        }
        
        if (empty($errors)) {
            $settings = [
                'notifications' => $notifications,
                'daily_goal' => $dailyGoal
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

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4>Profile Menu</h4>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#profile-settings" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    <a href="#change-password" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                    <a href="stats.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-bar"></i> Statistics
                    </a>
                </div>
            </div>
            
            <!-- Learning Streak Card -->
            <div class="card mt-4">
                <div class="card-header bg-success text-white">
                    <h4>Learning Streak</h4>
                </div>
                <div class="card-body text-center">
                    <h2 class="display-4"><?php echo $user['streak']['current']; ?></h2>
                    <p class="lead">days</p>
                    <p class="text-muted">Longest: <?php echo $user['streak']['max']; ?> days</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $success; ?>
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
                <!-- Profile Settings -->
                <div class="tab-pane fade show active" id="profile-settings">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4>Profile Settings</h4>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                    <small class="form-text text-muted">Username cannot be changed</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Daily Goal (words to study)</label>
                                    <input type="number" class="form-control" name="daily_goal" min="1" max="100" value="<?php echo $user['settings']['daily_goal']; ?>">
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="notifications" name="notifications" <?php echo $user['settings']['notifications'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="notifications">Enable Notifications</label>
                                    <small class="form-text text-muted d-block">Receive reminders about words due for review</small>
                                </div>
                                
                                <button type="submit" name="update_settings" class="btn btn-primary">Save Settings</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Change Password -->
                <div class="tab-pane fade" id="change-password">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4>Change Password</h4>
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
                                    <small class="form-text text-muted">At least 6 characters</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <button type="submit" name="update_password" class="btn btn-primary">Change Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>