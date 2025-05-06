<?php
/**
 * Data handling functions for IELTS Study Tracker
 */

/**
 * Get all scores
 * 
 * @return array Array of scores
 */
function getScores() {
    $scoresFile = 'data/scores.json';
    
    if (file_exists($scoresFile)) {
        $scores = json_decode(file_get_contents($scoresFile), true);
        return is_array($scores) ? $scores : [];
    }
    
    return [];
}

/**
 * Add a new score
 * 
 * @param array $scoreData Score data to add
 * @return bool True if successful
 */
function addScore($scoreData) {
    $scores = getScores();
    $scores[] = $scoreData;
    
    return file_put_contents('data/scores.json', json_encode($scores, JSON_PRETTY_PRINT));
}

/**
 * Delete a score by index
 * 
 * @param int $index Index of the score to delete
 * @return bool True if successful
 */
function deleteScore($index) {
    $scores = getScores();
    
    if (isset($scores[$index])) {
        array_splice($scores, $index, 1);
        return file_put_contents('data/scores.json', json_encode($scores, JSON_PRETTY_PRINT));
    }
    
    return false;
}

/**
 * Get all works for a skill
 * 
 * @param string $skill Skill type (writing, speaking, reading, listening)
 * @return array Array of works
 */
function getWorks($skill) {
    $worksDir = "data/works/{$skill}";
    $works = [];
    
    if (!file_exists($worksDir)) {
        mkdir($worksDir, 0777, true);
        return $works;
    }
    
    $files = glob("{$worksDir}/*.json");
    
    foreach ($files as $file) {
        $work = json_decode(file_get_contents($file), true);
        if ($work) {
            // Add the file ID as part of the work data
            $work['id'] = basename($file, '.json');
            $works[] = $work;
        }
    }
    
    // Sort works by date (most recent first)
    usort($works, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    return $works;
}

/**
 * Add a new work
 * 
 * @param array $workData Work data to add
 * @param string $skill Skill type (writing, speaking, reading, listening)
 * @return bool True if successful
 */
function addWork($workData, $skill) {
    $worksDir = "data/works/{$skill}";
    
    if (!file_exists($worksDir)) {
        mkdir($worksDir, 0777, true);
    }
    
    $id = generateUniqueId();
    $filename = "{$worksDir}/{$id}.json";
    
    return file_put_contents($filename, json_encode($workData, JSON_PRETTY_PRINT));
}

/**
 * Delete a work by ID
 * 
 * @param string $id ID of the work to delete
 * @param string $skill Skill type (writing, speaking, reading, listening)
 * @return bool True if successful
 */
function deleteWork($id, $skill) {
    $filename = "data/works/{$skill}/{$id}.json";
    
    if (file_exists($filename)) {
        return unlink($filename);
    }
    
    return false;
}

/**
 * Get word statistics for a skill
 * 
 * @param string $skill Skill type (writing, speaking, reading, listening)
 * @return array Word statistics
 */
function getWordStatistics($skill) {
    $statsFile = "data/statistics/{$skill}-words.json";
    
    if (file_exists($statsFile)) {
        $stats = json_decode(file_get_contents($statsFile), true);
        return is_array($stats) ? $stats : [];
    }
    
    return [];
}