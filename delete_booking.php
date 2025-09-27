<?php
require_once 'data/storage.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin_profile.php");
    exit;
}

$booking_id = $_GET['id'];

$bookings_io = new JsonIO(__DIR__ . '/data/bookings.json');
$bookings_storage = new Storage($bookings_io);

$bookings_storage->delete($booking_id);

header("Location: admin_profile.php");
exit;
?>
