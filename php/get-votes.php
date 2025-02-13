<?php
include 'connect.php';

$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : null;
$comment_id = isset($_GET['comment_id']) ? intval($_GET['comment_id']) : null;

$query = "SELECT SUM(CASE WHEN vote_type = 'up' THEN 1 ELSE -1 END) as score 
          FROM votes WHERE post_id = ? OR comment_id = ?";
$stmt = $dbconn->prepare($query);
$stmt->bind_param("ii", $post_id, $comment_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode(["score" => $result['score'] ?? 0]);
?>
