<?php
require_once 'data/storage.php';
session_start();

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

if (empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$car = getCarById($_GET['id']);
if (!$car) {
    echo "Car not found!";
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    if (empty($start_date) || empty($end_date)) {
        $error = "Both start date and end date are required.";
    } elseif (!DateTime::createFromFormat('Y-m-d', $start_date) || !DateTime::createFromFormat('Y-m-d', $end_date)) {
        $error = "Invalid date format.";
    } elseif (new DateTime($end_date) < new DateTime($start_date)) {
        $error = "End date cannot be earlier than start date.";
    } else {
        $_SESSION['booking_data'] = [
            'car_id' => $car['id'],
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
        header("Location: book.php");
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
    <title><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></title>
</head>
<body>
    <header>
        <a href="index.php" class="btn-homepage"><h1>iKarRental</h1></a>
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

    <main class="car-details">
        <div class="car-container">
            <div class="car-image">
                <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
            </div>
            <div class="car-info">
                <h2><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h2>
                <p><strong>Fuel:</strong> <?= htmlspecialchars($car['fuel_type']) ?></p>
                <p><strong>Shifter:</strong> <?= htmlspecialchars($car['transmission']) ?></p>
                <p><strong>Year of manufacture:</strong> <?= htmlspecialchars($car['year']) ?></p>
                <p><strong>Number of seats:</strong> <?= htmlspecialchars($car['passengers']) ?></p>
                <p><strong>HUF <?= number_format($car['daily_price_huf'], 0) ?>/day</strong></p>
            </div>
        </div>

        <?php if (isset($_SESSION['user'])): ?>
            <form method="post" class="booking-form" novalidate>
                <?php if (!empty($error)): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                <input type="hidden" name="car_id" value="<?= htmlspecialchars($car['id']) ?>">
                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>" required>
                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>" required>
                <button type="submit" class="btn btn-book">Book it</button>
            </form>
        <?php else: ?>
            <p class="error">You need to <a href="login.php" class="btn">log in</a> to book this car.</p>
        <?php endif; ?>
    </main>
</body>
</html>
