<?php  
//betöltjük a tesztelendő fájlt
require_once __DIR__ . '/connect.php';

/**
 * Teszteljük az adatbázis kapcsolatot
 * @param mysqli $dbconn adatbázis kapcsolat
 */

 function testDatabaseConnection($dbconn) {
    /* if ($dbconn->connect_error) {
         die('Az adatbázis kapcsolat sikertelen: '. $dbconn->connect_error);
     }
     echo 'Az adatbázis kapcsolat sikeresen lett megnyitva.';*/

     if($dbconn){
        echo 'Az adatbázis kapcsolat sikeres';
     }else{
        echo 'Az adatbázis kapcsolat sikertelen:' . mysqli_connect_error();
     }
 }

 //karakter kódolás tesztelése

 function testCharacterEncoding($dbconn) {
    $result = mysqli_query($dbconn, "SHOW VARIABLES LIKE 'character_set_connection';");
    $row = mysqli_fetch_assoc($result);
    if($row['Value'] === 'utf8'){
        echo 'A karakterkódolás UTF-8-ra beállításra került. <br>';
    }else{
        echo 'A karakterkódolás nem UTF-8-ra lett beállítva. A jelenlegi 
        karakter beállítás:' . htmlspecialchars($row['Value']) . "<br>" ;
    }
 }

 //környezeti változók megfelelő betöltődése

 function testEnvironmentVariables($dbconn) {
    $requireKeys = ['DBHOST', 'DBUSER', 'DBPASS', 'DBNAME'];
    $missingKeys = [];

    foreach($requireKeys as $key){
        if(!isset($_ENV[$key])){
            $missingKeys[] = $key;
        }
    }

    if(empty($missingKeys)){
        echo 'Minden szükséges környezeti változó beállításra került. <br>';
    } else {
        echo 'A következő környezeti változók hiányoznak: '. implode(', ', $missingKeys) . "<br>";
    }
 }

 //Tesztek futtatása a függvények meghívásán keresztül
 echo "<h2>Adatbázis kapcsolat tesztelése</h2>";
 testDatabaseConnection($dbconn);

 echo "<h2>Karakterkódolás tesztelése</h2>";
 testCharacterEncoding($dbconn);
 
 echo "<h2>Környezeti változók tesztelése</h2>";
 testEnvironmentVariables($dbconn);

 //adatbázis kapcsolat lezárása
 mysqli_close($dbconn);