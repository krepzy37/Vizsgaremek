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
$query = "SELECT posts.id, posts.title, posts.user_id, posts.body, posts.post_image_url, posts.created_at, cars.name AS community_name, brands.logo_url, brands.name AS brand_name 
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
<div class="container mt-5">
    <div class="main-content">
        <?php include 'kisegitok/nav.php'; ?>
        <?php $roleTag = ($user['role'] == 'Moderator') ? " <span class='text-warning'>[Moderator]</span>" : ""; ?>
        <h1><?php echo htmlspecialchars($user['username']) . $roleTag; ?></h1>
        <img src="php/img/<?php echo htmlspecialchars($user['profile_picture_url']) ?>" alt="Profilkép" style="width:100px; height:100px; border-radius:50%;">
        <p>Státusz: <?php echo $status; ?></p>
        <?php 
if (isset($_SESSION['id']) && isset($_GET['user_id']) && $_SESSION['id'] == $_GET['user_id']) {
    echo '<a href="update.php">Profil szerkesztése</a>';
}
        ?>
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

                            <div class="mb-2 pb-2 pt-2" style="display: flex; align-items: center; border-radius: 8px; color:#4CAF50">
                                <img style="max-width: 40px; margin-right: 10px;" src="php/img/carlogos/<?php echo htmlspecialchars($post['logo_url']) ?>" alt="a">
                                <h5><?php echo htmlspecialchars($post['brand_name']) . " " . htmlspecialchars($post['community_name']) ?></h5>

                            </div>

                            <h3 class='card-subtitle mb-2'><?php echo htmlspecialchars($post['title']); ?></h3>
                            <p class='card-text'><?php echo nl2br(htmlspecialchars($post['body'])); ?></p>
                            <?php if ($postImage): ?>
                                <img src='<?php echo $postImage; ?>' alt='Poszt Kép' class='img-fluid rounded d-block mx-auto'>
                            <?php endif; ?>
                            <?php
                            $vote_query = "SELECT SUM(CASE WHEN vote_type = 'upvote' THEN 1 ELSE -1 END) as score FROM votes WHERE post_id = ?";
                            $stmt = $dbconn->prepare($vote_query);
                            $stmt->bind_param("i", $post['id']); // Use the current post ID
                            $stmt->execute();
                            $vote_result = $stmt->get_result()->fetch_assoc();

                            // Set score to 0 if there are no votes
                            $score = $vote_result['score'] !== null ? $vote_result['score'] : 0;

                            $user_vote_query = "SELECT vote_type FROM votes WHERE user_id = ? AND post_id = ?";
                            $user_vote_stmt = $dbconn->prepare($user_vote_query);
                            $user_vote_stmt->bind_param("ii", $_SESSION['id'], $post['id']);
                            $user_vote_stmt->execute();
                            $user_vote_result = $user_vote_stmt->get_result();
                            $user_vote = $user_vote_result->fetch_assoc();

                            $upvote_class = ($user_vote && $user_vote['vote_type'] === 'upvote') ? 'voted' : '';
                            $downvote_class = ($user_vote && $user_vote['vote_type'] === 'downvote') ? 'voted' : '';
                            // Display the post with the vote score
                            echo "<div class='vote-buttons mt-2 d-flex align-items-center gap-2'>
    <button class='upvote btn btn-outline-success $upvote_class' data-id='" . $post['id'] . "' data-type='post'>⬆</button>
    <span id='post-score-" . $post['id'] . "'>" . $score . "</span>
    <button class='downvote btn btn-outline-danger $downvote_class' data-id='" . $post['id'] . "' data-type='post'>⬇</button>
