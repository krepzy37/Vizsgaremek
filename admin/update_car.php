<?php
require "../php/connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = (int)$_POST['id'];
    $name = $_POST['name'];
    $brand_id = (int)$_POST['brand_id'];

    // Kép feltöltése, ha van új fájl
    $image_url = NULL;
    if (!empty($_FILES["bg_image"]["name"])) {
        $target_dir = "../php/img/";
        $image_name = time() . "_" . basename($_FILES["bg_image"]["name"]);
        $target_file = $target_dir . $image_name;

        if (move_uploaded_file($_FILES["bg_image"]["tmp_name"], $target_file)) {
            $image_url = $image_name;
        }
    }

    if ($image_url) {
        $stmt = $dbconn->prepare("UPDATE cars SET name = ?, brand_id = ?, bg_image_url = ? WHERE id = ?");
        $stmt->bind_param("sisi", $name, $brand_id, $image_url, $id);
    } else {
        $stmt = $dbconn->prepare("UPDATE cars SET name = ?, brand_id = ? WHERE id = ?");
        $stmt->bind_param("sii", $name, $brand_id, $id);
    }

    if ($stmt->execute()) {
        echo "Autó sikeresen frissítve!";
    } else {
        echo "Hiba történt: " . $dbconn->error;
    }
}
?>
