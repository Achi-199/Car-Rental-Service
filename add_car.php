<?php
require_once 'data/storage.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = trim($_POST['brand']);
    $model = trim($_POST['model']);
    $year = trim($_POST['year']);
    $transmission = trim($_POST['transmission']);
    $fuel_type = trim($_POST['fuel_type']);
    $passengers = trim($_POST['passengers']);
    $daily_price_huf = trim($_POST['daily_price_huf']);
    $image = trim($_POST['image']);

    if (empty($brand) || empty($model) || empty($year) || empty($transmission) || empty($fuel_type) || empty($passengers) || empty($daily_price_huf) || empty($image)) {
        $error = "All fields are required.";
    } elseif (!in_array(strtolower($transmission), ['automatic', 'manual'])) {
        $error = "Transmission type must be either 'Automatic' or 'Manual'.";
    } elseif (!filter_var($image, FILTER_VALIDATE_URL)) {
        $error = "Invalid image URL.";
    } elseif (!is_numeric($year) || $year < 1900 || $year > (int)date("Y")) {
        $error = "Please enter a valid year.";
    } elseif (!is_numeric($passengers) || $passengers <= 0) {
        $error = "Number of passengers must be a positive number.";
    } elseif (!is_numeric($daily_price_huf) || $daily_price_huf <= 0) {
        $error = "Daily price must be a positive number.";
    } else {
        $cars_io = new JsonIO(__DIR__ . '/data/cars.json');
        $cars_storage = new Storage($cars_io);

        $new_car = [
            "id" => uniqid(), 
            "brand" => htmlspecialchars($brand), 
            "model" => htmlspecialchars($model),
            "year" => (int)$year, 
            "transmission" => ucfirst(strtolower($transmission)), 
            "fuel_type" => htmlspecialchars($fuel_type),
            "passengers" => (int)$passengers,
            "daily_price_huf" => (int)$daily_price_huf,
            "image" => htmlspecialchars($image)
        ];

        $cars_storage->add($new_car);

        header("Location: admin_profile.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="porsche-6-logo-svgrepo-com.svg">
    <title>Add New Car</title>
</head>
<body>
    <header>
        <a href="index.php" class="btn-homepage"><h1>iKarRental</h1></a>
    </header>

    <main class="auth-form">
        <h2>Add New Car</h2>
        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="post" novalidate>
            <label for="brand">Brand</label>
            <input type="text" name="brand" id="brand" value="<?= htmlspecialchars($brand ?? '') ?>" required>
            <label for="model">Model</label>
            <input type="text" name="model" id="model" value="<?= htmlspecialchars($model ?? '') ?>" required>
            <label for="year">Year</label>
            <input type="number" name="year" id="year" value="<?= htmlspecialchars($year ?? '') ?>" required>
            <label for="transmission">Transmission</label>
            <input type="text" name="transmission" id="transmission" value="<?= htmlspecialchars($transmission ?? '') ?>" required>
            <label for="fuel_type">Fuel Type</label>
            <input type="text" name="fuel_type" id="fuel_type" value="<?= htmlspecialchars($fuel_type ?? '') ?>" required>
            <label for="passengers">Passengers</label>
            <input type="number" name="passengers" id="passengers" value="<?= htmlspecialchars($passengers ?? '') ?>" required>
            <label for="daily_price_huf">Daily Price (HUF)</label>
            <input type="number" name="daily_price_huf" id="daily_price_huf" value="<?= htmlspecialchars($daily_price_huf ?? '') ?>" required>
            <label for="image">Image URL</label>
            <input type="text" name="image" id="image" value="<?= htmlspecialchars($image ?? '') ?>" required>
            <button type="submit" class="btn btn-add">Add Car</button>
        </form>
    </main>
</body>
</html>
