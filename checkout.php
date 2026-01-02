<?php
session_start();
include 'db.php';
include 'header.php';

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header('Location: products.php');
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Create order
$uid = (int)$_SESSION['user_id'];
$total = 0.00;
foreach ($cart as $item) {
    $total += ($item['price'] * $item['qty']);
}

// generate simple numeric OTP
$otp = strval(rand(100000, 999999));

try {
    // Use transaction to ensure order and stock updates are atomic
    $conn->beginTransaction();

    $stmt = $conn->prepare('INSERT INTO orders (user_id, total, status, otp) VALUES (?, ?, ?, ?)');
    $stmt->execute([$uid, $total, 'pending', $otp]);
    $orderId = $conn->lastInsertId();

    // prepare statements
    $it = $conn->prepare('INSERT INTO order_items (order_id, product_id, qty, price) VALUES (?, ?, ?, ?)');
    $dec = $conn->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');

    foreach ($cart as $item) {
        $pid = isset($item['id']) ? (int)$item['id'] : (int)($item['product_id'] ?? 0);
        $qtyItem = (int)$item['qty'];

        // attempt to decrement stock; ensure enough stock exists
        $dec->execute([$qtyItem, $pid, $qtyItem]);
        if ($dec->rowCount() === 0) {
            // insufficient stock — rollback and inform user
            $conn->rollBack();
            die('Order failed: Insufficient stock for product ID ' . $pid);
        }

        // insert order item
        $it->execute([$orderId, $pid, $qtyItem, $item['price']]);
    }

    $conn->commit();

} catch (PDOException $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    die('Order creation failed: ' . $e->getMessage());
}

// send OTP email (simple mail) and log result for debugging
$userStmt = $conn->prepare('SELECT email FROM users WHERE id = ?');
$userStmt->execute([$uid]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);
if ($user && filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
    $to = $user['email'];
    $subject = 'Your order verification code';
    $message = "Your OTP to verify order #$orderId is: $otp";
    $headers = 'From: no-reply@localhost' . "\r\n";
    $sent = mail($to, $subject, $message, $headers);
    if (!$sent) {
        $log = date('Y-m-d H:i:s') . " - mail() returned false for order $orderId to $to\n";
        $log .= "Subject: $subject\nMessage: $message\nHeaders: $headers\n\n";
        file_put_contents(__DIR__ . '/mail_log.txt', $log, FILE_APPEND);
    } else {
        $log = date('Y-m-d H:i:s') . " - mail sent for order $orderId to $to\n";
        file_put_contents(__DIR__ . '/mail_log.txt', $log, FILE_APPEND);
    }
}

// clear cart and redirect to verify page
$_SESSION['cart'] = [];
header('Location: verify_order.php?order=' . urlencode($orderId));
exit();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Checkout</title>
</head>
<body>
<div class="container">
    <h2>Thank you for your purchase!</h2>
    <p>Your order has been placed (demo).</p>
    <a href="products.php">Continue shopping</a>
</div>
<?php include 'footer.php'; ?>
</body>
</html>