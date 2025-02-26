<?php
require 'php/connect.php'; // Adatbázis kapcsolat

if (isset($_GET['query'])) {
    $search = '%' . $_GET['query'] . '%';
    
    $query = "SELECT id, username, profile_picture_url FROM users WHERE username LIKE ? LIMIT 10";
    $stmt = $dbconn->prepare($query);
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo '<ul>';
        while ($row = $result->fetch_assoc()) {
            echo '<li><a href="profile.php?user_id=' . $row['id'] . '">  <img style="max-width:30px" src="php/img/' . htmlspecialchars($row['profile_picture_url']) . '" alt="profilkép"> ' . htmlspecialchars($row['username']) . '</a></li>';
        }
        echo '</ul>';
    } else {
        echo '<p>Nincs találat.</p>';
    }
}
?>
