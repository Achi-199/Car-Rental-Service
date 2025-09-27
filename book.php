<?php
require_once 'data/storage.php';
session_start();

if (!isset($_SESSION['user'])) {
   header("Location: login.php");
   exit;
}

if (empty($_SESSION['booking_data'])) {
   header("Location: index.php");
   exit;
}

$booking_data = $_SESSION['booking_data'];
unset($_SESSION['booking_data']); 

$car_id = $booking_data['car_id'];
$start_date = $booking_data['start_date'];
$end_date = $booking_data['end_date'];

if (empty($car_id) || empty($start_date) || empty($end_date)) {
   header("Location: details.php?id=$car_id&error=missing_fields");
   exit;
}

$io = new JsonIO(__DIR__ . '/data/bookings.json');
$storage = new Storage($io);

$conflict = $storage->findMany(function ($booking) use ($car_id, $start_date, $end_date) {
   $booking_start = new DateTime($booking['start_date']);
   $booking_end = new DateTime($booking['end_date']);
   $new_start = new DateTime($start_date);
   $new_end = new DateTime($end_date);

   return $booking['car_id'] === $car_id && ($new_start <= $booking_end && $new_end >= $booking_start);
});

if (!empty($conflict)) {
   header("Location: booking_failed.php?id=$car_id");
   exit;
}

$new_booking = [
   "car_id" => (string)$car_id,
   "start_date" => $start_date,
   "end_date" => $end_date,
   "user" => $_SESSION['user'],
   "id" => uniqid()
];

try {
   $storage->add($new_booking);

   header("Location: booking_success.php?id=$car_id&start=$start_date&end=$end_date");
   exit;
} catch (Exception $e) {
   file_put_contents('debug.log', "Error Adding Booking: " . $e->getMessage() . "\n", FILE_APPEND);

   header("Location: booking_failed.php?id=$car_id");
   exit;
}
