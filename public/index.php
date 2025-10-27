<?php
require_once __DIR__ . '/../config/session.php';

// Start session if not started (optional here, but fine to keep)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect logged-in users to their dashboards
if (!empty($_SESSION['role_id'])) {
    switch ((int)$_SESSION['role_id']) {
        case 1:
            header("Location: /maconglomo_app/admin/dashboard.php");
            exit;
        case 2:
            header("Location: /maconglomo_app/inventory/dashboard.php");
            exit;
        case 3:
            header("Location: /maconglomo_app/medrep/dashboard.php");
            exit;
    }
}

// Flash message via cookie
$successMessage = '';
if (!empty($_COOKIE['flash_success'])) {
    $successMessage = $_COOKIE['flash_success'];

    // Delete the cookie so it behaves like a one-time flash
    $cookiePath = '/maconglomo_app';
    setcookie('flash_success', '', time() - 3600, $cookiePath, '', !empty($_SERVER['HTTPS']), true);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Maconglomo App</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/index.css">
</head>

<body>

    <!-- Hero Section -->
    <div class="page-wrapper">
    <section class="hero">
      <div class="container text-center">
        <h1>Welcome to Maconglomo App</h1>
        <p>Streamline your inventory, clients, and representatives with ease.</p>
        <div class="hero-buttons">
          <a href="login.php" class="btn-outline">Login</a>
          <a href="register.php" class="btn-outline">Register</a>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Fade-out script for alerts -->
    <script>
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.classList.add('fade');
                setTimeout(() => alert.remove(), 500);
            }
        }, 3000); // 3 seconds before starting fade
    </script>
</body>
</html>
