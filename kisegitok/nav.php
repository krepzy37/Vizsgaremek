

<body ng-app="carApp" ng-controller="CarController">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a href="index.php"><img src="php/img/logo-placeholder-image.png" alt="Logo" style="max-width: 65px;"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Főoldal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Kapcsolat</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <?php if (isset($_SESSION['id'])): 
                        include_once "php/connect.php";
                        $id = $_SESSION['id'];
                        $query = mysqli_query($dbconn, "SELECT username, profile_picture_url FROM users WHERE id = $id");
                        $user = mysqli_fetch_assoc($query);
                    ?>
                        <a href="php/logoutProcess.php" class="btn btn-outline-light me-2">Kilépés</a>
                        <a href="profile.php" class="btn btn-outline-light">
                            <img src="php/img/<?php echo htmlspecialchars($user['profile_picture_url']); ?>" alt="Profilkép" style="width:30px; height:30px; border-radius:50%;"> 
                            <?php echo htmlspecialchars($user['username']); ?>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-light">Belépés</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="menu-btn">
    <div class="menu-btn__burger">
        <i class="fas fa-car"></i>
    </div>
</div>

<!-- Oldalsó Menü -->
<div class="side-menu">
    <div class="offcanvas-body">
        <ul class="nav nav-pills flex-column mb-auto">
            <li style="margin-top: 20px; margin-left: 10px; color: #4CAF50; margin-bottom:10px"><h2>Márkák</h2></li>
            <li ng-repeat="brand in brands track by brand.brand_id" class="nav-item" ng-class="{'active': selectedBrand === brand.brand_id}">
                <a href="#{{brand.brand_id}}Submenu" data-bs-toggle="collapse" class="nav-link text-light" aria-expanded="false" ng-click="selectBrand(brand.brand_id)">
                    <img ng-src="php/img/carlogos/{{brand.logo_url}}" alt="{{brand.name}} logo" class="me-2" style="height: 24px;">
                    {{brand.name}}
                </a>
                <ul class="collapse list-unstyled ps-3" id="{{brand.brand_id}}Submenu">
                    <li ng-repeat="model in brand.models" class="nav-item">
                        <a href="car.php?brand={{brand.name}}&model={{model.name}}" class="nav-link text-light">· {{model.name}}</a>
                    </li>
                </ul>
                <hr>
            </li>
        </ul>
    </div>
</div>


<main class="flex-grow-1 p-3">


<script>
    let app = angular.module('carApp', []);

    app.controller('CarController', function($scope, $http) {
        // Az API lekérése, hogy betöltsük a márkákat és modelleket
        $http.get('php/getBrands.php').then(function(response) {
            
            // Az adatokat hozzárendeljük az $scope.brands változóhoz
            $scope.brands = response.data;
        }, function(error) {
            console.error('Hiba történt a márkák lekérésekor:', error);
        });
    });



    document.addEventListener('DOMContentLoaded', function () {
    const menuBtn = document.querySelector('.menu-btn');
    const sideMenu = document.querySelector('.side-menu');
    let overlay = document.createElement('div');
    overlay.className = 'overlay';
    document.body.appendChild(overlay);

    menuBtn.addEventListener('click', function () {
    sideMenu.classList.toggle('active');  // Hozzáadja vagy eltávolítja a .active-t a sideMenu elemről
    menuBtn.classList.toggle('active');   // Hozzáadja vagy eltávolítja a .active-t a menuBtn elemről
    overlay.classList.toggle('active');   // Hozzáadja vagy eltávolítja a .active-t az overlay elemről
});


    // Overlay-re kattintva bezárjuk a menüt
    overlay.addEventListener('click', function () {
        sideMenu.classList.remove('active');
        menuBtn.classList.remove('active');
        overlay.classList.remove('active');
    });

    // Menüpontokra kattintva is bezárjuk a menüt
    const menuLinks = document.querySelectorAll('.side-menu nav ul li a');
    menuLinks.forEach(link => {
        link.addEventListener('click', function () {
            sideMenu.classList.remove('active');
            menuBtn.classList.remove('active');
            overlay.classList.remove('active');
        });
    });
});
</script>


