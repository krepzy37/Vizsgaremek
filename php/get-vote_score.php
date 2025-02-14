<?php
session_start();
include 'connect.php';

$type = $_POST['type'] ?? null; // "post" or "comment"
$id = isset($_POST['id']) ? intval($_POST['id']) : null;

// Check for required parameters
if (!$type || !$id) {
    echo json_encode(['success' => false, 'message' => 'Hiányzó paraméterek']);
    exit;
}

// Prepare the query based on the type
if ($type === 'post') {
    $query = "SELECT SUM(CASE WHEN vote_type = 'upvote' THEN 1 ELSE -1 END) as score FROM votes WHERE post_id = ?";
} else {
    $query = "SELECT SUM(CASE WHEN vote_type = 'upvote' THEN 1 ELSE -1 END) as score FROM votes WHERE comment_id = ?";
}

$stmt = $dbconn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$vote_result = $stmt->get_result()->fetch_assoc();

// Set score to 0 if there are no votes
$score = $vote_result['score'] !== null ? $vote_result['score'] : 0;

echo json_encode(['success' => true, 'score' => $score]);
?>