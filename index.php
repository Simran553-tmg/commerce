<?php
include 'header.php';

// Fetch featured products
$stmt = $conn->prepare("SELECT id, name, price, image FROM products ORDER BY created_at DESC LIMIT 8");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="hero">
    <div class="wrap hero-inner">
        <div class="hero-text">
            <h1>Discover great products</h1>
            <p>Quality items at competitive prices. Fast delivery and easy returns.</p>
            <a href="products.php" class="btn">Shop Now</a>
        </div>
        <div class="hero-image">
            <img src="photos/default.png" alt="Shop">
        </div>
    </div>
</section>

<section class="join" style="padding:28px 0;background:#fff;margin-top:20px;">
    <div class="wrap" style="display:flex;align-items:center;justify-content:space-between;gap:20px;">
        <div>
            <h2>Join our community</h2>
            <p>Create an account to save orders, track shipments, and get exclusive offers.</p>
        </div>
        <div>
            <a href="register.php" class="btn" style="margin-right:8px;">Register Now</a>
            <a href="login.php" class="btn" style="background:#6c757d;">Login</a>
        </div>
    </div>
</section>

<div class="wrap">
    <section class="categories" aria-label="Categories">
        <h2>Top Categories</h2>
        <div class="cat-grid">
            <a class="cat" href="#">Electronics</a>
            <a class="cat" href="#">Fashion</a>
            <a class="cat" href="#">Home</a>
            <a class="cat" href="#">Toys</a>
        </div>
    </section>

    <section id="featured">
        <h2>Featured Products</h2>
        <div class="grid">
            <?php if (empty($products)): ?>
                <p>No products available yet. Visit <a href="admin_products.php">admin</a> to add items.</p>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                    <div class="card">
                        <a href="product.php?id=<?php echo $p['id']; ?>">
                            <img src="<?php echo htmlspecialchars($p['image'] ?: 'photos/default.png'); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                        </a>
                        <h4><?php echo htmlspecialchars($p['name']); ?></h4>
                        <p class="price">$<?php echo number_format($p['price'],2); ?></p>
                        <a href="product.php?id=<?php echo $p['id']; ?>" class="btn">View</a>
                        <form method="POST" action="add_to_cart.php" style="display:inline-block;margin-left:8px;">
                            <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                            <input type="hidden" name="qty" value="1">
                            <button type="submit" class="btn">Add to Cart</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>
