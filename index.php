<?php

require_once 'data/storage.php';
session_start();

function getCars()
{
   $io = new JsonIO(__DIR__ . '/data/cars.json');
   $storage = new Storage($io);
   return $storage->findAll();
}

function filterCars($filters)
{
   $cars = getCars();

   if (!empty($filters['price_min']) || !empty($filters['price_max'])) {
      $price_min = isset($filters['price_min']) && is_numeric($filters['price_min']) ? (float)$filters['price_min'] : 0;
      $price_max = isset($filters['price_max']) && is_numeric($filters['price_max']) ? (float)$filters['price_max'] : PHP_INT_MAX;

      if ($price_min < 0 || $price_max < 0) {
         die("Error: Price values must be non-negative.");
      }
      if ($price_min > $price_max) {
         die("Error: Minimum price cannot be greater than maximum price.");
      }

      $cars = array_filter($cars, function ($car) use ($price_min, $price_max) {
         $daily_price = (float)$car['daily_price_huf'];
         return $daily_price >= $price_min && $daily_price <= $price_max;
      });
   }

   if (!empty($filters['transmission'])) {
      $transmission = strtolower($filters['transmission']);
      if (!in_array($transmission, ['automatic', 'manual'], true)) {
         die("Error: Invalid transmission type.");
      }
      $cars = array_filter($cars, fn($car) => strtolower($car['transmission']) === $transmission);
   }

   if (!empty($filters['passengers'])) {
      $passengers = (int)$filters['passengers'];
      if ($passengers < 0) {
         die("Error: Number of passengers must be non-negative.");
      }
      $cars = array_filter($cars, fn($car) => $car['passengers'] >= $passengers);
   }

   if (!empty($filters['date_from']) && !empty($filters['date_until'])) {
      $date_from = DateTime::createFromFormat('Y-m-d', $filters['date_from']);
      $date_until = DateTime::createFromFormat('Y-m-d', $filters['date_until']);

      if (!$date_from || !$date_until) {
         die("Error: Invalid date format.");
      }
      if ($date_from > $date_until) {
         die("Error: Start date cannot be later than end date.");
      }

      $bookings = getBookings();

      $cars = array_filter($cars, function ($car) use ($date_from, $date_until, $bookings) {
         foreach ($bookings as $booking) {
            if ($booking['car_id'] == $car['id']) {
               $booking_start = new DateTime($booking['start_date']);
               $booking_end = new DateTime($booking['end_date']);

               if (($date_from <= $booking_end) && ($date_until >= $booking_start)) {
                  return false;
               }
            }
         }
         return true;
      });
   }

   return $cars;
}


function getBookings()
{
   $io = new JsonIO(__DIR__ . '/data/bookings.json');
   $storage = new Storage($io);
   return $storage->findAll();
}


$cars = getCars();
$filters = $_GET;
if (!empty($filters)) {
   $cars = filterCars($filters);
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="styles.css">
   <link rel="icon" href="porsche-6-logo-svgrepo-com.svg">
   <title>Car Rental</title>
</head>

<body>
   <header>
      <a href="index.php" class="btn-homepage">
         <h1>iKarRental</h1>
      </a>
      <nav>
         <?php if (isset($_SESSION['user'])): ?>
            <a href="profile.php" class="btn-login">Profile</a>
            <a href="logout.php" class="btn btn-logout">Logout</a>
         <?php else: ?>
            <a href="login.php" class="btn-login">Login</a>
            <a href="register.php" class="btn btn-register">Registration</a>
         <?php endif; ?>
      </nav>
   </header>

   <main class="container">
      <section class="hero">
         <h1>Rent cars easily!</h1>
         <a href="register.php" class="btn btn-register">Registration</a>
      </section>

      <section class="filter">
         <form method="get" class="filter-form" novalidate>
            <div>
               <label for="passengers">Seats</label>
               <input type="number" name="passengers" id="passengers" min="0" value="<?= htmlspecialchars($filters['passengers'] ?? '') ?>">
            </div>
            <div>
               <label for="date_from">From</label>
               <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
            </div>
            <div>
               <label for="date_until">Until</label>
               <input type="date" name="date_until" id="date_until" value="<?= htmlspecialchars($filters['date_until'] ?? '') ?>">
            </div>
            <div>
               <label for="transmission">Gear type</label>
               <select name="transmission" id="transmission">
                  <option value="" <?= empty($filters['transmission']) ? 'selected' : '' ?>>Any</option>
                  <option value="automatic" <?= (isset($filters['transmission']) && $filters['transmission'] === 'automatic') ? 'selected' : '' ?>>Automatic</option>
                  <option value="manual" <?= (isset($filters['transmission']) && $filters['transmission'] === 'manual') ? 'selected' : '' ?>>Manual</option>
               </select>
            </div>
            <div>
               <label for="price_min">Price (HUF)</label>
               <input type="number" name="price_min" id="price_min" placeholder="Min" value="<?= htmlspecialchars($filters['price_min'] ?? '') ?>">
               <input type="number" name="price_max" id="price_max" placeholder="Max" value="<?= htmlspecialchars($filters['price_max'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-filter">Filter</button>
         </form>
      </section>


      <section class="cars-grid">
         <?php foreach ($cars as $car): ?>
            <a href="details.php?id=<?= htmlspecialchars($car['id']) ?>" class="card-link">
               <div class="car-card">
                  <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
                  <div class="car-info">
                     <h4><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h4>
                     <p><?= htmlspecialchars($car['passengers']) ?> seats - <?= htmlspecialchars($car['transmission']) ?></p>
                     <p><strong><?= number_format($car['daily_price_huf'], 0) ?> Ft/day</strong></p>
                  </div>
                  <div class="btn-container">
                     <button class="btn btn-book">Book</button>
                  </div>
               </div>
            </a>
         <?php endforeach; ?>
      </section>

   </main>
</body>

</html>