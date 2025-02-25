<?php
session_start();
require "../php/connect.php";

// Csak moderátorok érhetik el az oldalt
if ($_SESSION['user_role'] !== 'Moderator') {
    header("Location: access_denied.php");
    exit();
}

// Felhasználók lekérdezése
$user_query = "SELECT * FROM users";
$user_result = $dbconn->query($user_query);

// Archivált posztok lekérdezése
$archived_posts_query = "
SELECT p.id, p.title, p.body, p.post_image_url, p.created_at, 
u.username AS author, 
m.username AS moderator, al.created_at AS log_time
FROM posts p
JOIN admin_logs al ON p.id = al.target_post_id
JOIN users u ON p.user_id = u.id
JOIN users m ON al.admin_user_id = m.id
WHERE p.status = 'Archived';

";
$archived_posts_result = $dbconn->query($archived_posts_query);

// Archivált kommentek lekérdezése
$archived_comments_query = "
SELECT c.id, c.body, c.comment_image_url, c.created_at, 
u.username AS author, 
m.username AS moderator, al.created_at AS log_time 
FROM comments c
JOIN admin_logs al ON c.id = al.target_comment_id
JOIN users u ON c.user_id = u.id
JOIN users m ON al.admin_user_id = m.id
WHERE c.status = 'Archived';

";
$archived_comments_result = $dbconn->query($archived_comments_query);

?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderator Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4">Moderator Dashboard</h1>
        <a href="../index.php">Vissza a főoldalra</a>
        <!-- Felhasználók kezelése -->
        <h2>Felhasználók</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Felhasználónév</th>
                    <th>Email</th>
                    <th>Szerep</th>
                    <th>Létrehozva</th>
                    <th>Profilkép</th>
                    <th>Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $user_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($user['created_at'])); ?></td>
                        <td><img style="max-width:50px" src="../php/img/<?php echo htmlspecialchars($user['profile_picture_url']) ?>" alt="<?php echo htmlspecialchars($user['username']); ?> profilképe"></td>
                        <td>
                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm">Szerkesztés</a>
                            <a href="php/delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Biztosan törölni akarod ezt a felhasználót?')">Törlés</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Archivált posztok -->
        <h2 class="mt-5">Archivált Posztok</h2>
        <table class="table table-striped">
        <thead>
    <tr>
        <th>ID</th>
        <th>Cím</th>
        <th>Tartalom</th>
        <th>Média</th>
        <th>Készítette</th>
        <th>Létrehozás dátuma</th>
        <th>Archiválta</th>
        <th>Archiválás dátuma</th>
        <th>Műveletek</th>
    </tr>
</thead>
<tbody>
    <?php while ($post = $archived_posts_result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $post['id']; ?></td>
            <td><?php echo htmlspecialchars($post['title']); ?></td>
            <td><?php echo htmlspecialchars($post['body']); ?></td>
            <td>
                <?php if (!empty($post['post_image_url'])): ?>
                    <img style="max-width:150px" src="../php/img/<?php echo htmlspecialchars($post['post_image_url']); ?>" alt="Média">
                <?php else: ?>
                    <span>Nincs kép</span>
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($post['author']); ?></td>
            <td><?php echo date('Y-m-d H:i:s', strtotime($post['created_at'])); ?></td>
            <td><?php echo htmlspecialchars($post['moderator']); ?></td>
            <td><?php echo date('Y-m-d H:i:s', strtotime($post['log_time'])); ?></td>
            <td>
                <a href="restore_post.php?id=<?php echo $post['id']; ?>" class="btn btn-success btn-sm"
                    onclick="return confirm('Biztosan visszaállítod ezt a posztot?')">
                    Visszaállítás
                </a>
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>

        </table>

        <!-- Archivált kommentek -->
        <h2 class="mt-5">Archivált Kommentek</h2>
        <table class="table table-striped">
        <thead>
    <tr>
        <th>ID</th>
        <th>Tartalom</th>
        <th>Média</th>
        <th>Készítette</th>
        <th>Létrehozás dátuma</th>
        <th>Archiválta</th>
        <th>Archiválás dátuma</th>
        <th>Műveletek</th>
    </tr>
</thead>
<tbody>
    <?php while ($comment = $archived_comments_result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $comment['id']; ?></td>
            <td><?php echo htmlspecialchars($comment['body']); ?></td>
            <td>
                <?php if (!empty($comment['comment_image_url'])): ?>
                    <img style="max-width:150px" src="../php/img/<?php echo htmlspecialchars($comment['comment_image_url']); ?>" alt="Média">
                <?php else: ?>
                    <span>Nincs kép</span>
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($comment['author']); ?></td>
            <td><?php echo date('Y-m-d H:i:s', strtotime($comment['created_at'])); ?></td>
            <td><?php echo htmlspecialchars($comment['moderator']); ?></td>
            <td><?php echo date('Y-m-d H:i:s', strtotime($comment['log_time'])); ?></td>
            <td>
                <a href="restore_comment.php?id=<?php echo $comment['id']; ?>" class="btn btn-success btn-sm"
                    onclick="return confirm('Biztosan visszaállítod ezt a kommentet?')">
                    Visszaállítás
                </a>
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>

        </table>
    </div>
</body>

</html>