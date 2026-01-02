<?php
include 'db.php';
include 'header.php';

// Admin only
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit();
}

$message = '';

$editCategory = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $s = $conn->prepare('SELECT * FROM categories WHERE id = ?');
    $s->execute([$eid]);
    $editCategory = $s->fetch(PDO::FETCH_ASSOC);
}

// Create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    if ($name !== '') {
        $ins = $conn->prepare('INSERT IGNORE INTO categories (name) VALUES (?)');
        $ins->execute([$name]);
        $message = 'Category added.';
    }
}

// Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $id = (int)$_POST['edit_id'];
    $name = trim($_POST['name']);
    if ($name !== '') {
        $conn->prepare('UPDATE categories SET name = ? WHERE id = ?')->execute([$name, $id]);
        header('Location: admin_categories.php');
        exit();
    }
}

// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
    header('Location: admin_categories.php');
    exit();
}

$categories = $conn->query('SELECT * FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Manage Categories</h2>
    <?php if ($message): ?>
        <p class="msg"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST">
        <label><?php echo $editCategory ? 'Edit Category' : 'New Category'; ?><br>
            <input type="text" name="name" required value="<?php echo $editCategory ? htmlspecialchars($editCategory['name']) : ''; ?>">
        </label>
        <?php if ($editCategory): ?>
            <input type="hidden" name="edit_id" value="<?php echo (int)$editCategory['id']; ?>">
            <button type="submit" name="update_category" class="btn">Update</button>
            <a href="admin_categories.php" class="btn" style="background:#6c757d;margin-left:6px;">Cancel</a>
        <?php else: ?>
            <button type="submit" name="add_category" class="btn">Add</button>
        <?php endif; ?>
    </form>

    <h3 style="margin-top:20px;">Existing Categories</h3>
    <table>
        <thead><tr><th>ID</th><th>Name</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($categories as $c): ?>
            <tr>
                <td><?php echo (int)$c['id']; ?></td>
                <td><?php echo htmlspecialchars($c['name']); ?></td>
                <td>
                    <a href="admin_categories.php?edit=<?php echo (int)$c['id']; ?>">Edit</a> |
                    <a href="admin_categories.php?delete=<?php echo (int)$c['id']; ?>" onclick="return confirm('Delete category?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
