<?php 
session_start();
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Komment szöveg
    $comment_text = mysqli_real_escape_string($dbconn, $_POST['comment_text']);
    $post_id = (int) $_POST['post_id'];

    // Feltöltéshez szükséges változók
    $comment_image = null;
    
    if (isset($_FILES['comment_image']) && $_FILES['comment_image']['error'] == 0) {
        $image_name = $_FILES['comment_image']['name'];
        $image_tmp = $_FILES['comment_image']['tmp_name'];
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);

        // Ellenőrizzük a fájl kiterjesztését és feltöltjük a képet
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($image_ext), $allowed_extensions)) {
            // Kép egyedi nevének generálása
            $new_image_name = uniqid() . '.' . $image_ext;
            $upload_dir = 'img/';
            
            // Ellenőrizzük, hogy a fájl sikeresen lett feltöltve
            if (move_uploaded_file($image_tmp, $upload_dir . $new_image_name)) {
                $comment_image = $new_image_name; // Sikeres feltöltés, mentsük el az új fájl nevét
            } else {
                echo json_encode(['success' => false, 'message' => 'Hiba történt a kép feltöltésekor']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Nem támogatott képformátum.']);
            exit;
        }
    }

    // SQL beszúrás a kommentbe
    $user_id = $_SESSION['id'];  // A felhasználó azonosítója a session-ból
    /*$status = 'Active'; // Alapértelmezett státusz*/
    $query = "INSERT INTO comments (post_id, user_id, body, comment_image_url) 
              VALUES ($post_id, $user_id, '$comment_text', '$comment_image')";
    
    if (mysqli_query($dbconn, $query)) {
        $response = [
            'success' => true,
            'message' => 'Komment sikeresen hozzáadva!'
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Hiba történt a komment hozzáadása közben.'
        ];
    }

    echo json_encode($response);
}
?>
