<?php
require_once 'data/storage.php';
session_start();

if (empty($_GET['id']) || empty($_GET['start']) || empty($_GET['end'])) {
    echo "Booking information is incomplete!";
    exit;
}

$car_id = $_GET['id'];
$start_date = $_GET['start'];
$end_date = $_GET['end'];

$car = getCarById($car_id);

if (!$car) {
    echo "Car not found!";
    exit;
}

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="porsche-6-logo-svgrepo-com.svg">
    <title>Booking Successful</title>
</head>
<body>
    <header>
        <a href="index.php" class="btn-homepage"><h1>iKarRental</h1></a>
        <nav>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main class="success">
        <h2>Successful Booking!</h2>
        <p>Your booking for <strong><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></strong> from <strong><?= htmlspecialchars($start_date) ?></strong> to <strong><?= htmlspecialchars($end_date) ?></strong> has been confirmed.</p>
        <a href="profile.php" class="btn btn-profile">My Profile</a>
    </main>
</body>
</html>
