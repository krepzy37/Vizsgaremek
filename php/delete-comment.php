<?php
session_start();
include 'connect.php'; // Csatlakozás az adatbázishoz

// Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['id'])) {
    header("Location: login.php"); // Ha nincs bejelentkezve, irányítsuk a login oldalra
    exit;
}

$user_id = $_SESSION['id'];

// Ellenőrizzük, hogy meg van-e adva a komment ID
if (isset($_GET['comment_id']) && is_numeric($_GET['comment_id'])) {
    $comment_id = intval($_GET['comment_id']);

    // Lekérdezzük a komment tulajdonosát
    $query = "SELECT user_id FROM comments WHERE id = ?";
    if ($stmt = $dbconn->prepare($query)) {
        $stmt->bind_param("i", $comment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $comment = $result->fetch_assoc();
            $comment_owner = $comment['user_id'];

            // Lekérdezzük a bejelentkezett felhasználó szerepét
            $roleQuery = "SELECT role FROM users WHERE id = ?";
            $stmt = $dbconn->prepare($roleQuery);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $roleResult = $stmt->get_result();

            if ($roleResult->num_rows > 0) {
                $user = $roleResult->fetch_assoc();
                $role = $user['role'];

                if ($role === 'Moderator') {
                    // Ha moderátor, akkor archiváljuk a kommentet
                    $updateQuery = "UPDATE comments SET status = 'archived' WHERE id = ?";
                    $stmt = $dbconn->prepare($updateQuery);
                    $stmt->bind_param("i", $comment_id);
                    $stmt->execute();

                    // Logoljuk az admin_logs táblába
                    $logQuery = "INSERT INTO admin_logs (admin_user_id, target_comment_id) VALUES (?, ?)";
                    $stmt = $dbconn->prepare($logQuery);
                    $stmt->bind_param("ii", $user_id, $comment_id);
                    $stmt->execute();

                } elseif ($user_id === $comment_owner) {
                    // Ha a saját kommentjét törli a felhasználó, akkor véglegesen töröljük
                    $deleteQuery = "DELETE FROM comments WHERE id = ?";
                    $stmt = $dbconn->prepare($deleteQuery);
                    $stmt->bind_param("i", $comment_id);
                    $stmt->execute();
                }
            }
        }
    }
}

// Visszairányítás az előző oldalra
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;

?>
