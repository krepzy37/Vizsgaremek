<?php
session_start();
include 'connect.php';

if (isset($_GET['post_id']) && isset($_SESSION['id'])) {
    $post_id = intval($_GET['post_id']);
    $user_id = $_SESSION['id']; // Bejelentkezett felhasználó azonosítója

    // Lekérdezzük a felhasználó szerepét
    $roleQuery = "SELECT role FROM users WHERE id = ?";
    $stmt = $dbconn->prepare($roleQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $role = $user['role'];

        if ($role === 'Moderator') {
            // Ha moderátor, akkor archiválás
            $updateQuery = "UPDATE posts SET status = 'Archived' WHERE id = ?";
            $stmt = $dbconn->prepare($updateQuery);
            $stmt->bind_param("i", $post_id);
            $stmt->execute();
            
            $logQuery = "INSERT INTO admin_logs (admin_user_id, target_post_id) VALUES (?, ?)";
            $stmt = $dbconn->prepare($logQuery);
            $stmt->bind_param("ii", $user_id, $post_id);
            $stmt->execute();
        } else {
            // Ha nem moderátor, akkor teljes törlés
            $deleteQuery = "DELETE FROM posts WHERE id = ?";
            $stmt = $dbconn->prepare($deleteQuery);
            $stmt->bind_param("i", $post_id);
            $stmt->execute();
        }
    }

    // Honnan jött a felhasználó?
    $redirect = isset($_GET['redirect']) ? urldecode($_GET['redirect']) : '../index.php';

    // Visszairányítás az eredeti oldalra
    header("Location: $redirect");
    exit();
}

// Ha nincs post_id vagy a felhasználó nincs bejelentkezve, visszairányítás a főoldalra
header("Location: ../index.php");
exit();
