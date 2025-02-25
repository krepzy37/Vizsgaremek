<?php
require '../php/connect.php';

if (isset($_GET['id'])) {
    $comment_id = intval($_GET['id']);

    $sql = "UPDATE comments SET status = 'Active' WHERE id = ?";
    $stmt = $dbconn->prepare($sql);
    $stmt->bind_param("i", $comment_id);

    if ($stmt->execute()) {
        header("Location: moderator.php?message=Comment restored successfully");
        exit();
    } else {
        echo "Hiba a visszaállítás során: " . $dbconn->error;
    }
} else {
    echo "Érvénytelen kérés.";
}
?>
