

<body ng-app="carApp" ng-controller="CarController">
    <nav>
        <div id="logo">
            <a href="../index.php"><button>logo</button></a>
            
        </div>
        <div class="navigation">
            <div class="nav">
                    
                    <a href="../index.php"><button>Főoldal</button></a>
                    <a href="../contact.php"><button>Kapcsolat</button></a>
                    
        </div>
        
        <?php if (isset($_SESSION['id'])): 
        
        include_once "../php/connect.php";
        $id = $_SESSION['id'];
        
        $query = mysqli_query($dbconn, "SELECT username, profile_picture_url FROM users WHERE id = $id");
        $user = mysqli_fetch_assoc($query);
        ?>
        
        <a href="../php/logoutProcess.php">
            <button id="logout">Kilépés</button>
        </a>
        <a href="../profile.php">
            <button id="profile">
                <img src="../php/img/<?php echo htmlspecialchars($user['profile_picture_url']); ?>" alt="Profilkép" style="width:30px; height:30px; border-radius:50%;"> 
                <?php echo htmlspecialchars($user['username']); ?>
            </button>
        </a>
        <?php else: ?>
            <a href="../login.php"><button  id="login">Belépés</button></a>
        <?php endif; ?>
        

</div>

</nav>


<aside class="d-flex flex-column flex-shrink-0 p-3 text-bg-dark">
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li ng-repeat="brand in brands track by brand.brand_id">
            <a href="#{{brand.brand_id}}Submenu" data-bs-toggle="collapse" class="nav-link text-white" aria-expanded="false">
                <img ng-src="../php/img/carlogos/{{brand.logo_url}}" alt="{{brand.name}} logo" class="me-2" style="height: 24px;">
                {{brand.name}}
            </a>
            <ul class="collapse list-unstyled ps-3" id="{{brand.brand_id}}Submenu">
                <li ng-repeat="model in brand.models">
                    <a href="{{brand.name}}-{{model.name}}.php" class="nav-link text-white">- {{model.name}}</a>
                </li>
            </ul>
            <hr>
        </li>
    </ul>
</aside>


<main>

<script>
    let app = angular.module('carApp', []);

    app.controller('CarController', function($scope, $http) {
        // Az API lekérése, hogy betöltsük a márkákat és modelleket
        $http.get('../php/getBrands.php').then(function(response) {
            
            // Az adatokat hozzárendeljük az $scope.brands változóhoz
            $scope.brands = response.data;
        }, function(error) {
            console.error('Hiba történt a márkák lekérésekor:', error);
        });
    });
</script>