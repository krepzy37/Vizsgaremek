<?php 
//munkamenet hozzáadása
session_start();

if (isset($_SESSION['id'])) {
    include_once "connect.php";

    $id = $_SESSION['id'];
    $status = "Offline";

    // A felhasználó státuszának frissítése
    $sql = mysqli_query($dbconn, "UPDATE users SET status = '$status' WHERE id = $id");

    if ($sql) {
        // Munkamenet lezárása
        session_unset();
        session_destroy();
        header("location: ../index.php");
    }
} else {
    header("location: ../login.php");
}
?>

