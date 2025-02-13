<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("location: index.php");
    exit();
}
?>

<?php include_once "kisegitok/head.html"; ?>


<body>
<?php include_once "kisegitok/nav.php"; ?>
    <div id="login" class="content">

        <div class="wrapper">
     
            <section class="form login">

                <h1>Bejelentkezés</h1>               
                <form action="#">
                    <div class="error-txt"></div>

                    <div class="field input">
                        <h3><label for="email" style="padding-top: 20px;">E-mail:</label> </h3>
                        <input type="email" id="email" placeholder="E-mail cím" name="email">
                    </div>
                    <div class="field input">
                        <h3><label for="password" style="padding-top: 20px;">Jelszó:</label></h3>
                        <input type="password" id="password" placeholder="Jelszó" name="password">
                        <i class="fas fa-eye"></i>
                    </div>

                    <div class="filed button">
                        <input class="btn-primary btn-mybtn" type="submit" value="Bejelentkezés">
                    </div>
                </form>
                <div class="link">
                    <h4>Ha még nincs regisztrációja:</h4>
                    <a class="btn-primary btn-mybtn register" href="signup.php">Regisztráció</a>
                </div>
            </section>
        </div>
    </div>
    <script src="script/pass-show-hide.js"></script>
    <script src="script/login.js"></script>
    <?php include "kisegitok/end.html"?>
