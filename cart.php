<?php
session_start();
include 'db.php';

// handle cart actions: remove single item or clear cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'remove' && isset($_POST['product_id'])) {
        $pid = (int)$_POST['product_id'];
        if (isset($_SESSION['cart'][$pid])) {
            $qty = (int)$_SESSION['cart'][$pid]['qty'];
            // return reserved stock
            $inc = $conn->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
            $inc->execute([$qty, $pid]);
            unset($_SESSION['cart'][$pid]);
        }
        header('Location: cart.php');
        exit();
    }
    if (isset($_POST['action']) && $_POST['action'] === 'clear') {
        // return stock for all items in cart
        if (!empty($_SESSION['cart'])) {
            $inc = $conn->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
            foreach ($_SESSION['cart'] as $pid => $item) {
                $inc->execute([(int)$item['qty'], (int)$pid]);
            }
        }
        unset($_SESSION['cart']);
        header('Location: cart.php');
        exit();
    }
}

include 'header.php';

$cart = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['qty'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Shopping Cart</title>
</head>
<body>
<div class="container">
    <h2>Your Cart</h2>
    <?php if (empty($cart)): ?>
        <p>Your cart is empty. <a href="products.php">Continue shopping</a></p>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
                <?php foreach ($cart as $pid => $c): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($c['name']); ?></td>
                        <td>Rs.<?php echo number_format($c['price'],2); ?></td>
                        <td><?php echo (int)$c['qty']; ?></td>
                        <td>Rs.<?php echo number_format($c['price'] * $c['qty'],2); ?></td>
                        <td>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?php echo (int)$pid; ?>">
                                <button type="submit" class="btn">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><strong>Total: Rs.<?php echo number_format($total,2); ?></strong></p>
        <div style="display:flex;gap:8px;align-items:center;">
            <a href="checkout.php" class="btn">Proceed to Checkout</a>
            <form method="POST" style="display:inline;margin:0;">
                <input type="hidden" name="action" value="clear">
                <button type="submit" class="btn">Clear Cart</button>
            </form>
        </div>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
</body>
</html>