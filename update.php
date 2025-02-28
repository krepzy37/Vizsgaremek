<?php 
session_start();

if (!isset($_SESSION['id'])) {
    header("location:index.php");
    exit;
}

include_once "php/connect.php";

// Bejelentkezett felhasználó azonosítója
$user_id = $_SESSION['id'];

// Felhasználói adatok lekérése
$sql = "SELECT id, username, email, profile_picture_url FROM users WHERE id = ?";
$stmt = mysqli_prepare($dbconn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$row = mysqli_fetch_assoc($result)) {
    echo "Felhasználó nem található!";
    exit;
}

$username = $row['username'];
$email = $row['email'];
$profile_picture = $row['profile_picture_url'];

?>
<?php include_once "kisegitok/head.html"; ?>
<body>

<div class="content">
    <div class="wrapper">
        <section class="form signup">
            <header>Adatok módosítása</header>
            <form action="process_update.php" method="POST" enctype="multipart/form-data" autocomplete="off">
                <div class="error-txt"></div>

                <!-- Felhasználói azonosító (rejtett mező) -->
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

                <div class="field input">
                    <label>Felhasználónév:</label>
                    <input type="text" name="username" value="<?php print($username); ?>" required>
                </div>

                <div class="field input">
                    <label>E-mail:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="field input">
                    <label>Jelszó (ha nem szeretné megváltoztatni, hagyja üresen):</label>
                    <input type="password" name="password" placeholder="Új jelszó">
                </div>

                <div class="field image">
                    <label>Profil kép:</label>
                    <input type="file" name="image">
                    <?php if (!empty($profile_picture)): ?>
                        <p>Jelenlegi kép: <img src="php/img/<?php echo $profile_picture; ?>" width="100"></p>
                    <?php endif; ?>
                </div>

                <div class="field button">
                    <input type="submit" value="Módosítások mentése">
                </div>
            </form>
            
            <div class="link">Meggondolta magát? <a href="profile.php?user_id=<?php echo $user_id; ?>">Vissza a profilra</a></div>
        </section>
    </div>
</div>

<script src="script/pass-show-hide.js"></script>
<script src="script/update.js"></script>
</body>
</html>
