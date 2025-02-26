<?php
session_start();
require 'php/connect.php'; // Az adatbázis kapcsolat betöltése

// Ellenőrizzük, hogy van-e bejelentkezett felhasználó
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];
$view_user_id = $user_id; // Alapértelmezetten a bejelentkezett felhasználó profilját mutatja

// Ha van GET paraméterben felhasználó ID, akkor azt használjuk
if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $view_user_id = $_GET['user_id'];
}

// Lekérdezzük a megjelenítendő felhasználó adatait
$query = "SELECT id, username, email, profile_picture_url FROM users WHERE id = ?";
$stmt = $dbconn->prepare($query);
$stmt->bind_param("i", $view_user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "<p>Felhasználó nem található.</p>";
    exit();
}

// Lekérdezzük a felhasználó posztjait és a hozzájuk tartozó közösségeket
$query = "SELECT posts.id, posts.title, posts.body, cars.name AS community_name 
          FROM posts 
          JOIN cars ON posts.car_id = cars.id
          WHERE posts.user_id = ? 
          ORDER BY posts.created_at DESC";
$stmt = $dbconn->prepare($query);
$stmt->bind_param("i", $view_user_id);
$stmt->execute();
$result = $stmt->get_result();
$posts = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Felhasználói profil</title>
    <script>
        function searchUsers(query) {
            if (query.length === 0) {
                document.getElementById("searchResults").innerHTML = "";
                return;
            }
            let xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    document.getElementById("searchResults").innerHTML = this.responseText;
                }
            };
            xhr.open("GET", "search_users.php?query=" + query, true);
            xhr.send();
        }
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/cursor.css">
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>
<?php include 'kisegitok/nav.php' ?>
    <h1><?php echo htmlspecialchars($user['username']); ?> profilja</h1>
    <img src="php/img/<?php echo htmlspecialchars($user['profile_picture_url'])?>" alt="">
    <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
    
    <h2>Posztok</h2>
    <?php if (count($posts) > 0): ?>
        <ul>
            <?php foreach ($posts as $post): ?>
                <li>
                    <strong><?php echo htmlspecialchars($post['title']); ?></strong> - <?php echo htmlspecialchars($post['community_name']); ?><br>
                    <?php echo nl2br(htmlspecialchars($post['body'])); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Nincs poszt.</p>
    <?php endif; ?>
    
    <h2>Felhasználó keresése</h2>
    <input type="text" name="query" placeholder="Felhasználónév" onkeyup="searchUsers(this.value)">
    <div id="searchResults"></div>
    
    <a href="index.php">Vissza a főoldalra</a>
</body>
</html>
