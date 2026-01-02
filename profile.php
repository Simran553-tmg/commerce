<?php
include 'db.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$uid = (int)$_SESSION['user_id'];

// Fetch current user
$stmt = $conn->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    echo "User not found.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $contactNo = trim($_POST['contactNo']);

    if ($email !== $user['email']) {
        // check duplicate email
        $ch = $conn->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $ch->execute([$email, $uid]);
        if ($ch->rowCount() > 0) {
            $message = 'Email already in use by another account.';
        }
    }

    if ($message === '') {
        try {
            $up = $conn->prepare('UPDATE users SET name = ?, email = ?, address = ?, contactNo = ? WHERE id = ?');
            $ok = $up->execute([$name, $email, $address, $contactNo, $uid]);

            if ($ok) {
                $message = 'Profile updated.';
                // refresh session name
                $_SESSION['user_name'] = $name;
                $stmt = $conn->prepare('SELECT * FROM users WHERE id = ?');
                $stmt->execute([$uid]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $message = 'Update failed.';
            }
        } catch (PDOException $e) {
            $message = 'Update error: ' . $e->getMessage();
        }
    }
}
?>

<div class="container">
    <h2>Edit Profile</h2>
    <?php if ($message): ?>
        <p class="msg"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Full name<br><input type="text" name="name" required value="<?php echo htmlspecialchars($user['name']); ?>"></label><br>
        <label>Email<br><input type="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>"></label><br>
        <label>Address<br><input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>"></label><br>
        <label>Contact No<br><input type="number" name="contactNo" value="<?php echo htmlspecialchars($user['contactno']); ?>"></label><br>
        <button type="submit" class="btn">Save Changes</button>
    </form>
</div>

<?php include 'footer.php'; ?>
