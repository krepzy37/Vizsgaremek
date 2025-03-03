<?php
require_once 'connect.php'; // A kapcsolat létrehozásához

// SQL lekérdezés, hogy lekérd a márkákat és a hozzájuk tartozó modelleket
$query = "SELECT b.id AS brand_id, b.name AS brand_name, c.name AS model_name, b.logo_url AS logo_url
          FROM brands b
          LEFT JOIN cars c ON b.id = c.brand_id";  // A modelleket a cars táblából

$result = mysqli_query($dbconn, $query);

// Hiba kezelése
if (!$result) {
    die("Hiba az adatbázis lekérdezésében: " . mysqli_error($dbconn));
}

$brands = [];
while ($row = mysqli_fetch_assoc($result)) {
    $brandName = $row['brand_name'];
    $brandLogo_url = $row['logo_url'];
    //$brandName = strtr($brandName, ['Š' => 'S', 'š' => 's']); Skoda rendezés problémák
    // URL-kompatibilis azonosító létrehozása
    $brandId = strtolower(str_replace(['á', 'é', 'í', 'ó', 'ö', 'ő', 'ú', 'ü', 'ű', ' '], ['a', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'u', '-'], $brandName));

    if (!isset($brands[$brandName])) {
        //$brands[$brandName] = ['name' => $brandName, 'brand_id' => $brandId, 'models' => []];
        $brands[$brandName] = ['name' => $brandName, 'brand_id' => $brandId, 'models' => [], 'logo_url' => $brandLogo_url]; // Include logo_url
    }
    if ($row['model_name']) {
        $brands[$brandName]['models'][] = ['name' => $row['model_name']];
    }
}

$collator = collator_create('hu_HU');  // Magyar nyelvi beállítások
uksort($brands, function($a, $b) use ($collator) {
    return collator_compare($collator, $a, $b);
});

// A modelleket betűrendbe rendezzük
foreach ($brands as &$brand) {
    usort($brand['models'], function ($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
}

// Az adatokat JSON formátumban visszaadjuk
echo json_encode(array_values($brands));
?>
