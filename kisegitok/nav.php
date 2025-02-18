

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
            <li><a href="index.php"><img src="php/img/logo-placeholder-image.png" alt="Logo" style="max-width:100px; margin-bottom: 15px"></a></li>
            <li ng-repeat="brand in brands track by brand.brand_id" class="nav-item">
                <a href="#{{brand.brand_id}}Submenu" data-bs-toggle="collapse" class="nav-link text-light" aria-expanded="false">
                    <img ng-src="php/img/carlogos/{{brand.logo_url}}" alt="{{brand.name}} logo" class="me-2" style="height: 24px;">
                    {{brand.name}}
                </a>
                <ul class="collapse list-unstyled ps-3" id="{{brand.brand_id}}Submenu">
                    <li ng-repeat="model in brand.models" class="nav-item">
                        <a href="car.php?brand={{brand.name}}&model={{model.name}}" class="nav-link text-light">- {{model.name}}</a>
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

<style>
/* Menü gomb újratervezése */
.menu-btn {
    position: fixed;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    background: #333;
    padding: 15px;
    width: 50px;
    text-align: center;
    border-radius: 0 10px 10px 0;
    cursor: pointer;
    z-index: 1000;
    box-shadow: 2px 2px 10px rgba(0,0,0,0.2);
    transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.side-menu.active ~ .menu-btn {
    left: 300px;
}

.menu-btn.active{
    left: 300px;
}
.menu-btn:hover {
    background: #4CAF50;
}

.menu-btn i {
    color: white;
    font-size: 1.5rem;
    transition: all 0.3s ease;
    display: block;
    margin: 0 auto;
}

/* Oldalsó menü újratervezése */
.side-menu {
    position: fixed;
    left: -300px;
    top: 0;
    width: 300px;
    height: 100vh;
    background: #333;
    transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 999;
    box-shadow: 2px 0 10px rgba(0,0,0,0.2);
    overflow-y: auto;
    
}

.side-menu.active {
    left: 0;
}

.side-menu nav ul {
    padding: 60px 0 0 0;
    margin: 0;
    list-style: none;
}

.side-menu nav ul li {
    padding: 0;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.side-menu nav ul li a {
    color: white;
    text-decoration: none;
    font-size: 1.1rem;
    padding: 20px 30px;
    display: block;
    transition: all 0.3s ease;
}

.side-menu nav ul li a i {
    margin-right: 10px;
    width: 20px;
}

.side-menu nav ul li:hover a {
    background: #4CAF50;
    padding-left: 40px;
}

/* Overlay újratervezése */
.overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    opacity: 0;
    z-index: 998;
    transition: opacity 0.3s ease;
}

.overlay.active {
    display: block;
    opacity: 1;
}

/* Az alapértelmezett scrollbar testreszabása */
.side-menu::-webkit-scrollbar {
    width: 10px; /* A scrollbar szélessége */
}

/* A scrollbar "foga", azaz a sáv, amivel görgetünk */
.side-menu::-webkit-scrollbar-thumb {
    background-color: #4CAF50; /* A thumb színe */
    border-radius: 10px; /* A szélét lekerekítjük */
    border: 3px solid #333; /* A sáv körüli keret */
}

/* A scrollbar háttérszíne */
.side-menu::-webkit-scrollbar-track {
    background-color: #333; /* A track színe (a háttér) */
    border-radius: 10px; /* A track szélét is lekerekíthetjük */
}

</style>
