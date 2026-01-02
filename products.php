<?php
include 'db.php';
include 'header.php';

$stmt = $conn->prepare("SELECT id, name, price, image, stock FROM products WHERE approved = 1 ORDER BY id DESC");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shop - Products</title>
</head>
<body>
<div class="container">
    <h2>Products</h2>
    <div class="grid">
        <?php foreach ($products as $p): ?>
            <div class="card">
                <a href="product.php?id=<?php echo $p['id']; ?>">
                    <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                </a>
                <h4><?php echo htmlspecialchars($p['name']); ?></h4>
                <p>Rs.<?php echo number_format($p['price'], 2); ?></p>
                <?php if (isset($p['stock']) && $p['stock'] <= 0): ?>
                    <p class="out-of-stock">Out of stock</p>
                    <a href="product.php?id=<?php echo $p['id']; ?>" class="btn">View</a>
                <?php else: ?>
                    <a href="product.php?id=<?php echo $p['id']; ?>" class="btn">View</a>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <p class="note">Admins cannot add to cart</p>
                    <?php else: ?>
                        <form method="POST" action="add_to_cart.php" style="display:inline-block;margin-left:8px;">
                            <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                            <input type="hidden" name="qty" value="1">
                            <button type="submit" class="btn">Add to Cart</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>