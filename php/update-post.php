<?php
session_start();
require_once "connect.php";
header("Content-Type: application/json");

if (!isset($_SESSION['id'])) {
    echo json_encode(["success" => false, "message" => "Be kell jelentkezned!"]);
    exit;
}

if (isset($_POST['post_id']) && is_numeric($_POST['post_id'])) {
    $post_id = $_POST['post_id'];
    $title = $_POST['title'];
    $body = $_POST['body'];
    $user_id = $_SESSION['id'];
    $remove_image = isset($_POST['remove_image']) ? $_POST['remove_image'] === 'true' : false;

    // Poszt lekérése és ellenőrzése
    $query = "SELECT user_id, post_image_url FROM posts WHERE id = ?";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $post = mysqli_fetch_assoc($result);

    if (!$post || $post['user_id'] != $user_id) {
        echo json_encode(["success" => false, "message" => "Nincs jogosultságod a szerkesztéshez!"]);
        exit;
    }

    $image_name = $post['post_image_url'];

    // Kép törlése, ha kérték
    if ($remove_image && !empty($post['post_image_url'])) {
        $image_path = '../php/img/' . $post['post_image_url'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
        $image_name = NULL;
    }

    // Új kép feltöltése, ha van
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_tmp = $_FILES['image']['tmp_name'];
        $new_image_name = basename($_FILES['image']['name']);
        $image_dir = '../php/img/' . $new_image_name;

        if (move_uploaded_file($image_tmp, $image_dir)) {
            // Régi kép törlése, ha van és nem azonos az újjal
            if (!empty($post['post_image_url']) && $post['post_image_url'] !== $new_image_name) {
                $old_image_path = '../php/img/' . $post['post_image_url'];
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
            $image_name = $new_image_name;
        }
    }

    // Adatbázis frissítése
    $query = "UPDATE posts SET title = ?, body = ?, post_image_url = ? WHERE id = ?";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, 'sssi', $title, $body, $image_name, $post_id);
    mysqli_stmt_execute($stmt);

    echo json_encode(["success" => true, "message" => "A poszt sikeresen frissítve!"]);
} else {
    echo json_encode(["success" => false, "message" => "Érvénytelen kérés!"]);
}