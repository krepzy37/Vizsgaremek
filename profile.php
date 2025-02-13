<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("location: login.php");
    exit;
}

include_once "php/connect.php";

// Felhasználói azonosító beolvasása a session-ből
$user_id = $_SESSION['id'];

// Saját profil adatainak lekérdezése
$sql = "SELECT id, username, email, role, status, profile_picture_url FROM users WHERE id = ?";
$stmt = mysqli_prepare($dbconn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Ellenőrzés, hogy van-e találat
if ($row = mysqli_fetch_assoc($result)) {
    $username = $row['username'];
    $email = $row['email'];
    $role = $row['role'];
    $status = $row['status'];
    $profile_picture = !empty($row['profile_picture_url']) ? "php/img/" . $row['profile_picture_url'] : "php/img/default.png";
} else {
    die("Felhasználó nem található.");
}

// Profil megjelenítése
$output = "
    <div class=\"profile_container\">
        <div class=\"profil_image\">
            <div class=\"img\"><img src=\"$profile_picture\" alt=\"Profilkép\"></div>
        </div>
        <div class=\"user_details\">
            <table>
                <tr>
                    <th>Felhasználónév:</th>
                    <td>{$username}</td>
                </tr>
                <tr>
                    <th>E-mail:</th>
                    <td>{$email}</td>
                </tr>
                <tr>
                    <th>Szerepkör:</th>
                    <td>{$role}</td>
                </tr>
                
                <tr>
                    <td><a class=\"update\" href=\"update.php\">Adatok módosítása</a></td>
                    <td><a class=\"nav-link\" href=\"php/logoutProcess.php\">Kilépés</a></td>
                </tr>
            </table>
        </div>
    </div>";
?>

<!DOCTYPE html>
<html lang="hu">
<?php include_once "kisegitok/head.html"; ?>
<body>
  <!-- Menü -->
  <?php include_once "kisegitok/nav.php"; 
   echo $output; 
  include_once "kisegitok/end.html";  ?>
  

  
  
