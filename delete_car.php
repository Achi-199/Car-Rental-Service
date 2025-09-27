<?php
require_once 'data/storage.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

$cars_io = new JsonIO(__DIR__ . '/data/cars.json');
$cars_storage = new Storage($cars_io);

$bookings_io = new JsonIO(__DIR__ . '/data/bookings.json');
$bookings_storage = new Storage($bookings_io);

if (empty($_GET['id'])) {
    header("Location: admin_profile.php");
    exit;
}

$car_id = $_GET['id'];

$cars_storage->delete($car_id);

$bookings_storage->deleteMany(function ($booking) use ($car_id) {
    return $booking['car_id'] == $car_id;
});

header("Location: admin_profile.php");
exit;
?>
