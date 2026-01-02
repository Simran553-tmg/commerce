<?php
include 'db.php';
include 'header.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$orderId = isset($_GET['order']) ? (int)$_GET['order'] : 0;
if ($orderId <= 0) {
    echo '<div class="container"><p>Invalid order id.</p><p><a href="admin_orders.php">Back to orders</a></p></div>';
    include 'footer.php';
    exit();
}

$stmt = $conn->prepare('SELECT o.*, u.name, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?');
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    echo '<div class="container"><p>Order not found.</p><p><a href="admin_orders.php">Back to orders</a></p></div>';
    include 'footer.php';
    exit();
}

$itemsStmt = $conn->prepare('SELECT oi.*, p.name AS product_name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
$itemsStmt->execute([$orderId]);
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="container">
    <h2>Order #<?php echo $order['id']; ?></h2>
    <p><strong>User:</strong> <?php echo htmlspecialchars($order['name']) . ' (' . htmlspecialchars($order['email']) . ')'; ?></p>
    <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?> &nbsp; <strong>OTP Verified:</strong> <?php echo $order['otp_verified'] ? 'Yes' : 'No'; ?></p>
    <p><strong>Created:</strong> <?php echo $order['created_at']; ?></p>

    <h3>Items</h3>
    <table>
        <thead>
            <tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
        <?php foreach ($items as $it): ?>
            <tr>
                <td><?php echo htmlspecialchars($it['product_name'] ?? 'Product #' . $it['product_id']); ?></td>
                <td><?php echo (int)$it['qty']; ?></td>
                <td>Rs.<?php echo number_format($it['price'], 2); ?></td>
                <td>Rs.<?php echo number_format($it['price'] * $it['qty'], 2); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <p><strong>Total:</strong> Rs.<?php echo number_format($order['total'], 2); ?></p>

    <p><a href="admin_orders.php">Back to orders</a></p>
</div>

<?php include 'footer.php'; ?>
