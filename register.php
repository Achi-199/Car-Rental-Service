<?php
require_once 'data/storage.php';
session_start();

$name = $email = $password = $confirm_password = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    $admin_email = "admin@ikarrental.hu";

    if (empty($name)) {
        $errors['name'] = "Full Name is required.";
    }

    if (empty($email)) {
        $errors['email'] = "Email Address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email address.";
    } elseif ($email === $admin_email) {
        $errors['email'] = "You cannot use this email for registration.";
    }

    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters long.";
    }

    if (empty($confirm_password)) {
        $errors['confirm_password'] = "Please confirm your password.";
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $users_io = new JsonIO(__DIR__ . '/data/users.json');
        $current_users = $users_io->load();

        $existing_user = array_filter($current_users, fn($user) => $user['email'] === $email);
        if (!empty($existing_user)) {
            $errors['email'] = "Email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $new_user = [
                "name" => $name,
                "email" => $email,
                "password" => $hashed_password,
                "admin" => false
            ];

            $current_users[] = $new_user;

            $users_io->save($current_users);

            header("Location: login.php");
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
    <title>Register</title>
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
        <h2>Register</h2>
        <form method="post" novalidate>
            <div>
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($name) ?>" required>
                <?php if (!empty($errors['name'])): ?>
                    <p class="error"><?= htmlspecialchars($errors['name']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" required>
                <?php if (!empty($errors['email'])): ?>
                    <p class="error"><?= htmlspecialchars($errors['email']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
                <?php if (!empty($errors['password'])): ?>
                    <p class="error"><?= htmlspecialchars($errors['password']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
                <?php if (!empty($errors['confirm_password'])): ?>
                    <p class="error"><?= htmlspecialchars($errors['confirm_password']) ?></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-register">Register</button>
        </form>
    </main>
</body>

</html>