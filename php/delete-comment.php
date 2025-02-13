<?php
session_start();
include 'connect.php'; // Csatlakozás az adatbázishoz

// Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['id'])) {
    header("Location: login.php"); // Ha nincs bejelentkezve, irányítsuk a login oldalra
    exit;
}

// Ellenőrizzük, hogy meg van-e adva a komment ID
if (isset($_GET['comment_id']) && is_numeric($_GET['comment_id'])) {
    $comment_id = $_GET['comment_id'];

    // Előkészítjük a törlési SQL lekérdezést
    $query = "DELETE FROM comments WHERE id = ?";
    if ($stmt = mysqli_prepare($dbconn, $query)) {
        // Paraméterek bindolása
        mysqli_stmt_bind_param($stmt, 'i', $comment_id);

        // Lekérdezés végrehajtása
        if (mysqli_stmt_execute($stmt)) {
            // Törlés sikeres, visszairányítjuk a főoldalra
            header("Location: " . $_SERVER['HTTP_REFERER']); // Vissza arra az oldalra, ahol a törlés történt
            exit;
        } else {
            // Ha hiba történik
            echo "Hiba történt a komment törlésekor!";
        }
    } else {
        // Ha a lekérdezés nem sikerült
        echo "Hiba történt a lekérdezés előkészítésekor!";
    }
} else {
    // Ha nincs megadva komment ID
    echo "Érvénytelen komment ID!";
}

mysqli_close($dbconn); // Adatbázis kapcsolat lezárása
?>