</div>";

                            $createdAt = date("Y. m. d. H:i", strtotime($post['created_at']));
                            echo "<p class='d-flex justify-content-end card-text text-muted'><em style='margin-top: -40px'> $createdAt</em></p>";

                            if (isset($_SESSION['id'])) {
                                // Ha a bejelentkezett felhasználó a poszt írója, megjelenik a szerkesztés gomb
                                if ($_SESSION['id'] == $post['user_id']) {
                                    echo "<button class='btn btn-warning edit-post-btn' data-id='" . $post['id'] . "' 
                                  data-title='" . htmlspecialchars($post['title']) . "' 
                                  data-body='" . htmlspecialchars($post['body']) . "' 
                                  data-image='" . $postImage . "'>Szerkesztés</button>";
                                }

                                // Ha a felhasználó a poszt írója VAGY moderátor, megjelenik a törlés gomb
                                if ($_SESSION['id'] == $post['user_id'] || $_SESSION['user_role'] == 'Moderator') {
                                    $referer = urlencode($_SERVER['REQUEST_URI']); // Az aktuális oldal URL-je
                                    echo "<a href='php/delete-post.php?post_id=" . $post['id'] . "&redirect=" . $referer . "' 
                                  class='btn btn-danger ms-2' 
                                  onclick='return confirm(\"Biztosan törlöd a posztot?\")'>Poszt törlése</a>";
                                }
                            }

                            $post_id = $post['id'];
                            $comment_query = "SELECT comments.id, comments.body, comments.comment_image_url, users.profile_picture_url, users.username, users.role, comments.user_id, comments.created_at  
                  FROM comments 
                  JOIN users ON comments.user_id = users.id 
                  WHERE comments.post_id = $post_id AND comments.status = 'Active'";
                            $comment_result = mysqli_query($dbconn, $comment_query);

                            // Hozzászólások szekció
                            echo "<div>";
                            echo "<button type='button' class='toggle-comments btn btn-secondary mt-3 mb-1' data-post-id='$post_id'>Hozzászólások megjelenítése</button>";

                            echo "<div class='comments' id='comments-$post_id' style='display:none;'>";
                            echo '<div class="card shadow-sm p-4 bg-dark text-light mt-4 "">
          <h5 class="card-title mb-3">Hozzászólás hozzáadása</h5>
          <form class="comment-form" data-post-id=' . $post_id . ' enctype="multipart/form-data">
              <div class="mb-3">
                  <label for="comment_text" class="form-label">Hozzászólás:</label>
                  <textarea name="comment_text" class="form-control bg-secondary text-light border-0" rows="3" required></textarea>
              </div>
              
              <div class="mb-3">
                  <label for="comment_image" class="form-label">Kép csatolása:</label>
                  <input type="file" name="comment_image" class="form-control bg-secondary text-light border-0">
              </div>
              
              <button type="submit" class="btn btn-primary w-100">Hozzászólás hozzáadása</button>
          </form>
      </div>';
                            while ($comment = mysqli_fetch_assoc($comment_result)) {
                                $roleTag = ($comment['role'] == 'Moderator') ? " <span class='text-warning'>[Moderator]</span>" : "";
                                $commentPfp = htmlspecialchars($comment['profile_picture_url']);

                                echo "<div class='card mb-3 bg-dark text-light'>
            <div class='card-body'>
                <div class='d-flex align-items-center mb-2'>
                    <img src='php/img/$commentPfp' alt='Profilkép' class='rounded-circle' style='width: 35px; height: 35px;'>
                    
                    <strong class='ms-2'>" . htmlspecialchars($comment['username']) . $roleTag . "</strong>
                </div>
                <p class='card-text'>" . htmlspecialchars($comment['body']) . "</p>";

                                // Ha van kommenthez tartozó kép
                                if (!empty($comment['comment_image_url'])) {
                                    $commentImage = 'php/img/' . htmlspecialchars($comment['comment_image_url']);
                                    echo "<div class='mb-2'>
                              <img src='$commentImage' alt='Komment Kép'  class='img-fluid' style='max-width: 50%; height: auto; '>
                          </div>";
                                }

                                $commentCreatedAt = date("Y. m. d. H:i", strtotime($comment['created_at']));
                                echo "<p class='d-flex justify-content-end card-text text-muted'><em style='margin-top: -45px;'> $commentCreatedAt</em></p>
            ";

                                // Szavazógombok a kommenthez
                                // Fetching the vote score for the comment
                                $comment_vote_query = "SELECT SUM(CASE WHEN vote_type = 'upvote' THEN 1 ELSE -1 END) as score 
FROM votes 
WHERE comment_id = ?";
                                $comment_stmt = $dbconn->prepare($comment_vote_query);
                                $comment_stmt->bind_param("i", $comment['id']); // Use the current comment ID
                                $comment_stmt->execute();
                                $comment_vote_result = $comment_stmt->get_result()->fetch_assoc();

                                // Set score to 0 if there are no votes
                                $comment_score = $comment_vote_result['score'] !== null ? $comment_vote_result['score'] : 0;

                                // Display the comment with the vote score
                                echo "<div class='vote-buttons mt-2 mb-2 d-flex align-items-center gap-2'>
<button class='upvote btn btn-outline-success' data-id='" . $comment['id'] . "' data-type='comment'>⬆</button>
<span id='comment-score-" . $comment['id'] . "'>" . $comment_score . "</span>
<button class='downvote btn btn-outline-danger' data-id='" . $comment['id'] . "' data-type='comment'>⬇</button>
</div> </div>
</div>";

                                if (isset($_SESSION['id'])) {
                                    // Ha a belépett user a komment írója, megjelenik a szerkesztés gomb
                                    if ($_SESSION['id'] == $comment['user_id']) {
                                        echo "<button class='btn btn-warning edit-comment-btn' data-id='" . $comment['id'] . "' 
              data-text='" . htmlspecialchars($comment['body']) . "' 
              data-image='" . $comment['comment_image_url'] . "'>Szerkesztés</button>";
                                    }

                                    // Ha a belépett user a komment írója VAGY moderátor, megjelenik a törlés gomb
                                    if ($_SESSION['id'] == $comment['user_id'] || $_SESSION['user_role'] == 'Moderator') {
                                        $referer = urlencode($_SERVER['REQUEST_URI']); // Az aktuális oldal URL-je
                                        echo "<a class='btn btn-danger ms-2' href='php/delete-comment.php?comment_id=" . $comment['id'] . "&redirect=" . $referer . "' onclick='return confirm(\"Biztosan törlöd a kommentet?\")'>Komment törlése</a>";
                                    }
                                }
                            }
                            echo "</div>";
                            echo "</div>";
                            ?>

                        </div>
                        <div id="editModal" class="modal">
                            <div class="modal-content">
                                <span class="close">&times;</span>
                                <h2>Poszt szerkesztése</h2>
                                <form id="editPostForm" enctype="multipart/form-data">
                                    <input type="hidden" id="edit_post_id" name="post_id">

                                    <label for="edit_title">Cím:</label>
                                    <input type="text" id="edit_title" name="title" required>

                                    <label for="edit_body">Tartalom:</label>
                                    <textarea id="edit_body" name="body" required></textarea>

                                    <label for="edit_image">Kép:</label>
                                    <input type="file" name="image" id="edit_image">

                                    <img id="current_post_image" src="" style="max-width: 200px; display: none;">
                                    <label for="remove_image">Kép törlése:</label>
                                    <input type="checkbox" id="remove_image" name="remove_image">


                                    <button type="submit">Mentés</button>
                                </form>
                            </div>
                        </div>

                        <style>
                            .voted {
                                background-color: #007bff;
                                /* Change to your desired highlight color */
                                color: white;
                                /* Change text color if needed */
                            }

                            .modal {
                                display: none;
                                position: fixed;
                                z-index: 1000;
                                left: 0;
                                top: 0;
                                width: 100%;
                                height: 100%;
                                background-color: rgba(0, 0, 0, 0.5);

                            }

                            .modal-content {
                                background-color: white;
                                margin: 10% auto;
                                padding: 20px;
                                width: 50%;
                            }

                            .close {
                                float: right;
                                font-size: 28px;
                                cursor: pointer;
                            }
                        </style>

                        <script src="script/edit-post.js"></script>

                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <p>Nincs poszt.</p>
            <?php endif; ?>
        </div>
        <div id="editCommentModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Komment szerkesztése</h2>
                    <form id="editCommentForm" enctype="multipart/form-data">
                        <input type="hidden" id="edit_comment_id" name="comment_id">

                        <label for="edit_comment_text">Szöveg:</label>
                        <textarea id="edit_comment_text" name="comment_text" required></textarea>

                        <label for="edit_comment_image">Kép:</label>
                        <input type="file" name="comment_image" id="edit_comment_image">

                        <img id="current_comment_image" src="" style="max-width: 200px; display: none;">
                        <div id="delete_image_container" style="display: none;">
                            <input type="checkbox" id="delete_comment_image" name="delete_comment_image">
                            <label for="delete_comment_image">Jelenlegi kép törlése</label>
                        </div>

                        <button type="submit">Mentés</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            color: black !important;
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            width: 50%;
        }

        .close {
            float: right;
            font-size: 28px;
            cursor: pointer;
        }
    </style>
    </main>
    <script src="script/edit-comment-vote.js"></script>
    <?php include 'kisegitok/footer.php' ?>
    <div class="custom-cursor"></div>
    <div class="cursor-follower"></div>
    <script src="./script/cursor.js"></script>
    </div>
</div>
<script src="script/comments.js"></script>

</body>

</html>