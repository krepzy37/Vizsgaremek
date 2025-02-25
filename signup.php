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
<div class="container">


    <div id="signup" class="content loginBG">
        <div class="wrapper">
            <section class="signup form">
                <h1>Regisztráció</h1>
                <form action="#" enctype="multipart/form-data" autocomplete="off">
                    <div class="error-box">
                        <div class="error-txt"></div>
                    </div>
                    <div class="field input">
                        <h3 class="loginBox">Felhasználónév:</h3>
                        <input type="text" placeholder="Felhasználónév" name="username" title="Amit itt ad meg, azon a néven fognak a későbbiekben hivatkozni magára!">
                    </div>
                    <div class="field input">
                        <h3 class="loginBox">E-mail:</h3>
                        <input type="email" name="email" placeholder="E-mail cím" title="Valós emailcímet adjon meg! (Pl: valaki@pelda.com)">
                    </div>
                    <div class="pass field input">
                        <h3 class="loginBox">Jelszó:</h3>
                        <div class="input-container">
                            <input id="pass" type="password" name="password" placeholder="Jelszó"
                                title="A jelszónak legalább 8 karakterből kell állnia, tartalmaznia kell kis és nagy betűt,&#10; valamint egy speciális karaktert!">
                            <i class="fas fa-eye" id="togglePassword"></i>
                        </div>
                    </div>
                    <div class="field button">
                        <input class="btn-primary btn-mybtn" type="submit" value="Regisztráció">
                    </div>
                </form>
                <div class="link">
                    <h4>Ha már van regisztrált fiókja:</h4>
                    <a class="btn-primary btn-mybtn register" style="text-decoration: none; font-weight: bold;" href="login.php">Belépés</a>
                </div>
            </section>
        </div>
    </div>
</div>
<script src="script/pass-show-hide.js"></script>

<script src="script/signup.js"></script>

 <!-- <script src="script/signup_autofill.js"></script>-->
<?php include "kisegitok/end.html" ?>