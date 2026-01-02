<?php
if (session_status() == PHP_SESSION_NONE) session_start();
// Ensure DB connection is available to all pages that include header
if (!isset($conn)) {
    include_once __DIR__ . '/db.php';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header">
    <div class="wrap">
        <h1 class="brand"><a href="index.php">My Shop</a></h1>
        <nav>
            <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
                <a href="products.php">Shop</a>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
                    <a href="cart.php">Cart (<?php echo isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'qty')) : 0; ?>)</a>
                <?php endif; ?>
                <a href="dashboard.php">Dashboard</a>
                <!-- Admin management moved to dashboard; no separate Admin link -->
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main>
