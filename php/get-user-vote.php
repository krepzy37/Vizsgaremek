<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Nincs bejelentkezve']);
    exit;
}

$type = $_POST['type'] ?? null; // "post" or "comment"
$id = isset($_POST['id']) ? intval($_POST['id']) : null;

if (!$type || !$id) {
    echo json_encode(['success' => false, 'message' => 'Hiányzó paraméterek']);
    exit;
}

$query = $type === 'post' ? "SELECT vote_type FROM votes WHERE user_id = ? AND post_id = ?" : "SELECT vote_type FROM votes WHERE user_id = ? AND comment_id = ?";
$stmt = $dbconn->prepare($query);
$stmt->bind_param("ii", $_SESSION['id'], $id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

$vote_type = $result ? $result['vote_type'] : null;

echo json_encode(['success' => true, 'vote_type' => $vote_type]);