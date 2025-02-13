<?php
include 'connect.php';

if (isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];

    // Post adatainak lekérdezése
    $query = "SELECT * FROM posts WHERE id = ?";
    $stmt = $dbconn->prepare($query);
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
        echo json_encode(['success' => true, 'post' => $post]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Post not found.']);
    }
}
?>
