<?php
session_start();
include 'connect.php';

if (isset($_GET['post_id'])) {
    $post_id = intval($_GET['post_id']);

    // Töröljük a posztot
    $deleteQuery = "DELETE FROM posts WHERE id = ?";
    $stmt = $dbconn->prepare($deleteQuery);
    $stmt->bind_param("i", $post_id);
    $stmt->execute();

    // Honnan jött a felhasználó?
    $redirect = isset($_GET['redirect']) ? urldecode($_GET['redirect']) : '../index.php';

    // Visszairányítás az eredeti oldalra
    header("Location: $redirect");
    exit();
}

// Ha nincs post_id vagy valami hiba történt, visszairányítás a főoldalra
header("Location: ../index.php");
exit();
