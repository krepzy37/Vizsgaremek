<?php
session_start();
include 'php/connect.php';

// Get the brand and model from the URL
$brand = isset($_GET['brand']) ? $_GET['brand'] : '';
$model = isset($_GET['model']) ? $_GET['model'] : '';

// Prepare the SQL query to fetch the car details
$car_query = "
    SELECT cars.id AS car_id, cars.name AS car_name, brands.logo_url, brands.name AS brand_name 
    FROM cars 
    JOIN brands ON cars.brand_id = brands.id 
    WHERE brands.name = ? AND cars.name = ?";
$car_stmt = $dbconn->prepare($car_query);
$car_stmt->bind_param("ss", $brand, $model);
$car_stmt->execute();
$car_result = $car_stmt->get_result();
$car = $car_result->fetch_assoc();

// ellenőrizzük, hogy létezik-e autó
if (!$car) {
    // index.php ha nincs autó
    header("Location: index.php");
    exit;
}

$car_name = htmlspecialchars($car['car_name']);
$brand_name = htmlspecialchars($car['brand_name']);
$car_logo = htmlspecialchars($car['logo_url']);
$car_id = $car['car_id'];

//az autóhoz tartozó adatok
$order = "ORDER BY created_at DESC"; // alapértelmezetten a legújabbra rendezünk
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'latest':
            $order = "ORDER BY created_at DESC";
            break;
        case 'votes_asc':
            $order = "ORDER BY COALESCE((SELECT SUM(CASE WHEN vote_type = 'upvote' THEN 1 ELSE -1 END) FROM votes WHERE post_id = posts.id), 0) ASC";
            break;
        case 'votes_desc':
        default:
            $order = "ORDER BY COALESCE((SELECT SUM(CASE WHEN vote_type = 'upvote' THEN 1 ELSE -1 END) FROM votes WHERE post_id = posts.id), 0) DESC";
            break;
    }
}

//posztok és szavazatok
$query = "
    SELECT posts.*, 
           users.username, 
           users.profile_picture_url, 
           posts.created_at,
           COALESCE(vote_counts.vote_count, 0) AS vote_count
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    LEFT JOIN (
        SELECT post_id, 
               SUM(CASE WHEN vote_type = 'upvote' THEN 1 ELSE -1 END) AS vote_count
        FROM votes
        GROUP BY post_id
    ) AS vote_counts ON posts.id = vote_counts.post_id
    WHERE posts.car_id = ?
    $order
";

$stmt = $dbconn->prepare($query);
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="hu" ng-app="carApp">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $brand_name, " ", $car_name  ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/cursor.css">
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<?php
include 'kisegitok/nav.php';

echo '<img src="php/img/carlogos/' . $car_logo . '" alt="' . $brand_name . ' logo" style="max-width: 200px;">';
?>
<h1><?php echo $brand_name . ' ' . $car_name; ?></h1>
<div>
    <a href="?brand=<?php echo urlencode($brand); ?>&model=<?php echo urlencode($model); ?>&sort=latest">Legutóbbi</a>
    <a href="?brand=<?php echo urlencode($brand); ?>&model=<?php echo urlencode($model); ?>&sort=votes_desc">Legjobb</a>
    <a href="?brand=<?php echo urlencode($brand); ?>&model=<?php echo urlencode($model); ?>&sort=votes_asc">Legrosszabb</a>
</div>

<form id="postForm" enctype="multipart/form-data">
    <label for="title">Cím:</label>
    <input type="text" id="title" name="title" required>

    <label for="body">Tartalom:</label>
    <textarea id="body" name="body" required></textarea>

    <label for="image">Kép:</label>
    <input type="file" name="image" id="image">

    <input type="hidden" name="car_id" value=<?php echo $car_id; ?>>

    <button type="submit">Poszt hozzáadása</button>
</form>

