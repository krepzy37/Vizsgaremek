<?php
require '../php/connect.php'; // Az adatbázis kapcsolatot betöltjük

if (isset($_GET['id'])) {
    $post_id = intval($_GET['id']);

    $sql = "UPDATE posts SET status = 'Active' WHERE id = ?";
    $stmt = $dbconn->prepare($sql);
    $stmt->bind_param("i", $post_id);

    if ($stmt->execute()) {
        header("Location: moderator.php?message=Post restored successfully");
        exit();
    } else {
        echo "Hiba a visszaállítás során: " . $dbconn->error;
    }
} else {
    echo "Érvénytelen kérés.";
}
?>
