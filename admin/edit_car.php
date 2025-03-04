<?php
require "../php/connect.php";

if (!isset($_GET['id'])) {
    die("Nincs megadva autó ID!");
}

$id = (int)$_GET['id'];

$stmt = $dbconn->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$car = $result->fetch_assoc();

if (!$car) {
    die("Nincs ilyen autó!");
}

// Márkák lekérése a legördülő menühöz
$brands_query = "SELECT id, name FROM brands ORDER BY name ASC";
$brands_result = $dbconn->query($brands_query);
?>

<form action="update_car.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $car['id'] ?>">

    <label for="name">Autó neve:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($car['name']) ?>" required>

    <label for="brand">Márka:</label>
    <select name="brand_id">
        <?php while ($brand = $brands_result->fetch_assoc()): ?>
            <option value="<?= $brand['id'] ?>" <?= ($brand['id'] == $car['brand_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($brand['name']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label for="bg_image">Háttérkép módosítása:</label>
    <input type="file" name="bg_image" accept="image/*">

    <button type="submit">Mentés</button>
</form>
