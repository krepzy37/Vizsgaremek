<?php 
//munkamenet indítása a bejelentkezett felhasználó részére
session_start();

//csatlakozás az adatbázishoz
include_once "connect.php";
//frontend oldalról érkező adatok
$writtenemail = mysqli_real_escape_string($dbconn, $_POST['email']);
//print_r($writtenemail); ellenőrzés megkapom e a beírt 
$writtenpass = mysqli_real_escape_string($dbconn, $_POST['password']);
//print_r($writtenpass);

//e-mail cím ellenőrzése
$sql = "SELECT * FROM users WHERE email LIKE '{$writtenemail}'";
$result = mysqli_query($dbconn, $sql) or die(mysqli_error($dbconn));
        if(mysqli_num_rows($result) != 1){
            echo "Hibás e-mail címet adott meg!";
            return;
        }
$row = mysqli_fetch_assoc($result);
//var_dump($row);
$hash = $row['password_hash'];
//print_r($password);


if(!empty($writtenemail) && !empty($writtenpass)){

    if (!password_verify($writtenpass, $hash)) {
        echo "Hibás bejelntkezési adatok.";
        return;
    } 

    $sql2 = mysqli_query($dbconn, "SELECT * FROM users WHERE email LIKE '{$writtenemail}' AND password_hash = '{$hash}'");
    if(mysqli_num_rows($sql2) > 0){
        $row = mysqli_fetch_assoc($sql2);
        $status = "Online";
        //update user állapot (status)

        /**A felhasználó automatikus kijelentkeztetése, ha csak bezárja a böngésző ablakot, bonyolultabb folyamat, mivel a böngésző nem küldhet értesítést a szervernek, amikor a felhasználó egyszerűen bezárja az ablakot. Ehelyett azt kell vizsgálnunk, hogy a felhasználó az elmúlt időszakban végzett-e valamilyen műveletet az oldalon. Ehhez meg kell jegyezni az utolsó aktivitás idejét, és ha ez az idő meghalad egy bizonyos határt, akkor kijelentkezünk. */

        $sql2 = mysqli_query($dbconn, "UPDATE users SET status = '{$status}'
                WHERE id = {$row['id']}");
                if($sql2){
                    $_SESSION['id'] = $row['id'];
                    $_SESSION['last_activity'] = time(); // Utolsó aktivitás ideje
                    echo "success";
                }      
    }else{
        echo "Helytelen jelszót adott meg!";
    }
}else{
    echo "Minden mezőt ki kell töltenie!";
}
/*Ebben a kódban hozzáadtam egy ellenőrzést az automatikus kijelentkezéshez, amely figyeli az utolsó aktivitást. Ha a felhasználó nem hajt végre semmilyen műveletet az oldalon egy meghatározott inaktivitási idő alatt, akkor a szerver kijelentkezteti a felhasználót és átirányítja a kijelentkezési oldalra.*/



?>