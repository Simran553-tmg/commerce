<?php
include 'db.php';
include 'header.php';

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$id = (int)$_GET['id'];
$stmt = $conn->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    echo "Product not found";
    exit();
}
// If product exists but not approved, hide from non-admins
if (empty($product['approved']) && (($_SESSION['role'] ?? '') !== 'admin')) {
    echo "Product not available.";
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($product['name']); ?></title>
</head>
<body>
<div class="container">
    <h2><?php echo htmlspecialchars($product['name']); ?></h2>
    <img src="<?php echo htmlspecialchars($product['image'] ?: 'photos/default.png'); ?>" alt="">
    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
    <p>Price: Rs.<?php echo number_format($product['price'], 2); ?></p>
    <?php if (isset($product['stock']) && $product['stock'] <= 0): ?>
        <p class="out-of-stock">Out of stock</p>
    <?php else: ?>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <p class="note">Admins cannot add to cart</p>
        <?php else: ?>
            <form method="POST" action="add_to_cart.php">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <label>Quantity: <input type="number" name="qty" value="1" min="1" max="<?php echo (int)$product['stock']; ?>"></label>
                <button type="submit">Add to Cart</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'outofstock'): ?>
        <p class="error">Unable to add to cart: product out of stock.</p>
    <?php elseif (isset($_GET['error']) && $_GET['error'] === 'admin_no_cart'): ?>
        <p class="error">Admins are not allowed to add items to the cart.</p>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
</body>
</html>