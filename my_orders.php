<?php
include 'db.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$uid = (int)$_SESSION['user_id'];
$orders = $conn->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$orders->execute([$uid]);
$orders = $orders->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="container">
    <h2>My Orders</h2>
    <?php if (empty($orders)): ?>
        <p>You have not placed any orders yet.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>Order #</th><th>Total</th><th>Status</th><th>OTP Verified</th><th>Placed</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td><?php echo $o['id']; ?></td>
                    <td>Rs.<?php echo number_format($o['total'], 2); ?></td>
                    <td><?php echo htmlspecialchars($o['status']); ?></td>
                    <td><?php echo $o['otp_verified'] ? 'Yes' : 'No'; ?></td>
                    <td><?php echo $o['created_at']; ?></td>
                    <td><a href="order_view.php?order=<?php echo $o['id']; ?>">View</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
