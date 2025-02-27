<?php
session_start();
require 'php/connect.php'; // Adatbázis kapcsolat

// Inicializáljuk a view_user_id-t null-ra
$view_user_id = null;

// Ellenőrizzük, hogy van-e user_id a GET paraméterek között
if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $view_user_id = $_GET['user_id'];
} elseif (isset($_SESSION['id'])) {
    // Ha be vagyunk jelentkezve, akkor a saját profilunkat mutatjuk
    $view_user_id = $_SESSION['id'];
}

// Ha nincs megadva user_id, akkor hibaüzenet
if ($view_user_id === null) {
    echo "<p>Felhasználó nem található.</p>";
    exit();
}

// Lekérdezzük a megjelenítendő felhasználó adatait
$query = "SELECT id, username, profile_picture_url, status, role FROM users WHERE id = ?";
$stmt = $dbconn->prepare($query);
$stmt->bind_param("i", $view_user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "<p>Felhasználó nem található.</p>";
    exit();
}

/*  // Ellenőrzés - debug
var_dump($user);
var_dump($_SESSION);*/
// Biztosítsuk, hogy a status mindig létezik
$status = isset($user['status']) ? htmlspecialchars($user['status']) : "Nincs státusz megadva";

// Lekérdezzük a felhasználó posztjait
$query = "SELECT posts.id, posts.title, posts.body, posts.post_image_url, cars.name AS community_name, brands.name AS brand_name 
          FROM posts 
          JOIN cars ON posts.car_id = cars.id
          JOIN brands ON cars.brand_id = brands.id
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
    <title><?php echo htmlspecialchars($user['username']); ?> profilja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/cursor.css">
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<?php include 'kisegitok/nav.php'; ?>
<?php $roleTag = ($user['role'] == 'Moderator') ? " <span class='text-warning'>[Moderator]</span>" : ""; ?>
    <h1><?php echo htmlspecialchars($user['username']) . $roleTag; ?></h1>
    <img src="php/img/<?php echo htmlspecialchars($user['profile_picture_url'])?>" alt="Profilkép" style="width:100px; height:100px; border-radius:50%;">
    <p>Státusz: <?php echo $status; ?></p>
    
    <h2>Posztok</h2>

<div class="post-list">
    <?php if (count($posts) > 0): ?>
        <?php foreach ($posts as $post): ?>
            <?php
                //$profilePic = !empty($post['profile_picture_url']) ? 'php/img/' . htmlspecialchars($post['profile_picture_url']) : 'php/img/default.png';
                $postImage = !empty($post['post_image_url']) ? 'php/img/' . htmlspecialchars($post['post_image_url']) : '';
            ?>
            <div class='card mb-4 col-lg-8 bg-dark text-light' style='border-radius: 10px; margin:auto;'>
                <div class='card-body'>
                    
                    <h5><?php echo htmlspecialchars($post['brand_name']) . " " . htmlspecialchars($post['community_name']) ?></h5>
                    <h4 class='card-subtitle mb-2'><?php echo htmlspecialchars($post['title']); ?></h4>
                    <p class='card-text'><?php echo nl2br(htmlspecialchars($post['body'])); ?></p>
                    <?php if ($postImage): ?>
                        <img src='<?php echo $postImage; ?>' alt='Poszt Kép' class='img-fluid rounded d-block mx-auto'>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nincs poszt.</p>
    <?php endif; ?>
</div>

    
    <a href="index.php">Vissza a főoldalra</a>
</body>
</html>