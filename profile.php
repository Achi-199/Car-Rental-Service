<?php
require_once 'data/storage.php';
session_start();

if (!isset($_SESSION['user'])) {
   header("Location: login.php");
   exit;
}

$user_email = $_SESSION['user'];

$users_io = new JsonIO(__DIR__ . '/data/users.json');
$users_storage = new Storage($users_io);

$user = $users_storage->findOne(['email' => $user_email]);

if (!$user) {
   echo "User not found!";
   exit;
}

$reservations_io = new JsonIO(__DIR__ . '/data/bookings.json');
$reservations_storage = new Storage($reservations_io);
$all_reservations = $reservations_storage->findAll();

$reservations = array_filter($all_reservations, function ($reservation) use ($user_email) {
   return $reservation['user'] === $user_email;
});

$cars_io = new JsonIO(__DIR__ . '/data/cars.json');
$cars_storage = new Storage($cars_io);

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="styles.css">
   <link rel="icon" href="porsche-6-logo-svgrepo-com.svg">
   <title>Profile</title>
</head>

<body>
   <header>
      <a href="index.php" class="btn-homepage">
         <h1>iKarRental</h1>
      </a>
      <nav>
         <a href="logout.php" class="btn btn-logout">Logout</a>
      </nav>
   </header>

   <main class="profile">
      <div class="user-info">
         <img src="https://via.placeholder.com/150" alt="Profile Picture" class="profile-pic">
         <h2>Logged in as <?= htmlspecialchars($user['name']) ?></h2>
      </div>

      <section class="reservations">
         <h3>My Reservations</h3>
         <div class="reservations-grid">
            <?php foreach ($reservations as $reservation): ?>
               <?php
               $car = $cars_storage->findOne(['id' => $reservation['car_id']]);

               if (!$car) {
                  continue; 
               }
               ?>
               <div class="reservation-card">
                  <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
                  <div class="reservation-info">
                     <h4><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h4>
                     <p><?= htmlspecialchars($car['passengers']) ?> seats - <?= htmlspecialchars($car['transmission']) ?></p>
                     <p><?= htmlspecialchars($reservation['start_date']) ?> - <?= htmlspecialchars($reservation['end_date']) ?></p>
                  </div>
               </div>
            <?php endforeach; ?>
         </div>
      </section>
   </main>
</body>

</html>