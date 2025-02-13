<?php 
session_start();
include_once "connect.php";

if (!isset($_SESSION['id'])) {
    echo "Nincs bejelentkezve!";
    exit;
}

$user_id = $_SESSION['id'];

// Űrlapadatok beolvasása és tisztítása
$username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$passwordrow = $_POST['password'];

// Jelenlegi adatok lekérdezése
$sql = "SELECT password_hash, profile_picture_url FROM users WHERE id = ?";
$stmt = mysqli_prepare($dbconn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$row = mysqli_fetch_assoc($result)) {
    echo "Felhasználó nem található!";
    exit;
}

$existingPassword = $row['password_hash'];
$existingProfilePicture = $row['profile_picture_url'];

// Ha új jelszó van megadva, akkor titkosítjuk
$new_password_hash = $existingPassword; // Alapértelmezésben marad a régi jelszó

if (!empty($passwordrow)) {
    if (password_verify($passwordrow, $existingPassword)) {
        echo "Az új jelszó nem lehet ugyanaz, mint a régi!";
        exit;
    }
    $new_password_hash = password_hash($passwordrow, PASSWORD_DEFAULT);
}

// E-mail cím érvényességének ellenőrzése
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Érvénytelen e-mail cím formátum!";
    exit;
}

// Képfeltöltés kezelése
$profile_picture = $existingProfilePicture;

if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
    $img_name = $_FILES['image']['name'];
    $img_type = $_FILES['image']['type'];
    $tmp_name = $_FILES['image']['tmp_name'];

    $img_explode = explode('.', $img_name);
    $img_ext = strtolower(end($img_explode));
    $allowed_extensions = ['png', 'jpg', 'jpeg'];

    if (in_array($img_ext, $allowed_extensions)) {
        $new_img_name = time() . "_" . $img_name;
        if (move_uploaded_file($tmp_name, "img/" . $new_img_name)) {
            $profile_picture = $new_img_name;
        } else {
            echo "Hiba történt a képfeltöltés során!";
            exit;
        }
    } else {
        echo "Csak JPG, JPEG vagy PNG formátumú képeket lehet feltölteni!";
        exit;
    }
}

// Felhasználói adatok frissítése, beleértve a `username`-t is
$sql_update = "UPDATE users SET username = ?, email = ?, password_hash = ?, profile_picture_url = ? WHERE id = ?";
$stmt_update = mysqli_prepare($dbconn, $sql_update);
mysqli_stmt_bind_param($stmt_update, "ssssi", $username, $email, $new_password_hash, $profile_picture, $user_id);

if (mysqli_stmt_execute($stmt_update)) {
    echo "success";
} else {
    echo "Valami hiba történt!";
}
?>
