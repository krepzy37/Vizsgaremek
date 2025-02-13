<?php
session_start(); // session indítása
include 'connect.php'; // adatbázis csatlakozás

// Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Kérlek, jelentkezz be!']);
    exit;
}

// Kép feltöltése
$image_url = NULL; // Alapértelmezett, ha nincs kép
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    // Kép fájl információk
    $image_name = basename($_FILES['image']['name']);
    $image_type = $_FILES['image']['type'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_size = $_FILES['image']['size'];

    // Ellenőrizzük, hogy a fájl típus megfelelő (pl. jpeg, png)
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (in_array($image_type, $allowed_types)) {
        // Új fájl név generálása (előzzük meg az ütközést)
        $new_image_name = uniqid('post_', true) . '.' . pathinfo($image_name, PATHINFO_EXTENSION);
        $upload_dir = 'img/';
        
        // A fájl feltöltése
        if (move_uploaded_file($image_tmp_name, $upload_dir . $new_image_name)) {
            $image_url = $new_image_name; // A feltöltött kép URL-je
        } else {
            echo json_encode(['success' => false, 'message' => 'Hiba a kép feltöltése során!']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Csak képek tölthetők fel!']);
        exit;
    }
}

// Adatok biztonságos mentése változókba
$title = mysqli_real_escape_string($dbconn, $_POST['title']);
$body = mysqli_real_escape_string($dbconn, $_POST['body']);
$car_id = $_POST['car_id'];
$user_id = $_SESSION['id']; // Bejelentkezett felhasználó ID-ja
$status = 'Active'; // Alapértelmezett státusz

// SQL lekérdezés a poszt hozzáadására
$query = "INSERT INTO posts (title, body, user_id, car_id, status, post_image_url) 
          VALUES ('$title', '$body', $user_id, $car_id, '$status', '$image_url')";

if (mysqli_query($dbconn, $query)) {
    echo json_encode(['success' => true, 'message' => 'Poszt sikeresen hozzáadva!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Hiba történt a poszt hozzáadása során: ' . mysqli_error($dbconn)]);
}

?>
