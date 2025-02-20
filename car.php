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
    <style>
        body{
            background-image: url(php/img/Ggx5UreWEAAXJ0A.jpg);
            background-size: cover;
            background-attachment: fixed;
        }
    </style>
</head>
<?php
include 'kisegitok/nav.php';


?>
<div class="container mt-5">
    <div class="main-content">
        <!-- Car Information -->
        <div class="container my-4">
            <div class="card shadow-sm p-4 bg-dark text-light">
                <div class="d-flex align-items-center mb-3">
                    <img src="php/img/carlogos/<?php echo $car_logo ?>" alt="<?php echo $brand_name ?> logo" style="max-width: 100px" class="me-3">
                    <h1 class="m-0"><?php echo $brand_name . ' ' . $car_name ?></h1>
                </div>

                <div class="d-flex justify-content-start my-3">
                    <form method="GET" class="d-flex gap-2">
                        <input type="hidden" name="brand" value="<?php echo urlencode($brand); ?>">
                        <input type="hidden" name="model" value="<?php echo urlencode($model); ?>">
                        <label for="sort" class="form-label m-2">Rendezés:</label>
                        <select name="sort" id="sort" class="form-select bg-secondary text-light border-0" onchange="this.form.submit()">
                            <option value="latest" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'latest') ? 'selected' : ''; ?>>Legutóbbi</option>
                            <option value="votes_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'votes_desc') ? 'selected' : ''; ?>>Legjobb</option>
                            <option value="votes_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'votes_asc') ? 'selected' : ''; ?>>Legrosszabb</option>
                        </select>
                    </form>
                </div>

            </div>
        </div>


        <div class="card shadow-sm col-lg-8 m-auto p-4 mb-4 bg-dark text-light">
            <h5 class="card-title mb-3">Új poszt hozzáadása</h5>
            <button type="button" class="toggle-post-form btn btn-secondary mb-3">Poszt hozzáadása megjelenítése</button>

            <form id="postForm" enctype="multipart/form-data" style="display: none;">
                <div class="mb-3">
                    <label for="title" class="form-label">Cím:</label>
                    <input type="text" id="title" name="title" class="form-control bg-secondary text-light" required>
                </div>

                <div class="mb-3">
                    <label for="body" class="form-label">Tartalom:</label>
                    <textarea id="body" name="body" class="form-control bg-secondary text-light" rows="4" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Kép:</label>
                    <input type="file" name="image" id="image" class="form-control bg-secondary text-light">
                </div>

                <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">

                <button type="submit" class="btn btn-primary w-100">Poszt hozzáadása</button>
            </form>
        </div>
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
        <div class="post-list">
            <?php
            // Posztok megjelenítése
            if ($result->num_rows > 0) {

                while ($post = mysqli_fetch_assoc($result)) {
                    // Profilkép elérési útvonal beállítása
                    $profilePic = !empty($post['profile_picture_url']) ? 'php/img/' . htmlspecialchars($post['profile_picture_url']) : 'php/img/default.png';
                    $postImage = !empty($post['post_image_url']) ? 'php/img/' . htmlspecialchars($post['post_image_url']) : '';

                    echo "<div class='card mb-4 col-lg-8  bg-dark text-light' style='border-radius: 10px; margin:auto;'>";
                    echo "<div class='card-body'>";
                    echo "<div class='d-flex align-items-center gap-2'>";
                    echo "<img src='$profilePic' alt='Profilkép' class='rounded-circle  mb-2' style='width: 40px; height: 40px;'>";
                    echo "<h5 class='card-title '>" . htmlspecialchars($post['username']) . "</h5>";
                    echo "</div>";

                    echo "<h4 class='card-subtitle mb-2'>" . htmlspecialchars($post['title']) . "</h4>";
                    echo "<p class='card-text'>" . htmlspecialchars($post['body']) . "</p>";

                    // Ha van kép, akkor megjelenítjük
                    if ($postImage) {
                        echo "<img src='$postImage' alt='Poszt Kép' class='img-fluid rounded d-block mx-auto' >";
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

                    if (isset($_SESSION['id']) && $_SESSION['id'] == $post['user_id']) {
                        echo "<button class='btn btn-warning edit-post-btn' data-id='" . $post['id'] . "' 
                              data-title='" . htmlspecialchars($post['title']) . "' 
                              data-body='" . htmlspecialchars($post['body']) . "' 
                              data-image='" . $postImage . "'>Szerkesztés</button>";
                        echo "<a href='php/delete-post.php?post_id=" . $post['id'] . "' class='btn btn-danger ms-2' onclick='return confirm(\"Biztosan törlöd a posztot?\")'>Poszt törlése</a>";
                    }


                    // Hozzászólások lekérdezése ehhez a poszthoz
                    $post_id = $post['id'];
                    $comment_query = "SELECT comments.id, comments.body, comments.comment_image_url, users.profile_picture_url, users.username, comments.user_id, comments.created_at  
                  FROM comments 
                  JOIN users ON comments.user_id = users.id 
                  WHERE comments.post_id = $post_id";
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
                        $commentPfp = htmlspecialchars($comment['profile_picture_url']);
                        echo "<div class='card mb-3 bg-dark text-light'>
            <div class='card-body'>
                <div class='d-flex align-items-center mb-2'>
                    <img src='php/img/$commentPfp' alt='Profilkép' class='rounded-circle' style='width: 35px; height: 35px;'>
                    <strong class='ms-2'>" . htmlspecialchars($comment['username']) . ":</strong>
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

                        if (isset($_SESSION['id']) && $_SESSION['id'] == $comment['user_id']) {
                            echo "<button class='btn btn-warning edit-comment-btn' data-id='" . $comment['id'] . "' 
            data-text='" . htmlspecialchars($comment['body']) . "' 
            data-image='" . $comment['comment_image_url'] . "'>Szerkesztés</button>";
                            $referer = urlencode($_SERVER['REQUEST_URI']); // Az aktuális oldal URL-je
                            echo "<a class='btn btn-danger ms-2' href='php/delete-comment.php?comment_id=" . $comment['id'] . "&redirect=" . $referer . "' onclick='return confirm(\"Biztosan törlöd a kommentet?\")'>Komment törlése</a>";
                        }
                    }
                    echo "</div>";
                    echo "</div>";

                    // 
                    
                    
                    echo "</div></div>"; // Card end
                }
            } else {
                echo "<p class='col-lg-8 m-auto alert alert-info'>Ebbe a közösségbe még nem posztolt senki. Te lehetsz az első🤩!</p>";
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
    </main>
    <script src="script/edit-comment-vote.js"></script>
    <?php include 'kisegitok/footer.php' ?>
    <div class="custom-cursor"></div>
    <div class="cursor-follower"></div>
    <script src="./script/cursor.js"></script>
    </body>

</html>