<script>
    document.getElementById("postForm").addEventListener("submit", function(event) {
        event.preventDefault(); // Alapértelmezett form beküldés tiltása

        let formData = new FormData(this); // Form adatok elküldése AJAX-szal

        fetch("php/add-post.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json()) // JSON választ várunk
            .then(data => {
                alert(data.message); // Alert a válasz alapján
                if (data.success) {
                    location.reload(); // Sikeres poszt esetén frissítjük az oldalt
                }
            })
            .catch(error => console.error("Hiba:", error));
    });
</script>

<?php
// Posztok megjelenítése
if ($result->num_rows > 0) {
    while ($post = mysqli_fetch_assoc($result)) {
        // Profilkép elérési útvonal beállítása
        $profilePic = !empty($post['profile_picture_url']) ? 'php/img/' . htmlspecialchars($post['profile_picture_url']) : 'php/img/default.png';
        $postImage = !empty($post['post_image_url']) ? 'php/img/' . htmlspecialchars($post['post_image_url']) : '';

        echo "<div style='display: flex; align-items: center; gap: 10px;'>";
        echo "<img src='$profilePic' alt='Profilkép' style='width: 40px; height: 40px; border-radius: 50%;'>";
        echo "<h3>" . htmlspecialchars($post['username']) . "</h3>";
        echo "</div>";

        echo "<h2>" . htmlspecialchars($post['title']) . "</h2>";
        echo "<p>" . htmlspecialchars($post['body']) . "</p>";

        // Ha van kép, akkor megjelenítjük
        if ($postImage) {
            echo "<img src='$postImage' alt='Poszt Kép' style='max-width: 300px; height: auto;'>";
        }

        // Fetching the vote score for the post
        $vote_query = "SELECT SUM(CASE WHEN vote_type = 'upvote' THEN 1 ELSE -1 END) as score 
FROM votes 
WHERE post_id = ?";
        $stmt = $dbconn->prepare($vote_query);
        $stmt->bind_param("i", $post['id']); // Use the current post ID
        $stmt->execute();
        $vote_result = $stmt->get_result()->fetch_assoc();

        // Set score to 0 if there are no votes
        $score = $vote_result['score'] !== null ? $vote_result['score'] : 0;

        // Display the post with the vote score
        echo "<div class='vote-buttons'>
<button class='upvote' data-id='" . $post['id'] . "' data-type='post'>⬆</button>
<span id='post-score-" . $post['id'] . "'>" . $score . "</span>
<button class='downvote' data-id='" . $post['id'] . "' data-type='post'>⬇</button>
</div>";

        $createdAt = date("Y. m. d. H:i", strtotime($post['created_at']));
        echo "<p><em>Feltöltve: $createdAt</em></p>";

        if (isset($_SESSION['id']) && $_SESSION['id'] == $post['user_id']) {
            echo "<button class='edit-post-btn' data-id='" . $post['id'] . "' 
          data-title='" . htmlspecialchars($post['title']) . "' 
          data-body='" . htmlspecialchars($post['body']) . "' 
          data-image='" . $postImage . "'>Szerkesztés</button>";
            $referer = urlencode($_SERVER['REQUEST_URI']); // Az aktuális oldal URL-je
            echo "<a href='php/delete-post.php?post_id=" . $post['id'] . "&redirect=" . $referer . "' onclick='return confirm(\"Biztosan törlöd a posztot?\")'>Poszt törlése</a>";
        }

        // Hozzászólások lekérdezése ehhez a poszthoz
        $post_id = $post['id'];
        $comment_query = "SELECT comments.id, comments.body, comments.comment_image_url, users.username, comments.user_id, comments.created_at  
                  FROM comments 
                  JOIN users ON comments.user_id = users.id 
                  WHERE comments.post_id = $post_id";
        $comment_result = mysqli_query($dbconn, $comment_query);

        // Hozzászólások szekció
        echo "<div>";
        echo "<button type='button' class='toggle-comments' data-post-id='$post_id'>Hozzászólások megjelenítése</button>";

        echo "<div class='comments' id='comments-$post_id' style='display:none;'>";
        while ($comment = mysqli_fetch_assoc($comment_result)) {
            echo "<p><strong>" . htmlspecialchars($comment['username']) . ":</strong> " . htmlspecialchars($comment['body']) . "</p>";

            // Ha van kommenthez tartozó kép
            if (!empty($comment['comment_image_url'])) {
                $commentImage = 'php/img/' . htmlspecialchars($comment['comment_image_url']);
                echo "<img src='$commentImage' alt='Komment Kép' style='max-width: 200px; height: auto;'>";
            }
            $commentCreatedAt = date("Y. m. d. H:i", strtotime($comment['created_at']));
            echo "<p><em>Feltöltve: $commentCreatedAt</em></p>";

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
            echo "<div class='vote-buttons'>
<button class='upvote' data-id='" . $comment['id'] . "' data-type='comment'>⬆</button>
<span id='comment-score-" . $comment['id'] . "'>" . $comment_score . "</span>
<button class='downvote' data-id='" . $comment['id'] . "' data-type='comment'>⬇</button>
</div>";

            if (isset($_SESSION['id']) && $_SESSION['id'] == $comment['user_id']) {
                echo "<button class='edit-comment-btn' data-id='" . $comment['id'] . "' 
            data-text='" . htmlspecialchars($comment['body']) . "' 
            data-image='" . $comment['comment_image_url'] . "'>Szerkesztés</button>";
                $referer = urlencode($_SERVER['REQUEST_URI']); // Az aktuális oldal URL-je
                echo "<a href='php/delete-comment.php?comment_id=" . $comment['id'] . "&redirect=" . $referer . "' onclick='return confirm(\"Biztosan törlöd a kommentet?\")'>Komment törlése</a>";
            }
        }
        echo "</div>";
        echo "</div>";

        // Hozzászólás űrlap
        echo "<form class='comment-form' data-post-id='$post_id' enctype='multipart/form-data'>
            <textarea name='comment_text' required></textarea>
            <label for='comment_image'>Kép:</label>
            <input type='file' name='comment_image'>
            <button type='submit'>Hozzászólás hozzáadása</button>
          </form>";
    }
} else {
    // Display message if there are no posts
    echo "<p>Ebbe a közösségbe még nem posztolt senki. Te lehetsz az első🤩!</p>";
}
?>
<script src="script/comments.js"></script>

<!-- Szerkesztő Modal -->
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

<!-- Komment Szerkesztő Modal -->
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

<script src="script/edit-comment-vote.js"></script>
<div class="custom-cursor"></div>
<div class="cursor-follower"></div>
<script src="./script/cursor.js"></script>
</body>

</html>