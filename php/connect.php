<?php  

require_once __DIR__ . '/../vendor/autoload.php';

//környezeti változók betöltése a .env fájl használatához

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

//http fejlécet beállítjuk a megfelelő tartalom típussal és karakterkódolással

header('Content-Type: text/html; charset=utf-8');

//Az adatbázis kapcsolathoz szükséges adatok definiálása

/*$servername = getenv('DB_HOST');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');
$dbname = getenv('DB_NAME');*/

define('DBHOST', $_ENV['DBHOST']);

define('DBUSER', $_ENV['DBUSER']);

define('DBPASS', $_ENV['DBPASS']);

define('DBNAME', $_ENV['DBNAME']);


//adatbázis kapcsolat létrehozása, és esetleg hibakezelés

$dbconn = @mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME) or die("Hiba az adatbázis kapcsolatban: " . mysqli_connect_error());

// karakterkódolás beállítása az adatbázis kapcsolaton keresztül

mysqli_query($dbconn, "SET NAMES utf8");
