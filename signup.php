<?php
// Munkamenet kezelése
session_start();

// Ellenőrzi, hogy a felhasználó már be van-e jelentkezve
if (isset($_SESSION['user_id'])) {
    header("location:index.php");
}
?>
<?php include_once "kisegitok/head.html"; ?>


<?php include_once "kisegitok/nav.php"; ?>
    <div class="content">
        <div class="wrapper">
            <section class="signup form">
                <header>
                    regisztráció
                </header>
                <form action="#" enctype="multipart/form-data" autocomplete="off">
                    <div class="error-box">
                        <div class="error-txt"></div>
                    </div>
                    <div class="field input">
                        <label>Felhasználónév:</label>
                        <input type="text" placeholder="Felhasználónév" name="username">
                    </div>
                    <div class="field input">
                        <label>E-mail:</label>
                        <input type="email" name="email" placeholder="E-mail cím">
                    </div>
                    <div class="field input">
                        <label>Jelszó:</label>
                        <input type="password" name="password" placeholder="Jelszó"><i class="fas fa-eye"></i>
                    </div>
                    <div class="field button">
                        <input type="submit" value="Regisztráció">
                    </div>
                </form>
                <div class="link">Ha már van regisztrációja, lépjen be: <a href="login.php">Belépés</a></div>
            </section>
        </div>
    </div>

    <script src="script/pass-show-hide.js"></script>
    
    <script src="script/signup.js"></script>
    
   <!-- <script src="script/signup_autofill.js"></script>-->
    <?php include "kisegitok/end.html"?>