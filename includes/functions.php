<?php
/**
 * Enhanced functions.php with IELTS-specific functionality
 */

// Database directory and file constants
define('DATA_DIR', __DIR__ . '/../data');
define('VOCAB_FILE', DATA_DIR . '/vocabulary.json');
define('USERS_FILE', DATA_DIR . '/users.json');
define('CATEGORIES_FILE', DATA_DIR . '/categories.json');
define('STATS_FILE', DATA_DIR . '/stats.json');
define('DAILY_WORDS_FILE', DATA_DIR . '/daily_words.json');
define('AUDIO_DIR', DATA_DIR . '/audio');

/**
 * Initialize the data structure if it doesn't exist
 */
function initializeDataStructure() {
    // Create data directory if it doesn't exist
    if (!file_exists(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }
    
    // Create audio directory if it doesn't exist
    if (!file_exists(AUDIO_DIR)) {
        mkdir(AUDIO_DIR, 0755, true);
    }
    
    // Initialize vocabulary file
    if (!file_exists(VOCAB_FILE)) {
        file_put_contents(VOCAB_FILE, json_encode([]));
    }
    
    // Initialize users file
    if (!file_exists(USERS_FILE)) {
        file_put_contents(USERS_FILE, json_encode([]));
    }
    
    // Initialize categories file
    if (!file_exists(CATEGORIES_FILE)) {
        $defaultCategories = [
            ['id' => 1, 'name' => 'Education', 'description' => 'Vocabulary related to education and learning'],
            ['id' => 2, 'name' => 'Environment', 'description' => 'Vocabulary related to environment and climate'],
            ['id' => 3, 'name' => 'Technology', 'description' => 'Tech and digital vocabulary for IELTS'],
            ['id' => 4, 'name' => 'Health', 'description' => 'Health and wellness vocabulary'],
            ['id' => 5, 'name' => 'Society', 'description' => 'Social issues vocabulary for IELTS'],
            ['id' => 6, 'name' => 'Work', 'description' => 'Career and workplace vocabulary'],
            ['id' => 7, 'name' => 'Urban Development', 'description' => 'City planning and urbanization vocabulary'],
            ['id' => 8, 'name' => 'Travel', 'description' => 'Travel and transportation vocabulary'],
            ['id' => 9, 'name' => 'Culture', 'description' => 'Cultural topics vocabulary for IELTS'],
            ['id' => 10, 'name' => 'Globalization', 'description' => 'International relations vocabulary']
        ];
        file_put_contents(CATEGORIES_FILE, json_encode($defaultCategories));
    }
    
    // Initialize stats file
    if (!file_exists(STATS_FILE)) {
        file_put_contents(STATS_FILE, json_encode([]));
    }
    
    // Initialize daily words file
    if (!file_exists(DAILY_WORDS_FILE)) {
        file_put_contents(DAILY_WORDS_FILE, json_encode([]));
    }
}

/**
 * Get the current user ID from session
 * 
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    if (isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    }
    return null;
}

/**
 * Function to get vocabulary data with filtering and pagination
 * 
 * @param array $filters Optional filters (search, category, difficulty, ielts_band, study_type)
 * @param int $page Current page number
 * @param int $perPage Items per page
 * @return array Vocabulary data with pagination info
 */
function getVocabulary($filters = [], $page = 1, $perPage = 10) {
    initializeDataStructure();
    
    $jsonData = file_get_contents(VOCAB_FILE);
    $vocabulary = json_decode($jsonData, true);
    
    // Handle potential JSON errors
    if ($vocabulary === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON Error: ' . json_last_error_msg());
        return ['data' => [], 'pagination' => getPaginationInfo(0, $page, $perPage)];
    }
    
    $vocabulary = is_array($vocabulary) ? $vocabulary : [];
    
    // Apply user filter if logged in
    $userId = getCurrentUserId();
    if ($userId !== null) {
        $vocabulary = array_filter($vocabulary, function($item) use ($userId) {
            return !isset($item['user_id']) || $item['user_id'] == $userId;
        });
    }
    
    // Apply search filter
    if (!empty($filters['search'])) {
        $search = strtolower($filters['search']);
        $vocabulary = array_filter($vocabulary, function($item) use ($search) {
            return strpos(strtolower($item['english']), $search) !== false || 
                   strpos(strtolower($item['vietnamese']), $search) !== false ||
                   (isset($item['context']) && strpos(strtolower($item['context']), $search) !== false);
        });
    }
    
    // Apply category filter
    if (!empty($filters['category'])) {
        $category = $filters['category'];
        $vocabulary = array_filter($vocabulary, function($item) use ($category) {
            return isset($item['category']) && in_array($category, $item['category']);
        });
    }
    
    // Apply difficulty filter
    if (!empty($filters['difficulty'])) {
        $difficulty = $filters['difficulty'];
        $vocabulary = array_filter($vocabulary, function($item) use ($difficulty) {
            return isset($item['difficulty']) && $item['difficulty'] === $difficulty;
        });
    }
    
    // Apply IELTS band level filter
    if (!empty($filters['ielts_band'])) {
        $ielts_band = $filters['ielts_band'];
        $vocabulary = array_filter($vocabulary, function($item) use ($ielts_band) {
            return isset($item['ielts_band']) && $item['ielts_band'] == $ielts_band;
        });
    }
    
    // Apply study type filter
    if (!empty($filters['study_type'])) {
        if ($filters['study_type'] === 'academic') {
            // Academic Word List filter - words with higher IELTS band levels
            $vocabulary = array_filter($vocabulary, function($item) {
                return isset($item['ielts_band']) && $item['ielts_band'] >= 7;
            });
        } else if ($filters['study_type'] === 'collocations') {
            // Collocations filter - words with collocations defined
            $vocabulary = array_filter($vocabulary, function($item) {
                return isset($item['collocations']) && !empty($item['collocations']);
            });
        }
    }
    
    // Reindex array
    $vocabulary = array_values($vocabulary);
    
    // Get total count for pagination
    $totalItems = count($vocabulary);
    
    // Apply pagination
    $offset = ($page - 1) * $perPage;
    $paginatedData = array_slice($vocabulary, $offset, $perPage);
    
    return [
        'data' => $paginatedData,
        'pagination' => getPaginationInfo($totalItems, $page, $perPage)
    ];
}

/**
 * Helper function to get pagination information
 * 
 * @param int $totalItems Total number of items
 * @param int $currentPage Current page number
 * @param int $perPage Items per page
 * @return array Pagination information
 */
function getPaginationInfo($totalItems, $currentPage, $perPage) {
    $totalPages = ceil($totalItems / $perPage);
    
    return [
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'per_page' => $perPage,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Function to save vocabulary data
 * 
 * @param array $vocabulary Vocabulary data
 * @return bool Success status
 */
function saveVocabulary($vocabulary) {
    initializeDataStructure();
    
    $jsonData = json_encode($vocabulary, JSON_PRETTY_PRINT);
    return file_put_contents(VOCAB_FILE, $jsonData) !== false;
}

/**
 * Function to get a single vocabulary entry by ID
 * 
 * @param int $id Vocabulary ID
 * @return array|null Vocabulary entry or null if not found
 */
function getVocabularyById($id) {
    $vocabulary = json_decode(file_get_contents(VOCAB_FILE), true) ?? [];
    
    foreach ($vocabulary as $item) {
        if (isset($item['id']) && $item['id'] == $id) {
            return $item;
        }
    }
    
    return null;
}

/**
 * Function to add or update a vocabulary entry
 * 
 * @param array $data Vocabulary data
 * @param int|null $id ID for updating, null for adding new
 * @return bool Success status
 */
function saveVocabularyEntry($data, $id = null) {
    $vocabulary = json_decode(file_get_contents(VOCAB_FILE), true) ?? [];
    
    // Process arrays from form data
    $examples = isset($data['examples']) ? explode("\n", trim($data['examples'])) : [];
    $examples = array_map('trim', $examples);
    $examples = array_filter($examples, function($item) { return !empty($item); });
    
    $synonyms = isset($data['synonyms']) ? array_map('trim', explode(',', $data['synonyms'])) : [];
    $synonyms = array_filter($synonyms, function($item) { return !empty($item); });
    
    $antonyms = isset($data['antonyms']) ? array_map('trim', explode(',', $data['antonyms'])) : [];
    $antonyms = array_filter($antonyms, function($item) { return !empty($item); });
    
    $collocations = isset($data['collocations']) ? array_map('trim', explode(',', $data['collocations'])) : [];
    $collocations = array_filter($collocations, function($item) { return !empty($item); });
    
    // Prepare data with required fields
    $entry = [
        'english' => $data['english'],
        'vietnamese' => $data['vietnamese'],
        'context' => $data['context'] ?? '',
        'examples' => $examples,
        'synonyms' => $synonyms,
        'antonyms' => $antonyms,
        'collocations' => $collocations,
        'ielts_usage' => $data['ielts_usage'] ?? '',
        'ielts_band' => $data['ielts_band'] ?? '6', 
        'category' => isset($data['category']) ? $data['category'] : [],
        'difficulty' => $data['difficulty'] ?? 'medium',
        'pronunciation' => $data['pronunciation'] ?? ['en' => '', 'vi' => ''],
        'last_reviewed' => date('Y-m-d'),
        'srs_level' => $data['srs_level'] ?? 0,
        'srs_interval' => $data['srs_interval'] ?? 1
    ];
    
    // Add user_id if logged in
    $userId = getCurrentUserId();
    if ($userId !== null) {
        $entry['user_id'] = $userId;
    }
    
    // Add timestamp for new entries
    if ($id === null) {
        $entry['id'] = time() . rand(100, 999); // Generate unique ID
        $entry['created_at'] = date('Y-m-d');
        
        // Initialize review history
        $entry['review_history'] = [];
        
        // Calculate next review date based on SRS
        $entry['next_review'] = calculateNextReviewDate(0);
        
        // Add to vocabulary
        $vocabulary[] = $entry;
    } else {
        // Update existing entry
        $found = false;
        foreach ($vocabulary as $key => $item) {
            if (isset($item['id']) && $item['id'] == $id) {
                // Preserve existing fields that shouldn't be overwritten
                $entry['id'] = $id;
                $entry['created_at'] = $item['created_at'] ?? date('Y-m-d');
                $entry['review_history'] = $item['review_history'] ?? [];
                $entry['next_review'] = $item['next_review'] ?? date('Y-m-d');
                
                $vocabulary[$key] = $entry;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            return false; // Entry not found for updating
        }
    }
    
    return saveVocabulary($vocabulary);
}

/**
 * Function to delete a vocabulary entry
 * 
 * @param int $id Vocabulary ID
 * @return bool Success status
 */
function deleteVocabularyEntry($id) {
    $vocabulary = json_decode(file_get_contents(VOCAB_FILE), true) ?? [];
    $found = false;
    
    foreach ($vocabulary as $key => $item) {
        if (isset($item['id']) && $item['id'] == $id) {
            unset($vocabulary[$key]);
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        return false; // Entry not found
    }
    
    // Reindex array
    $vocabulary = array_values($vocabulary);
    
    return saveVocabulary($vocabulary);
}

/**
 * Function to get all categories
 * 
 * @return array Categories
 */
function getCategories() {
    initializeDataStructure();
    
    $jsonData = file_get_contents(CATEGORIES_FILE);
    $categories = json_decode($jsonData, true);
    
    // Handle potential JSON errors
    if ($categories === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON Error: ' . json_last_error_msg());
        return [];
    }
    
    return is_array($categories) ? $categories : [];
}

/**
 * Function to add a new category
 * 
 * @param string $name Category name
 * @param string $description Category description
 * @return bool Success status
 */
function addCategory($name, $description = '') {
    $categories = getCategories();
    
    // Check if category already exists
    foreach ($categories as $category) {
        if (strtolower($category['name']) === strtolower($name)) {
            return false; // Category already exists
        }
    }
    
    // Generate new ID
    $maxId = 0;
    foreach ($categories as $category) {
        if ($category['id'] > $maxId) {
            $maxId = $category['id'];
        }
    }
    
    // Add new category
    $categories[] = [
        'id' => $maxId + 1,
        'name' => $name,
        'description' => $description
    ];
    
    $jsonData = json_encode($categories, JSON_PRETTY_PRINT);
    return file_put_contents(CATEGORIES_FILE, $jsonData) !== false;
}

/**
 * Function to get the daily word
 * 
 * @return array|null Daily word or null if not available
 */
function getDailyWord() {
    initializeDataStructure();
    
    $today = date('Y-m-d');
    
    // Check if we have a daily word for today
    $jsonData = file_get_contents(DAILY_WORDS_FILE);
    $dailyWords = json_decode($jsonData, true);
    
    if ($dailyWords === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON Error: ' . json_last_error_msg());
        $dailyWords = [];
    }
    
    // Find today's word
    foreach ($dailyWords as $dailyWord) {
        if ($dailyWord['date'] === $today) {
            // Get the word details
            $wordId = $dailyWord['word_id'];
            return getVocabularyById($wordId);
        }
    }
    
    // No daily word for today, so create one
    $vocabulary = json_decode(file_get_contents(VOCAB_FILE), true) ?? [];
    
    if (empty($vocabulary)) {
        return null; // No vocabulary entries available
    }
    
    // Prefer higher IELTS band words for daily word
    $filteredVocab = array_filter($vocabulary, function($item) {
        return isset($item['ielts_band']) && $item['ielts_band'] >= 6;
    });
    
    // If no high-band words, use all vocabulary
    if (empty($filteredVocab)) {
        $filteredVocab = $vocabulary;
    }
    
    // Randomly select a word
    $randomIndex = array_rand($filteredVocab);
    $selectedWord = $filteredVocab[$randomIndex];
    
    // Save as today's daily word
    $dailyWords[] = [
        'date' => $today,
        'word_id' => $selectedWord['id']
    ];
    
    $jsonData = json_encode($dailyWords, JSON_PRETTY_PRINT);
    file_put_contents(DAILY_WORDS_FILE, $jsonData);
    
    return $selectedWord;
}

/**
 * Function to register a new user
 * 
 * @param string $username Username
 * @param string $password Password
 * @param string $email Email
 * @return int|false User ID on success, false on failure
 */
function registerUser($username, $password, $email) {
    initializeDataStructure();
    
    $jsonData = file_get_contents(USERS_FILE);
    $users = json_decode($jsonData, true);
    
    if ($users === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON Error: ' . json_last_error_msg());
        $users = [];
    }
    
    // Check if username already exists
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            return false; // Username already exists
        }
    }
    
    // Generate new ID
    $maxId = 0;
    foreach ($users as $user) {
        if ($user['id'] > $maxId) {
            $maxId = $user['id'];
        }
    }
    
    $userId = $maxId + 1;
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Add new user
    $users[] = [
        'id' => $userId,
        'username' => $username,
        'password' => $hashedPassword,
        'email' => $email,
        'created_at' => date('Y-m-d'),
        'streak' => [
            'current' => 0,
            'max' => 0,
            'last_login' => date('Y-m-d')
        ],
        'settings' => [
            'notifications' => true,
            'daily_goal' => 10,
            'target_ielts_band' => 7.0
        ]
    ];
    
    $jsonData = json_encode($users, JSON_PRETTY_PRINT);
    if (file_put_contents(USERS_FILE, $jsonData) !== false) {
        return $userId;
    }
    
    return false;
}

/**
 * Function to authenticate a user
 * 
 * @param string $username Username
 * @param string $password Password
 * @return int|false User ID on success, false on failure
 */
function loginUser($username, $password) {
    $jsonData = file_get_contents(USERS_FILE);
    $users = json_decode($jsonData, true);
    
    if ($users === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON Error: ' . json_last_error_msg());
        return false;
    }
    
    foreach ($users as $key => $user) {
        if ($user['username'] === $username && password_verify($password, $user['password'])) {
            // Update streak information
            $today = date('Y-m-d');
            $lastLogin = $user['streak']['last_login'];
            
            // Calculate days difference
            $daysDiff = (strtotime($today) - strtotime($lastLogin)) / (60 * 60 * 24);
            
            if ($daysDiff <= 1) {
                // Consecutive login (same day or next day)
                if ($daysDiff > 0) { // Only increment if it's a new day
                    $users[$key]['streak']['current']++;
                    // Update max streak if current is greater
                    if ($users[$key]['streak']['current'] > $users[$key]['streak']['max']) {
                        $users[$key]['streak']['max'] = $users[$key]['streak']['current'];
                    }
                }
            } else {
                // Streak broken
                $users[$key]['streak']['current'] = 1;
            }
            
            // Update last login
            $users[$key]['streak']['last_login'] = $today;
            
            // Save updated user data
            $jsonData = json_encode($users, JSON_PRETTY_PRINT);
            file_put_contents(USERS_FILE, $jsonData);
            
            return $user['id'];
        }
    }
    
    return false;
}

/**
 * Function to get user data by ID
 * 
 * @param int $userId User ID
 * @return array|null User data or null if not found
 */
function getUserById($userId) {
    $jsonData = file_get_contents(USERS_FILE);
    $users = json_decode($jsonData, true);
    
    if ($users === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON Error: ' . json_last_error_msg());
        return null;
    }
    
    foreach ($users as $user) {
        if ($user['id'] == $userId) {
            return $user;
        }
    }
    
    return null;
}

/**
 * Function to update user settings
 * 
 * @param int $userId User ID
 * @param array $settings New settings
 * @return bool Success status
 */
function updateUserSettings($userId, $settings) {
    $jsonData = file_get_contents(USERS_FILE);
    $users = json_decode($jsonData, true);
    
    if ($users === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON Error: ' . json_last_error_msg());
        return false;
    }
    
    foreach ($users as $key => $user) {
        if ($user['id'] == $userId) {
            $users[$key]['settings'] = array_merge($user['settings'], $settings);
            $jsonData = json_encode($users, JSON_PRETTY_PRINT);
            return file_put_contents(USERS_FILE, $jsonData) !== false;
        }
    }
    
    return false;
}

/**
 * Function to record review activity
 * 
 * @param int $wordId Vocabulary ID
 * @param string $result Review result (correct/incorrect)
 * @param string $mode Review mode (flashcard/quiz/matching/collocations/writing)
 * @return bool Success status
 */
function recordReviewActivity($wordId, $result, $mode) {
    $vocabulary = json_decode(file_get_contents(VOCAB_FILE), true) ?? [];
    $found = false;
    $today = date('Y-m-d');
    
    foreach ($vocabulary as $key => $item) {
        if (isset($item['id']) && $item['id'] == $wordId) {
            // Add review record
            $vocabulary[$key]['review_history'][] = [
                'date' => $today,
                'result' => $result,
                'mode' => $mode
            ];
            
            // Update last reviewed date
            $vocabulary[$key]['last_reviewed'] = $today;
            
            // Update SRS level based on result
            if ($result === 'correct') {
                $vocabulary[$key]['srs_level'] = min(5, ($item['srs_level'] ?? 0) + 1);
            } else {
                $vocabulary[$key]['srs_level'] = max(0, ($item['srs_level'] ?? 0) - 1);
            }
            
            // Calculate next review date
            $vocabulary[$key]['srs_interval'] = calculateSRSInterval($vocabulary[$key]['srs_level']);
            $vocabulary[$key]['next_review'] = calculateNextReviewDate($vocabulary[$key]['srs_interval']);
            
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        return false;
    }
    
    // Update vocabulary data
    $success = saveVocabulary($vocabulary);
    
    // Also update user stats
    if ($success) {
        updateUserStats($result, $mode);
    }
    
    return $success;
}

/**
 * Calculate SRS interval based on level
 * 
 * @param int $level SRS level (0-5)
 * @return int Days until next review
 */
function calculateSRSInterval($level) {
    switch ($level) {
        case 0: return 1;  // Review again tomorrow
        case 1: return 2;  // Review in 2 days
        case 2: return 4;  // Review in 4 days
        case 3: return 7;  // Review in 1 week
        case 4: return 14; // Review in 2 weeks
        case 5: return 30; // Review in 1 month
        default: return 1;
    }
}

/**
 * Calculate next review date based on interval
 * 
 * @param int $interval Days until next review
 * @return string Next review date (Y-m-d)
 */
function calculateNextReviewDate($interval) {
    return date('Y-m-d', strtotime("+{$interval} days"));
}

/**
 * Function to update user statistics
 * 
 * @param string $result Review result (correct/incorrect)
 * @param string $mode Review mode
 * @return bool Success status
 */
function updateUserStats($result, $mode) {
    $userId = getCurrentUserId();
    if ($userId === null) {
        return false; // Not logged in
    }
    
    initializeDataStructure();
    
    $jsonData = file_get_contents(STATS_FILE);
    $stats = json_decode($jsonData, true);
    
    if ($stats === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON Error: ' . json_last_error_msg());
        $stats = [];
    }
    
    $userKey = "user_{$userId}";
    $today = date('Y-m-d');
    
    // Initialize user stats if not exists
    if (!isset($stats[$userKey])) {
        $stats[$userKey] = [
            'words_learned' => 0,
            'quiz_accuracy' => 0,
            'study_time' => 0,
            'daily_activity' => [],
            'ielts_progress' => [
                'band_5' => 0,
                'band_6' => 0,
                'band_7' => 0,
                'band_8' => 0
            ]
        ];
    }
    
    // Update accuracy stats by mode
    if (!isset($stats[$userKey]['accuracy'])) {
        $stats[$userKey]['accuracy'] = [];
    }
    
    if (!isset($stats[$userKey]['accuracy'][$mode])) {
        $stats[$userKey]['accuracy'][$mode] = [
            'correct' => 0,
            'incorrect' => 0
        ];
    }
    
    if ($result === 'correct') {
        $stats[$userKey]['accuracy'][$mode]['correct']++;
    } else {
        $stats[$userKey]['accuracy'][$mode]['incorrect']++;
    }
    
    // Update overall accuracy for quiz mode
    if ($mode === 'quiz') {
        $totalQuizReviews = $stats[$userKey]['accuracy']['quiz']['correct'] + $stats[$userKey]['accuracy']['quiz']['incorrect'];
        $stats[$userKey]['quiz_accuracy'] = ($stats[$userKey]['accuracy']['quiz']['correct'] / $totalQuizReviews) * 100;
    }
    
    // Update daily activity
    $found = false;
    foreach ($stats[$userKey]['daily_activity'] as $key => $activity) {
        if ($activity['date'] === $today) {
            $stats[$userKey]['daily_activity'][$key]['words_studied']++;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $stats[$userKey]['daily_activity'][] = [
            'date' => $today,
            'words_studied' => 1
        ];
    }
    
    // If result is correct and SRS level is high, count as "learned"
    if ($result === 'correct') {
        $vocabulary = json_decode(file_get_contents(VOCAB_FILE), true) ?? [];
        foreach ($vocabulary as $item) {
            if (isset($item['id']) && $item['id'] == $_POST['word_id'] && isset($item['srs_level']) && $item['srs_level'] >= 4) {
                $stats[$userKey]['words_learned']++;
                
                // Update IELTS band progress
                if (isset($item['ielts_band'])) {
                    $band = (int)$item['ielts_band'];
                    if ($band >= 8) {
                        $stats[$userKey]['ielts_progress']['band_8']++;
                    } elseif ($band >= 7) {
                        $stats[$userKey]['ielts_progress']['band_7']++;
                    } elseif ($band >= 6) {
                        $stats[$userKey]['ielts_progress']['band_6']++;
                    } else {
                        $stats[$userKey]['ielts_progress']['band_5']++;
                    }
                }
                
                break;
            }
        }
    }
    
    $jsonData = json_encode($stats, JSON_PRETTY_PRINT);
    return file_put_contents(STATS_FILE, $jsonData) !== false;
}

/**
 * Function to get due words for review based on SRS
 * 
 * @param int $limit Maximum number of words to return
 * @return array Due words for review
 */
function getDueWordsForReview($limit = 10) {
    $vocabulary = json_decode(file_get_contents(VOCAB_FILE), true) ?? [];
    $today = date('Y-m-d');
    $dueWords = [];
    
    foreach ($vocabulary as $item) {
        if (isset($item['next_review']) && $item['next_review'] <= $today) {
            $dueWords[] = $item;
        }
    }
    
    // Sort by SRS level (lowest first) and next review date (oldest first)
    usort($dueWords, function($a, $b) {
        $levelComp = ($a['srs_level'] ?? 0) - ($b['srs_level'] ?? 0);
        if ($levelComp !== 0) {
            return $levelComp;
        }
        return strcmp($a['next_review'] ?? '', $b['next_review'] ?? '');
    });
    
    // Limit the number of words
    return array_slice($dueWords, 0, $limit);
}

/**
 * Function to handle audio file upload
 * 
 * @param array $file Uploaded file ($_FILES array entry)
 * @param string $wordId Word ID
 * @param string $language Language code (en/vi)
 * @return string|false Filename on success, false on failure
 */
function handleAudioUpload($file, $wordId, $language) {
    initializeDataStructure();
    
    // Check if file is a valid audio file
    $allowedTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg'];
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    // Generate filename
    $filename = $language . '_' . $wordId . '_' . time() . '.mp3';
    $targetPath = AUDIO_DIR . '/' . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $filename;
    }
    
    return false;
}

/**
 * Import vocabulary from CSV
 * 
 * @param string $csvFile Path to CSV file
 * @return array Result with status and count
 */
function importVocabularyFromCSV($csvFile) {
    if (!file_exists($csvFile)) {
        return ['status' => false, 'message' => 'File not found'];
    }
    
    $handle = fopen($csvFile, 'r');
    if (!$handle) {
        return ['status' => false, 'message' => 'Could not open file'];
    }
    
    $header = fgetcsv($handle);
    $requiredColumns = ['english', 'vietnamese'];
    
    // Check if required columns exist
    foreach ($requiredColumns as $column) {
        if (!in_array($column, $header)) {
            fclose($handle);
            return ['status' => false, 'message' => "Missing required column: {$column}"];
        }
    }
    
    // Map column indices
    $columnMap = array_flip($header);
    
    // Get existing vocabulary
    $vocabulary = json_decode(file_get_contents(VOCAB_FILE), true) ?? [];
    $imported = 0;
    
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < count($header)) {
            continue; // Skip rows with insufficient columns
        }
        
        $entry = [
            'english' => $row[$columnMap['english']],
            'vietnamese' => $row[$columnMap['vietnamese']],
            'context' => isset($columnMap['context']) ? $row[$columnMap['context']] : '',
            'synonyms' => isset($columnMap['synonyms']) ? $row[$columnMap['synonyms']] : '',
            'antonyms' => isset($columnMap['antonyms']) ? $row[$columnMap['antonyms']] : '',
            'ielts_band' => isset($columnMap['ielts_band']) ? $row[$columnMap['ielts_band']] : '6',
            'collocations' => isset($columnMap['collocations']) ? $row[$columnMap['collocations']] : '',
            'ielts_usage' => isset($columnMap['ielts_usage']) ? $row[$columnMap['ielts_usage']] : '',
            'difficulty' => isset($columnMap['difficulty']) ? $row[$columnMap['difficulty']] : 'medium'
        ];
        
        // Add category if exists
        if (isset($columnMap['category'])) {
            $entry['category'] = explode(',', $row[$columnMap['category']]);
        }
        
        // Add examples if exists
        if (isset($columnMap['examples'])) {
            $entry['examples'] = $row[$columnMap['examples']];
        }
        
        if (saveVocabularyEntry($entry)) {
            $imported++;
        }
    }
    
    fclose($handle);
    
    return [
        'status' => true,
        'message' => "Successfully imported {$imported} vocabulary entries",
        'count' => $imported
    ];
}

/**
 * Export vocabulary to CSV
 * 
 * @param string $filePath Path to save CSV file
 * @param array $filters Optional filters
 * @return bool Success status
 */
function exportVocabularyToCSV($filePath, $filters = []) {
    $vocabulary = getVocabulary($filters)['data'];
    
    $handle = fopen($filePath, 'w');
    if (!$handle) {
        return false;
    }
    
    // Write header
    $header = ['english', 'vietnamese', 'context', 'examples', 'synonyms', 'antonyms', 'collocations', 'ielts_band', 'ielts_usage', 'category', 'difficulty'];
    fputcsv($handle, $header);
    
    // Write data
    foreach ($vocabulary as $item) {
        $row = [
            $item['english'],
            $item['vietnamese'],
            $item['context'] ?? '',
            is_array($item['examples'] ?? null) ? implode("\n", $item['examples']) : '',
            is_array($item['synonyms'] ?? null) ? implode(', ', $item['synonyms']) : '',
            is_array($item['antonyms'] ?? null) ? implode(', ', $item['antonyms']) : '',
            is_array($item['collocations'] ?? null) ? implode(', ', $item['collocations']) : '',
            $item['ielts_band'] ?? '6',
            $item['ielts_usage'] ?? '',
            is_array($item['category'] ?? null) ? implode(', ', $item['category']) : '',
            $item['difficulty'] ?? 'medium'
        ];
        
        fputcsv($handle, $row);
    }
    
    fclose($handle);
    
    return true;
}

/**
 * Get user statistics
 * 
 * @param int|null $userId User ID (current user if null)
 * @return array User statistics
 */
function getUserStatistics($userId = null) {
    if ($userId === null) {
        $userId = getCurrentUserId();
    }
    
    if ($userId === null) {
        return []; // Not logged in
    }
    
    $jsonData = file_get_contents(STATS_FILE);
    $stats = json_decode($jsonData, true);
    
    if ($stats === null && json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON Error: ' . json_last_error_msg());
        return [];
    }
    
    $userKey = "user_{$userId}";
    
    return isset($stats[$userKey]) ? $stats[$userKey] : [];
}

/**
 * Get IELTS readiness statistics for a user
 * 
 * @param int $userId User ID
 * @return array IELTS readiness data
 */
function getIELTSReadiness($userId) {
    $vocabulary = json_decode(file_get_contents(VOCAB_FILE), true) ?? [];
    $user = getUserById($userId);
    $targetBand = $user['settings']['target_ielts_band'] ?? 7.0;
    
    // Filter vocabulary for this user
    $userVocab = array_filter($vocabulary, function($item) use ($userId) {
        return !isset($item['user_id']) || $item['user_id'] == $userId;
    });
    
    // Count total words
    $totalWords = count($userVocab);
    if ($totalWords === 0) {
        return [
            'overall' => 0,
            'by_band' => [5 => 0, 6 => 0, 7 => 0, 8 => 0],
            'mastered' => 0,
            'learning' => 0,
            'due' => 0
        ];
    }
    
    // Count words by IELTS band
    $bandCounts = [5 => 0, 6 => 0, 7 => 0, 8 => 0];
    $masteredCount = 0;
    $learningCount = 0;
    $dueCount = 0;
    $today = date('Y-m-d');
    
    foreach ($userVocab as $word) {
        // Count by IELTS band
        $band = isset($word['ielts_band']) ? (int)$word['ielts_band'] : 6;
        if ($band >= 8) {
            $bandCounts[8]++;
        } elseif ($band >= 7) {
            $bandCounts[7]++;
        } elseif ($band >= 6) {
            $bandCounts[6]++;
        } else {
            $bandCounts[5]++;
        }
        
        // Count by mastery level
        if (isset($word['srs_level']) && $word['srs_level'] >= 4) {
            $masteredCount++;
        } else {
            $learningCount++;
        }
        
        // Count due words
        if (isset($word['next_review']) && $word['next_review'] <= $today) {
            $dueCount++;
        }
    }
    
    // Calculate overall readiness based on target band
    $weightedSum = 0;
    
    if ($targetBand <= 5.5) {
        $weightedSum = $bandCounts[5] * 1.0;
    } elseif ($targetBand <= 6.5) {
        $weightedSum = $bandCounts[5] * 0.3 + $bandCounts[6] * 1.0;
    } elseif ($targetBand <= 7.5) {
        $weightedSum = $bandCounts[5] * 0.1 + $bandCounts[6] * 0.4 + $bandCounts[7] * 1.0;
    } else {
        $weightedSum = $bandCounts[5] * 0.1 + $bandCounts[6] * 0.2 + $bandCounts[7] * 0.5 + $bandCounts[8] * 1.0;
    }
    
    // Target number of words for each band level
    $targetWords = [
        5 => 1000, 
        6 => 1500, 
        7 => 2000, 
        8 => 1000
    ];
    
    // Calculate overall readiness percentage
    $overall = 0;
    if ($targetBand <= 5.5) {
        $overall = min(100, ($bandCounts[5] / $targetWords[5]) * 100);
    } elseif ($targetBand <= 6.5) {
        $overall = min(100, (($bandCounts[5] + $bandCounts[6]) / ($targetWords[5] + $targetWords[6])) * 100);
    } elseif ($targetBand <= 7.5) {
        $overall = min(100, (($bandCounts[5] + $bandCounts[6] + $bandCounts[7]) / ($targetWords[5] + $targetWords[6] + $targetWords[7])) * 100);
    } else {
        $overall = min(100, (($bandCounts[5] + $bandCounts[6] + $bandCounts[7] + $bandCounts[8]) / ($targetWords[5] + $targetWords[6] + $targetWords[7] + $targetWords[8])) * 100);
    }
    
    // Take mastery into account
    $masteryFactor = $masteredCount / $totalWords;
    $overall = $overall * (0.3 + 0.7 * $masteryFactor);
    
    return [
        'overall' => round($overall),
        'by_band' => $bandCounts,
        'mastered' => $masteredCount,
        'learning' => $learningCount,
        'due' => $dueCount
    ];
}

/**
 * Handle delete action with AJAX support
 */
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    $result = deleteVocabularyEntry($deleteId);
    
    // Check if it's an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
        exit;
    }
    
    // Regular form submission
    header('Location: index.php');
    exit;
}