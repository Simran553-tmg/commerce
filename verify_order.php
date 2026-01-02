<?php
include 'db.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$orderId = isset($_GET['order']) ? (int)$_GET['order'] : 0;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['otp'] ?? '');
    $ch = $conn->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
    $ch->execute([$orderId, (int)$_SESSION['user_id']]);
    $ord = $ch->fetch(PDO::FETCH_ASSOC);
    if (!$ord) {
        $message = 'Order not found.';
    } else {
        if ($ord['otp_verified']) {
            $message = 'Order already verified.';
        } elseif ($code === $ord['otp']) {
            $u = $conn->prepare('UPDATE orders SET otp_verified = 1, status = ? WHERE id = ?');
            $u->execute(['verified', $orderId]);
            $message = 'Order verified. Awaiting admin approval.';
        } else {
            $message = 'Incorrect code.';
        }
    }
}
?>
<div class="container">
    <h2>Verify Your Order</h2>
    <?php if ($message): ?>
        <p class="msg"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="POST">
        <label>Enter OTP<br><input type="text" name="otp" required></label><br>
        <button class="btn" type="submit">Verify</button>
    </form>
    <p>If you didn't receive the email, check your spam or contact support.</p>
</div>

<?php include 'footer.php'; ?>
