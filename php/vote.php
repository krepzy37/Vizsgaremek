<?php
session_start();
include 'connect.php';

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Nincs bejelentkezve']);
    exit;
}

$user_id = $_SESSION['id']; // Logged-in user ID
$type = $_POST['type'] ?? null; // "post" or "comment"
$vote_type = $_POST['vote_type'] ?? null; // upvote or downvote
$id = isset($_POST['id']) ? intval($_POST['id']) : null;

// Check for required parameters
if (!$type || !$vote_type || !$id) {
    echo json_encode(['success' => false, 'message' => 'Hiányzó paraméterek']);
    exit;
}

// Set post_id and comment_id based on the type
$post_id = ($type === "post") ? $id : null;
$comment_id = ($type === "comment") ? $id : null;

// Check if the user has already voted
$query = "SELECT * FROM votes WHERE user_id = ? AND post_id <=> ? AND comment_id <=> ?";
$stmt = $dbconn->prepare($query);
$stmt->bind_param("iii", $user_id, $post_id, $comment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // If the user has already voted, check if it's the same vote
    $existing_vote = $result->fetch_assoc();
    
    if ($existing_vote['vote_type'] === $vote_type) {
        // If it's the same vote, delete the vote
        $delete_query = "DELETE FROM votes WHERE user_id = ? AND post_id <=> ? AND comment_id <=> ?";
        $delete_stmt = $dbconn->prepare($delete_query);
        $delete_stmt->bind_param("iii", $user_id, $post_id, $comment_id);
        
        if ($delete_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Szavazat törölve!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Hiba történt a szavazat törlésében']);
        }
    } else {
        // If it's a different vote type, update the vote
        $update_query = "UPDATE votes SET vote_type = ? WHERE user_id = ? AND post_id <=> ? AND comment_id <=> ?";
        $update_stmt = $dbconn->prepare($update_query);
        $update_stmt->bind_param("siii", $vote_type, $user_id, $post_id, $comment_id);
        
        if ($update_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Szavazat frissítve!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Hiba történt a szavazat frissítésében']);
        }
    }
} else {
    // If the user hasn't voted yet, save the vote
    $query = "INSERT INTO votes (user_id, post_id, comment_id, vote_type, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $dbconn->prepare($query);
    $stmt->bind_param("iiis", $user_id, $post_id, $comment_id, $vote_type);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Szavazat mentve!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Hiba történt a szavazat mentésekor']);
    }
}
?>