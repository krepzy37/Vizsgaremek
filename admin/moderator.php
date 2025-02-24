<?php
session_start();
require "../php/connect.php";
// Must match database value
if ($_SESSION['user_role'] !== 'Moderator') {
    header("Location: access_denied.php");
    exit();
}

// Fetch users
$user_query = "SELECT * FROM users";
$user_result = $dbconn->query($user_query);

// Fetch posts
$post_query = "SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id";
$post_result = $dbconn->query($post_query);

// Fetch comments
$comment_query = "SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id";
$comment_result = $dbconn->query($comment_query);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

    
</head>
<body>
    

    <div class="container mt-5">
        <h1 class="mb-4">Admin Dashboard</h1>

        <!-- User Management -->
        <h2>Users</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Actions</th>
                    <th>Profile Pic</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $user_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($user['created_at']));?></td>
                        <td><img style="max-width:50px" src="../php/img/<?php echo htmlspecialchars($user['profile_picture_url']) ?>" alt="<?php echo htmlspecialchars($user['username']); ?> profile picture"></td>
                        <td>
                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="php/delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Post Management -->
        <h2>Posts</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($post = $post_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $post['id']; ?></td>
                        <td><?php echo htmlspecialchars($post['title']); ?></td>
                        <td><?php echo htmlspecialchars($post['username']); ?></td>
                        <td>
                            <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="../php/delete_post.php?post_id=<?php echo $post['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Comment Management -->
        <h2>Comments</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Comment</th>
                    <th>Author</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($comment = $comment_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $comment['id']; ?></td>
                        <td><?php echo htmlspecialchars($comment['body']); ?></td>
                        <td><?php echo htmlspecialchars($comment['username']); ?></td>
                        <td>
                            <a href="php/delete_comment.php?comment_id=<?php echo $comment['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this comment?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>