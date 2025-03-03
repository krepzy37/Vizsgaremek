<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Nincs bejelentkezve']);
    exit;
}

$user_id = $_SESSION['id'];

// Lekérdezzük az összes szavazatot (posztok és kommentek egyaránt)
$query = "SELECT post_id, comment_id, vote_type FROM votes WHERE user_id = ?";
$stmt = $dbconn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$votes = [];
while ($row = $result->fetch_assoc()) {
    $votes[] = $row;
}

echo json_encode(['success' => true, 'votes' => $votes]);
?>
