<?php
include 'db.php';
include 'header.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// handle actions
if (isset($_GET['action'], $_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    if ($action === 'approve') {
        $u = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $u->execute(['approved', $id]);
    } elseif ($action === 'cancel') {
        // Only restore stock if order wasn't already cancelled
        $check = $conn->prepare('SELECT status FROM orders WHERE id = ?');
        $check->execute([$id]);
        $ord = $check->fetch(PDO::FETCH_ASSOC);
        if ($ord && $ord['status'] !== 'cancelled') {
            try {
                $conn->beginTransaction();

                // fetch order items
                $items = $conn->prepare('SELECT product_id, qty FROM order_items WHERE order_id = ?');
                $items->execute([$id]);
                $rows = $items->fetchAll(PDO::FETCH_ASSOC);
                $inc = $conn->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
                foreach ($rows as $r) {
                    $inc->execute([(int)$r['qty'], (int)$r['product_id']]);
                }

                $u = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
                $u->execute(['cancelled', $id]);

                $conn->commit();
            } catch (PDOException $e) {
                if ($conn->inTransaction()) $conn->rollBack();
                // log or show error
            }
        }
    }
    header('Location: admin_orders.php');
    exit();
}

$orders = $conn->query('SELECT o.*, u.name, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container">
    <h2>Orders</h2>
    <table>
        <thead>
            <tr><th>ID</th><th>User</th><th>Total</th><th>Status</th><th>OTP Verified</th><th>Created</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><?php echo $o['id']; ?></td>
                <td><?php echo htmlspecialchars($o['name']) . ' (' . htmlspecialchars($o['email']) . ')'; ?></td>
                <td>Rs.<?php echo number_format($o['total'], 2); ?></td>
                <td><?php echo htmlspecialchars($o['status']); ?></td>
                <td><?php echo $o['otp_verified'] ? 'Yes' : 'No'; ?></td>
                <td><?php echo $o['created_at']; ?></td>
                <td>
                    <a href="admin_order_view.php?order=<?php echo $o['id']; ?>">View</a>
                    <?php if ($o['status'] !== 'approved'): ?>
                        &nbsp;|&nbsp;<a href="admin_orders.php?action=approve&id=<?php echo $o['id']; ?>">Approve</a>
                    <?php endif; ?>
                    <?php if ($o['status'] !== 'cancelled'): ?>
                        &nbsp;|&nbsp;<a href="admin_orders.php?action=cancel&id=<?php echo $o['id']; ?>">Cancel</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
