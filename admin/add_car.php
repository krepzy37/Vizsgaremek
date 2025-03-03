<?php
require "../php/connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $brand_id = $_POST['brand_id'];
    $bg_image_url = "bg-def.png"; // Alapértelmezett kép

    // Ha feltöltöttek képet
    if (!empty($_FILES["bg_image"]["name"])) {
        $target_dir = "../php/img/";
        $image_name = time() . "_" . basename($_FILES["bg_image"]["name"]);
        $target_file = $target_dir . $image_name;
        
        if (move_uploaded_file($_FILES["bg_image"]["tmp_name"], $target_file)) {
            $bg_image_url = $image_name; // Mentjük az adatbázisba
        }
    }

    // Adatok beszúrása
    $stmt = $dbconn->prepare("INSERT INTO cars (name, brand_id, bg_image_url) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $name, $brand_id, $bg_image_url);
    if ($stmt->execute()) {
        echo "Autó sikeresen hozzáadva!";
    } else {
        echo "Hiba történt: " . $dbconn->error;
    }
}
?>
