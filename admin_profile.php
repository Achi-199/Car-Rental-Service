<?php
require_once 'data/storage.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

$bookings_io = new JsonIO(__DIR__ . '/data/bookings.json');
$bookings_storage = new Storage($bookings_io);
$all_bookings = $bookings_storage->findAll();

$cars_io = new JsonIO(__DIR__ . '/data/cars.json');
$cars_storage = new Storage($cars_io);
$all_cars = $cars_storage->findAll();

$filters = $_GET;

function filterCars($cars, $filters)
{
    if (!empty($filters['price_min']) || !empty($filters['price_max'])) {
        $price_min = isset($filters['price_min']) && is_numeric($filters['price_min']) ? (float)$filters['price_min'] : 0;
        $price_max = isset($filters['price_max']) && is_numeric($filters['price_max']) ? (float)$filters['price_max'] : PHP_INT_MAX;

        $cars = array_filter($cars, function ($car) use ($price_min, $price_max) {
            $daily_price = (float)$car['daily_price_huf'];
            return $daily_price >= $price_min && $daily_price <= $price_max;
        });
    }

    if (!empty($filters['transmission'])) {
        $transmission = strtolower($filters['transmission']);
        $cars = array_filter($cars, fn($car) => strtolower($car['transmission']) === $transmission);
    }

    if (!empty($filters['passengers'])) {
        $passengers = (int)$filters['passengers'];
        $cars = array_filter($cars, fn($car) => $car['passengers'] >= $passengers);
    }

    return $cars;
}

function filterBookings($bookings, $filters)
{
    if (!empty($filters['date_from']) || !empty($filters['date_until'])) {
        $date_from = !empty($filters['date_from']) ? new DateTime($filters['date_from']) : null;
        $date_until = !empty($filters['date_until']) ? new DateTime($filters['date_until']) : null;

        $bookings = array_filter($bookings, function ($booking) use ($date_from, $date_until) {
            $booking_start = new DateTime($booking['start_date']);
            $booking_end = new DateTime($booking['end_date']);

            if ($date_from && $booking_end < $date_from) {
                return false;
            }

            if ($date_until && $booking_start > $date_until) {
                return false;
            }

            return true;
        });
    }

    return $bookings;
}

$cars = $all_cars;
$bookings = $all_bookings;

if (!empty($filters)) {
    $cars = filterCars($all_cars, $filters);
    $bookings = filterBookings($all_bookings, $filters);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="porsche-6-logo-svgrepo-com.svg">
    <title>Admin Panel</title>
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

    <main class="admin-profile">
        <h2 id="admin-text">Admin Panel</h2>

        <section class="filter">
            <form method="get" class="filter-form" novalidate>
                <div>
                    <label for="passengers">Seats</label>
                    <input type="number" name="passengers" id="passengers" min="0" value="<?= htmlspecialchars($filters['passengers'] ?? '') ?>">
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
                <div>
                    <label for="date_from">From</label>
                    <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                </div>
                <div>
                    <label for="date_until">Until</label>
                    <input type="date" name="date_until" id="date_until" value="<?= htmlspecialchars($filters['date_until'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-filter">Filter</button>
            </form>
        </section>
        <a href="add_car.php" class="btn btn-add">Add New Car</a>
        <section class="cars-management">
            <div class="cars-grid">
                <?php foreach ($cars as $car): ?>
                    <div class="car-card">
                        <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
                        <div class="car-info">
                            <h4><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h4>
                            <p><?= htmlspecialchars($car['passengers']) ?> seats - <?= htmlspecialchars($car['transmission']) ?></p>
                            <p><strong><?= number_format($car['daily_price_huf'], 0) ?> Ft/day</strong></p>
                        </div>
                        <a href="edit_car.php?id=<?= htmlspecialchars($car['id']) ?>" class="btn btn-edit">Edit</a>
                        <a href="delete_car.php?id=<?= htmlspecialchars($car['id']) ?>" class="btn btn-delete">Delete</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="bookings-management">
            <h3>All Bookings</h3>
            <table>
                <thead>
                    <tr>
                        <th>Car</th>
                        <th>User</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <?php
                        $car = $cars_storage->findOne(['id' => $booking['car_id']]);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></td>
                            <td><?= htmlspecialchars($booking['user']) ?></td>
                            <td><?= htmlspecialchars($booking['start_date']) ?></td>
                            <td><?= htmlspecialchars($booking['end_date']) ?></td>
                            <td>
                                <a href="delete_booking.php?id=<?= htmlspecialchars($booking['id']) ?>" class="btn btn-delete">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>

</html>
