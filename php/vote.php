<?php
session_start();
require 'connect.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Be kell jelentkezned a szavazáshoz.']);
    exit;
}

$user_id = $_SESSION['id'];
$type = $_POST['type']; // 'post' vagy 'comment'
$id = intval($_POST['id']);
$vote_type = $_POST['vote_type']; // 'upvote' vagy 'downvote'

if (!in_array($vote_type, ['upvote', 'downvote'])) {
    echo json_encode(['status' => 'error', 'message' => 'Érvénytelen szavazási típus.']);
    exit;
}

// Megnézzük, hogy van-e már szavazat erre az elemre
$column = ($type === 'post') ? 'post_id' : 'comment_id';
$checkVoteQuery = "SELECT * FROM votes WHERE user_id = ? AND $column = ?";
$stmt = $dbconn->prepare($checkVoteQuery);
$stmt->bind_param("ii", $user_id, $id);
$stmt->execute();
$result = $stmt->get_result();
$existingVote = $result->fetch_assoc();

if ($existingVote) {
    if ($existingVote['vote_type'] === $vote_type) {
        // Ha ugyanazt a szavazatot adjuk le, töröljük
        $deleteVoteQuery = "DELETE FROM votes WHERE user_id = ? AND $column = ?";
        $stmt = $dbconn->prepare($deleteVoteQuery);
        $stmt->bind_param("ii", $user_id, $id);
        $stmt->execute();
        echo json_encode(['status' => 'removed']);
    } else {
        // Ha másik szavazatot adunk le, módosítjuk
        $updateVoteQuery = "UPDATE votes SET vote_type = ? WHERE user_id = ? AND $column = ?";
        $stmt = $dbconn->prepare($updateVoteQuery);
        $stmt->bind_param("sii", $vote_type, $user_id, $id);
        $stmt->execute();
        echo json_encode(['status' => 'changed', 'new_vote' => $vote_type]);
    }
} else {
    // Ha még nincs szavazat, beszúrjuk
    $insertVoteQuery = "INSERT INTO votes (user_id, $column, vote_type) VALUES (?, ?, ?)";
    $stmt = $dbconn->prepare($insertVoteQuery);
    $stmt->bind_param("iis", $user_id, $id, $vote_type);
    $stmt->execute();
    echo json_encode(['status' => 'added', 'vote' => $vote_type]);
}

exit;
