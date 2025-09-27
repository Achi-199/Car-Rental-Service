<?php
require_once 'data/storage.php';
session_start();

$admin_email = "admin@ikarrental.hu";
$admin_password = "admin";

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Both email and password are required.";
    } else {
        if ($email === $admin_email && $password === $admin_password) {
            $_SESSION['admin'] = true;
            header("Location: admin_profile.php");
            exit;
        }

        $users_io = new JsonIO(__DIR__ . '/data/users.json');
        $users_storage = new Storage($users_io);

        $user = $users_storage->findOne(['email' => $email]);

        if (!$user) {
            $error = "Email not found.";
        } elseif (!password_verify($password, $user['password'])) {
            $error = "Incorrect password.";
        } else {
            $_SESSION['user'] = $user['email'];
            header("Location: index.php");
            exit;
        }
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
    <title>Login</title>
</head>

<body>
    <header>
        <a href="index.php" class="btn-homepage">
            <h1>iKarRental</h1>
        </a>
        <nav>
            <a href="login.php" class="btn-login">Login</a>
            <a href="register.php" class="btn btn-register">Registration</a>
        </nav>

    </header>

    <main class="auth-form">
        <h2>Login</h2>
        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="post" novalidate>
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
            <button type="submit" class="btn btn-login">Login</button>
        </form>
    </main>
</body>

</html>