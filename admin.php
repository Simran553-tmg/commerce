<?php
if (session_status() == PHP_SESSION_NONE) session_start();
header('Location: dashboard.php');
exit();

// Require admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch some stats
$uStmt = $conn->query('SELECT COUNT(*) as cnt FROM users');
$usersCount = $uStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
$pStmt = $conn->query('SELECT COUNT(*) as cnt FROM products');
$productsCount = $pStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;

?>

<div class="container">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <h2>Admin Dashboard</h2>
        <div><a href="logout.php" class="btn">Logout</a></div>
    </div>

    <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> 👋</p>

    <div class="cards" style="margin-top:20px;">
        <a href="admin_products.php" class="card">Manage Products<br><small><?php echo (int)$productsCount; ?> items</small></a>
        <a href="admin_categories.php" class="card">Categories</a>
        <a href="#" class="card">Manage Users<br><small><?php echo (int)$usersCount; ?> users</small></a>
        <a href="admin_orders.php" class="card">Orders<br><small>—</small></a>
        <a href="#" class="card">Settings</a>
    </div>

    <section style="margin-top:24px;">
        <h3>Quick Links</h3>
        <div style="display:flex;gap:12px;margin-top:8px;">
            <a class="btn" href="admin_products.php">Add Product</a>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>
