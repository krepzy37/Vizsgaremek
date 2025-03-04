

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

<a href="index.php" class="btn-shine main-page-btn nav-link">Főoldal</a>

</li>
                   <!-- <li>
                    <form action="profile_search.php" method="GET" class="d-flex ms-auto ">
                    <input type="text" name="query" placeholder="Felhasználónév" class="form-control" id="searchInput" onkeyup="searchUsers(this.value)">
                    
                </form>
                    </li>-->
                    <li>
                    <div class="search-container " style="position: relative;">
    <div class="group">
        <svg viewBox="0 0 24 24" aria-hidden="true" class="search-icon">
            <g>
                <path d="M21.53 20.47l-3.66-3.66C19.195 15.24 20 13.214 20 11c0-4.97-4.03-9-9-9s-9 4.03-9 9 4.03 9 9 9c2.215 0 4.24-.804 5.808-2.13l3.66 3.66c.147.146.34.22.53.22s.385-.073.53-.22c.295-.293.295-.767.002-1.06zM3.5 11c0-4.135 3.365-7.5 7.5-7.5s7.5 3.365 7.5 7.5-3.365 7.5-7.5 7.5-7.5-3.365-7.5-7.5z"></path>
            </g>
        </svg>
        <input autocomplete="off" type="text" id="query" name="query" placeholder="Felhasználónév" class="inputS" onkeyup="searchUsers(this.value)">
    </div>
    <div  id="searchResults"></div> <!-- A találatok itt jelennek meg -->
</div>
                    
                </ul>
                <!-- Keresési sáv -->
                
                <div class="d-flex">
                <?php if (isset($_SESSION['id'])): 
    include_once "php/connect.php";
    $id = $_SESSION['id'];
    $query = mysqli_query($dbconn, "SELECT username, role, profile_picture_url FROM users WHERE id = $id");
    $loggedInUser = mysqli_fetch_assoc($query); // Eredeti $user helyett új változó
    if (isset($_GET['query'])) {
        $query = mysqli_real_escape_string($dbconn, $_GET['query']);
        $result = mysqli_query($dbconn, "SELECT id, username, profile_picture_url FROM users WHERE username LIKE '%$query%' LIMIT 5");
    
        if (mysqli_num_rows($result) > 0) {
            echo '<ul>';
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<li>
                        <a href="profile.php?user_id=' . $row['id'] . '">
                            <img src="php/img/' . htmlspecialchars($row['profile_picture_url']) . '" alt="Profilkép">
                            ' . htmlspecialchars($row['username']) . '
                        </a>
                      </li>';
            }
            echo '</ul>';
        } else {
            echo '<p style="padding: 10px;">Nincs találat</p>';
        }
    }
?>
<?php if ($loggedInUser['role'] == 'Moderator'): ?>
                        <a href="admin/moderator.php" class="btn btn-outline-light btn-warning text-dark me-2">Moderátori Panel</a>
                    <?php endif; ?>
    <a href="php/logoutProcess.php" class="btn btn-outline-light me-2">Kilépés</a>
    <a href="profile.php?user_id=<?php echo $id; ?>" class="btn btn-outline-light">
        <img src="php/img/<?php echo htmlspecialchars($loggedInUser['profile_picture_url']); ?>" alt="Profilkép" style="width:30px; height:30px; border-radius:50%;"> 
        <?php echo htmlspecialchars($loggedInUser['username']); ?>
    </a>
    
<?php else: ?>
    <a href="login.php" class="btn btn-outline-light">Belépés</a>
<?php endif; ?>

                </div>
            </div>
        </div>
    </nav>
    <div class="search-container" style="position: relative;">
    
    <div id="searchResults"></div>
    <div id="searchResults" class="position-absolute w-100 " style="top: 70px; z-index: 9998;"></div>
    <div class="menu-btn">
    <div class="menu-btn__burger">
        <i class="fas fa-car"></i>
    </div>
</div>
<style>
    /* Navbar és keresési sáv elrendezés */
    .navbar-nav {
        margin-right: 10px;
    }

    .d-flex.ms-auto {
        margin-left: auto;
    }


#searchResults {
    background-color: #fff;
    max-height: 200px;
    overflow-y: auto;
    margin-top: 5px;
    position: absolute;
    left: 0;
    width: 100%; /* A keresőmező szélességéhez igazodik */
    z-index: 9999;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    background-color:rgb(0, 0, 0);
}

/* Keresési találatok listája */
#searchResults ul {
    list-style: none;
    padding: 0;
    margin: 0;
    
}



/* Egyes találati elemek */
#searchResults li {
    padding: 10px;
    display: flex;
    align-items: center;
}

/* Profilkép a keresési találatokban */
#searchResults li img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    margin-right: 10px;
}

/* Linkek a találatok között */
#searchResults li a {
    text-decoration: none;
    color: #000;
    display: flex;
    align-items: center;
    width: 100%;
    color: #fff;
}

/* Hover effektus */
#searchResults li:hover {
    background-color:rgb(26, 25, 25);
}


    </style>
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
                        <a href="car.php?brand={{brand.name}}&model={{model.name}}" class="nav-link text-light">▶  {{model.name}}</a>
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



function searchUsers(query) {
                if (query.length === 0) {
                    document.getElementById("searchResults").innerHTML = "";
                    return;
                }

                let xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (this.readyState === 4 && this.status === 200) {
                        document.getElementById("searchResults").innerHTML = this.responseText;
                    }
                };
                xhr.open("GET", "search_users.php?query=" + query, true);
                xhr.send();
            }
</script>


