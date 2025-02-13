<?php
session_start();
include 'connect.php'; // Adatbázis kapcsolat

if (!isset($_SESSION['id'])) {
    echo json_encode(["success" => false, "message" => "Nincs bejelentkezve!"]);
    exit;
}

if (isset($_POST['comment_id']) && is_numeric($_POST['comment_id'])) {
    $comment_id = $_POST['comment_id'];
    $comment_text = $_POST['comment_text'];
    $user_id = $_SESSION['id'];
    $delete_image = isset($_POST['delete_comment_image']); // Checkbox értékének kezelése

    // Komment ellenőrzése
    $query = "SELECT user_id, comment_image_url FROM comments WHERE id = ?";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $comment_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $comment = mysqli_fetch_assoc($result);

    if (!$comment || $comment['user_id'] != $user_id) {
        echo json_encode(["success" => false, "message" => "Nincs jogosultságod a szerkesztéshez!"]);
        exit;
    }

    $image_name = $comment['comment_image_url'];

    // Ha a checkbox be van pipálva, töröljük a képet
    if ($delete_image && $image_name) {
        $image_path = '../php/img/' . $image_name;
        if (file_exists($image_path)) {
            unlink($image_path);
        }
        $image_name = null; // Az adatbázisból is töröljük a kép nevét
    }

    // Új kép feltöltése
    if (isset($_FILES['comment_image']) && $_FILES['comment_image']['error'] == 0) {
        $image_tmp = $_FILES['comment_image']['tmp_name'];
        $new_image_name = basename($_FILES['comment_image']['name']);
        $image_dir = '../php/img/' . $new_image_name;

        if (move_uploaded_file($image_tmp, $image_dir)) {
            $image_name = $new_image_name;
        } else {
            echo json_encode(["success" => false, "message" => "Kép feltöltési hiba!"]);
            exit;
        }
    }

    // Adatbázis frissítése a komment szövegével és a kép nevével (ha van változás)
    $query = "UPDATE comments SET body = ?, comment_image_url = ? WHERE id = ?";
    $stmt = mysqli_prepare($dbconn, $query);
    mysqli_stmt_bind_param($stmt, 'ssi', $comment_text, $image_name, $comment_id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["success" => true, "message" => "Komment frissítve!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Adatbázis hiba!"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Hiányzó adatok!"]);
}
?>
