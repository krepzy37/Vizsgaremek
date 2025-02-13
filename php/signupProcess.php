<?php
include_once "connect.php";

// Ürlap adatok biztonságos feldolgozása
$username = mysqli_real_escape_string($dbconn, $_POST['username']);
$email = mysqli_real_escape_string($dbconn, $_POST['email']);
$passwordrow = mysqli_real_escape_string($dbconn, $_POST['password']);

// Jelszó hash-elése
$password = password_hash($passwordrow, PASSWORD_DEFAULT);

if (!empty($username) && !empty($email) && !empty($password)) {
    // E-mail cím érvényes formátumának ellenőrzése
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Ellenőrzés, létezik-e már ez az e-mail cím
        $sql = mysqli_query($dbconn, "SELECT email FROM users WHERE email = '{$email}'");
        if (mysqli_num_rows($sql) > 0) {
            echo "$email - már létező e-mail cím!";
        } else {
            // Adatok beszúrása az adatbázisba
            $role = 'User'; // Role alapértelmezetten User
            $created_at = date('Y-m-d H:i:s'); // Aktuális időpont

            $sql2 = mysqli_query($dbconn, "INSERT INTO users (username, email, password_hash, role, created_at)
                                         VALUES ('{$username}', '{$email}', '{$password}', '{$role}', '{$created_at}')");

            if ($sql2) {
                echo "success";
            } else {
                echo "Valami hiba történt!";
            }
        }
    } else {
        echo "Érvénytelen e-mail cím!";
    }
} else {
    echo "Minden mezőt ki kell töltenie!";
}
?>
