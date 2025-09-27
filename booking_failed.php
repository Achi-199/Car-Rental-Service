<?php
require_once 'data/storage.php';
session_start();

$car_id = $_GET['id'];
$car = getCarById($car_id);

function getCarById($id) {
    $io = new JsonIO(__DIR__ . '/data/cars.json');
    $storage = new Storage($io);
    $cars = $storage->findAll();

    foreach ($cars as $car) {
        if ((string)$car['id'] === (string)$id) { 
            return $car;
        }
    }

    return null;
}

if (!$car) {
    header("Location: index.php"); 
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="porsche-6-logo-svgrepo-com.svg">
    <title>Booking Failed</title>
</head>
<body>
    <header>
        <a href="index.php" class="btn-homepage"><h1>iKarRental</h1></a>
        <nav>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main class="failure">
        <h2>Booking failed!</h2>
        <p>Unfortunately, <strong><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></strong> is unavailable for the selected dates.</p>
        <a href="details.php?id=<?= htmlspecialchars($car['id']) ?>" class="btn btn-back">Back to the vehicle</a>
    </main>
</body>
</html>
