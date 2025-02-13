<?php
session_start();


// Csatlakozás az adatbázishoz
include '../php/connect.php'; 

// Lekérdezzük a Polo bejegyzéseit
$query = "SELECT posts.*, users.username, users.profile_picture_url, posts.created_at 
          FROM posts 
          JOIN users ON posts.user_id = users.id 
          WHERE posts.car_id = 147
          ORDER BY posts.created_at DESC"; 

$result = mysqli_query($dbconn, $query);
include '../kisegitok/head2.html';
include '../kisegitok/nav2.php';



?>

<form id="postForm" enctype="multipart/form-data">
    <label for="title">Cím:</label>
    <input type="text" id="title" name="title" required>

    <label for="body">Tartalom:</label>
    <textarea id="body" name="body" required></textarea>

    <label for="image">Kép:</label>
    <input type="file" name="image" id="image">

    <input type="hidden" name="car_id" value="147"> <!-- Autó ID-je -->

    <button type="submit">Poszt hozzáadása</button>
</form>


<script>
document.getElementById("postForm").addEventListener("submit", function(event) {
    event.preventDefault(); // Alapértelmezett form beküldés tiltása

    let formData = new FormData(this); // Form adatok elküldése AJAX-szal

    fetch("../php/add-post.php", {
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
while ($post = mysqli_fetch_assoc($result)) {
    // Profilkép elérési útvonal beállítása
    $profilePic = !empty($post['profile_picture_url']) ? '../php/img/' . htmlspecialchars($post['profile_picture_url']) : '../php/img/default.png';
    $postImage = !empty($post['post_image_url']) ? '../php/img/' . htmlspecialchars($post['post_image_url']) : '';

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

    // Szavazógombok a poszthoz
    echo "<div class='vote-buttons'>
            <button class='upvote' data-id='" . $post['id'] . "' data-type='post'>⬆</button>
            <span id='post-score-" . $post['id'] . "'>0</span>
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
        echo "<a href='../php/delete-post.php?post_id=" . $post['id'] . "&redirect=" . $referer . "' onclick='return confirm(\"Biztosan törlöd a posztot?\")'>Poszt törlése</a>";
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
            $commentImage = '../php/img/' . htmlspecialchars($comment['comment_image_url']);
            echo "<img src='$commentImage' alt='Komment Kép' style='max-width: 200px; height: auto;'>";
        }
        $commentCreatedAt = date("Y. m. d. H:i", strtotime($comment['created_at']));
        echo "<p><em>Feltöltve: $commentCreatedAt</em></p>";

        // Szavazógombok a kommenthez
        echo "<div class='vote-buttons'>
                <button class='upvote' data-id='" . $comment['id'] . "' data-type='comment'>⬆</button>
                <span id='comment-score-" . $comment['id'] . "'>0</span>
                <button class='downvote' data-id='" . $comment['id'] . "' data-type='comment'>⬇</button>
              </div>";

        if (isset($_SESSION['id']) && $_SESSION['id'] == $comment['user_id']) {
            echo "<button class='edit-comment-btn' data-id='" . $comment['id'] . "' 
            data-text='" . htmlspecialchars($comment['body']) . "' 
            data-image='" . $comment['comment_image_url'] . "'>Szerkesztés</button>";
            $referer = urlencode($_SERVER['REQUEST_URI']); // Az aktuális oldal URL-je
            echo "<a href='../php/delete-comment.php?comment_id=" . $comment['id'] . "&redirect=" . $referer . "' onclick='return confirm(\"Biztosan törlöd a kommentet?\")'>Komment törlése</a>";
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

?>
<script>
    // Hozzászólások megjelenítése/elrejtése gombbal
    document.querySelectorAll('.toggle-comments').forEach(button => {
        button.addEventListener('click', function() {
            let postId = this.getAttribute('data-post-id');
            let commentsContainer = document.getElementById('comments-' + postId);
            
            if (commentsContainer.style.display === 'none') {
                commentsContainer.style.display = 'block';
                this.textContent = 'Hozzászólások elrejtése';
            } else {
                commentsContainer.style.display = 'none';
                this.textContent = 'Hozzászólások megjelenítése';
            }
        });
    });

    // Hozzászólás hozzáadása AJAX segítségével
    document.querySelectorAll('.comment-form').forEach(form => {
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        let postId = this.getAttribute('data-post-id');
        let commentText = this.querySelector('textarea').value;
        let commentImage = this.querySelector('input[name="comment_image"]').files[0];

        let formData = new FormData();
        formData.append('comment_text', commentText);
        formData.append('post_id', postId);
        if (commentImage) formData.append('comment_image', commentImage); // Kép hozzáadása

        fetch("../php/add-comment.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                location.reload(); // Oldal frissítése a sikeres komment után
            }
        })
        .catch(error => console.error("Hiba:", error));
    });
});
</script>


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
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
    .modal-content { background-color: white; margin: 10% auto; padding: 20px; width: 50%; }
    .close { float: right; font-size: 28px; cursor: pointer; }
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let modal = document.getElementById("editModal");
    let closeModal = document.querySelector(".close");

    // Szerkesztés gombok eseménykezelése
    document.querySelectorAll(".edit-post-btn").forEach(button => {
        button.addEventListener("click", function() {
            let postId = this.getAttribute("data-id");
            let title = this.getAttribute("data-title");
            let body = this.getAttribute("data-body");
            let image = this.getAttribute("data-image");

            // Betöltjük az adatokat az űrlapba
            document.getElementById("edit_post_id").value = postId;
            document.getElementById("edit_title").value = title;
            document.getElementById("edit_body").value = body;
            
            let imgElem = document.getElementById("current_post_image");
            if (image) {
                imgElem.src = image;
                imgElem.style.display = "block";
            } else {
                imgElem.style.display = "none";
            }

            modal.style.display = "block"; // Modal megjelenítése
        });
    });

    // Modal bezárása
    closeModal.addEventListener("click", function() {
        modal.style.display = "none";
    });

    // AJAX-al frissítjük a posztot
    document.getElementById("editPostForm").addEventListener("submit", function(event) {
        event.preventDefault();

        let formData = new FormData(this);
        formData.append('remove_image', document.getElementById('remove_image').checked ? 'true' : 'false');
        
        fetch("../php/update-post.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                location.reload(); // Sikeres módosítás után frissítés
            }
        })
        .catch(error => console.error("Hiba:", error));
    });
});
</script>

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
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
    .modal-content { background-color: white; margin: 10% auto; padding: 20px; width: 50%; }
    .close { float: right; font-size: 28px; cursor: pointer; }
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let modal = document.getElementById("editCommentModal");
    let closeModal = document.querySelector("#editCommentModal .close");
    let editForm = document.getElementById("editCommentForm");

    document.querySelectorAll(".edit-comment-btn").forEach(button => {
        button.addEventListener("click", function() {
            let commentId = this.getAttribute("data-id");
            let text = this.getAttribute("data-text");
            let image = this.getAttribute("data-image");

            document.getElementById("edit_comment_id").value = commentId;
            document.getElementById("edit_comment_text").value = text;
            
            let imgElem = document.getElementById("current_comment_image");
            let deleteImageContainer = document.getElementById("delete_image_container");
            let deleteImageCheckbox = document.getElementById("delete_comment_image");

            if (image) {
                imgElem.src = image;
                imgElem.style.display = "block";
                deleteImageContainer.style.display = "block"; // Megjeleníti a törlés checkboxot
            } else {
                imgElem.style.display = "none";
                deleteImageContainer.style.display = "none"; // Elrejti a törlés checkboxot
            }

            deleteImageCheckbox.checked = false; // Alapértelmezett érték
            
            modal.style.display = "block";
        });
    });

    closeModal.addEventListener("click", function() {
        modal.style.display = "none";
    });

    window.addEventListener("click", function(event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

    // AJAX beküldés
    editForm.addEventListener("submit", function(event) {
        event.preventDefault();

        let formData = new FormData(editForm);
        fetch("../php/update-comment.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                modal.style.display = "none";
                location.reload();
            }
        })
        .catch(error => console.error("Hiba:", error));
    });
});








document.addEventListener("DOMContentLoaded", function () {
    const voteButtons = document.querySelectorAll(".upvote, .downvote");

    voteButtons.forEach(button => {
        button.addEventListener("click", function () {
            const postId = this.getAttribute("data-id");
            const type = this.getAttribute("data-type"); // 'post' vagy 'comment'
            const voteType = this.classList.contains("upvote") ? "upvote" : "downvote";

            fetch("../php/vote.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `id=${postId}&type=${type}&vote=${voteType}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const scoreElement = document.getElementById(`${type}-score-${postId}`);
                    if (scoreElement) {
                        scoreElement.textContent = data.newScore;
                    }
                } else {
                    alert("Hiba történt: " + data.message);
                }
            })
            .catch(error => console.error("Hálózati hiba: ", error));
        });
    });
});


</script>



</body>
</html>
