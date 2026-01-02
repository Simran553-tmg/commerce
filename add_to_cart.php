<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit();
}

$product_id = (int)($_POST['product_id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));

// Prevent admin users from adding items to the cart
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    // redirect back to product page if possible
    if ($product_id > 0) {
        header('Location: product.php?id=' . $product_id . '&error=admin_no_cart');
    } else {
        header('Location: products.php');
    }
    exit();
}

// fetch product to validate (including stock)
$stmt = $conn->prepare('SELECT id, name, price, stock FROM products WHERE id = ?');
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    header('Location: products.php');
    exit();
}

// Determine how much to reserve based on existing cart qty
$existingQty = isset($_SESSION['cart'][$product_id]) ? (int)$_SESSION['cart'][$product_id]['qty'] : 0;
$reserve = $qty; // amount to decrement from DB now

// Attempt to decrement stock in DB for the reserved amount atomically
$dec = $conn->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
$dec->execute([$reserve, $product_id, $reserve]);
if ($dec->rowCount() === 0) {
    // insufficient stock
    header('Location: product.php?id=' . $product_id . '&error=outofstock');
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$product_id])) {
    // increase qty in session (we already reserved $reserve in DB)
    $_SESSION['cart'][$product_id]['qty'] += $qty;
} else {
    $_SESSION['cart'][$product_id] = [
        'id' => $product['id'],
        'name' => $product['name'],
        'price' => $product['price'],
        'qty' => $qty
    ];
}

header('Location: cart.php');
exit();
?>