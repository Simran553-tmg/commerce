<?php
include 'header.php';

// Allow guests to view a limited dashboard with CTAs
$loggedIn = isset($_SESSION['user_id']);
$username = $loggedIn ? $_SESSION['user_name'] : null;

// Fetch recent products to show on user dashboard
$stmt = $conn->prepare('SELECT id, name, price, image FROM products WHERE approved = 1 ORDER BY created_at DESC LIMIT 6');
$stmt->execute();
$recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <?php
        $uStmt = $conn->query('SELECT COUNT(*) as cnt FROM users');
        $usersCount = $uStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
        $pStmt = $conn->query('SELECT COUNT(*) as cnt FROM products');
        $productsCount = $pStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
        ?>
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <h2>Admin Dashboard</h2>
            <div><a href="logout.php" class="btn">Logout</a></div>
        </div>

        <!-- <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> 👋</p> -->

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
        <?php
        // Show products list for admin directly in the dashboard
        $products = $conn->query('SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC')->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <section style="margin-top:24px;">
            <h3>Products List</h3>
            <table style="width:100%;border-collapse:collapse;margin-top:8px;">
                <thead>
                    <tr style="text-align:left;border-bottom:1px solid #ddd;"><th style="padding:6px;">ID</th><th style="padding:6px;">Image</th><th style="padding:6px;">Name</th><th style="padding:6px;">Price</th><th style="padding:6px;">Stock</th><th style="padding:6px;">Category</th><th style="padding:6px;">Status</th><th style="padding:6px;">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr style="border-bottom:1px solid #f1f1f1;">
                            <td style="padding:6px;vertical-align:middle;"><?php echo (int)$p['id']; ?></td>
                            <td style="padding:6px;width:90px;"><img src="<?php echo htmlspecialchars($p['image'] ?: 'photos/default.png'); ?>" style="width:80px;height:50px;object-fit:cover;border-radius:4px;"></td>
                            <td style="padding:6px;vertical-align:middle;">
                                <?php echo htmlspecialchars($p['name']); ?>
                                <?php if (!(int)$p['approved']): ?>
                                    <br><small style="color:orange;">(Pending approval)</small>
                                <?php endif; ?>
                            </td>
                            <td style="padding:6px;vertical-align:middle;">Rs.<?php echo number_format($p['price'],2); ?></td>
                            <td style="padding:6px;vertical-align:middle;"><?php echo (int)$p['stock']; ?></td>
                            <td style="padding:6px;vertical-align:middle;"><?php echo htmlspecialchars($p['category_name'] ?? ''); ?></td>
                            <td style="padding:6px;vertical-align:middle;"><?php echo (int)$p['approved'] ? 'Approved' : 'Pending'; ?></td>
                            <td style="padding:6px;vertical-align:middle;">
                                <a href="admin_products.php?edit=<?php echo (int)$p['id']; ?>">Edit</a>
                                <?php if (!(int)$p['approved']): ?> | <a href="admin_products.php?approve=<?php echo (int)$p['id']; ?>">Approve</a>
                                <?php else: ?> | <a href="admin_products.php?unapprove=<?php echo (int)$p['id']; ?>">Unapprove</a>
                                <?php endif; ?>
                                | <a href="admin_products.php?delete=<?php echo (int)$p['id']; ?>" onclick="return confirm('Delete product?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php else: ?>
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <?php if ($loggedIn): ?>
                <h2>Welcome back, <?php echo htmlspecialchars($username); ?></h2>
                <div><a href="logout.php" class="btn">Logout</a></div>
            <?php else: ?>
                <h2>Welcome to our store</h2>
                <div>
                    <a href="login.php" class="btn">Login</a>
                    <a href="register.php" class="btn" style="background:#28a745;margin-left:8px;">Register</a>
                </div>
            <?php endif; ?>
        </div>

        <section style="margin-top:20px;">
            <h3>Quick Actions</h3>
            <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">
                <a class="card small" href="products.php">Shop Now</a>
                <?php if ($loggedIn): ?>
                    <a class="card small" href="cart.php">My Cart</a>
                    <a class="card small" href="my_orders.php">My Orders</a>
                    <a class="card small" href="profile.php">Edit Profile</a>
                <?php else: ?>
                    <a class="card small" href="register.php">Create Account</a>
                    <a class="card small" href="login.php">Login</a>
                <?php endif; ?>
            </div>
        </section>

        <section style="margin-top:24px;">
            <h3>Featured For You</h3>
            <div class="grid" style="margin-top:12px;">
                <?php if (empty($recent)): ?>
                    <p>No products available yet.</p>
                <?php else: ?>
                    <?php foreach ($recent as $p): ?>
                        <div class="card">
                            <a href="product.php?id=<?php echo $p['id']; ?>">
                                <img src="<?php echo htmlspecialchars($p['image'] ?: 'photos/default.png'); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                            </a>
                            <h4><?php echo htmlspecialchars($p['name']); ?></h4>
                            <p>Rs.<?php echo number_format($p['price'],2); ?></p>
                            <a href="product.php?id=<?php echo $p['id']; ?>" class="btn">View</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
