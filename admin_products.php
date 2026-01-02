<?php
include 'db.php';
include 'header.php';

// Only admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit();
}

$message = '';

$editProduct = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $s = $conn->prepare('SELECT * FROM products WHERE id = ?');
    $s->execute([$eid]);
    $editProduct = $s->fetch(PDO::FETCH_ASSOC);
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // remove image file if exists
    $s = $conn->prepare('SELECT image FROM products WHERE id = ?');
    $s->execute([$id]);
    $prod = $s->fetch(PDO::FETCH_ASSOC);
    if ($prod && !empty($prod['image'])) {
        $imgPath = $prod['image'];
        // make absolute path if relative
        if (!file_exists($imgPath)) {
            $alt = __DIR__ . DIRECTORY_SEPARATOR . $imgPath;
            if (file_exists($alt)) $imgPath = $alt;
        }
        if (file_exists($imgPath)) {
            @unlink($imgPath);
        }
    }
    $d = $conn->prepare('DELETE FROM products WHERE id = ?');
    $d->execute([$id]);
    header('Location: admin_products.php');
    exit();
}

// Handle approve/unapprove
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $conn->prepare('UPDATE products SET approved = 1 WHERE id = ?')->execute([$id]);
    header('Location: admin_products.php');
    exit();
}
if (isset($_GET['unapprove'])) {
    $id = (int)$_GET['unapprove'];
    $conn->prepare('UPDATE products SET approved = 0 WHERE id = ?')->execute([$id]);
    header('Location: admin_products.php');
    exit();
}

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;

    // handle image upload
    $imagePath = 'photos/default.png';
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'photos';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'photos/' . uniqid('p_', true) . '.' . $ext;
        if (move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . DIRECTORY_SEPARATOR . $filename)) {
            $imagePath = $filename;
        }
    }

    // New products require admin approval; set approved=0 by default
    $ins = $conn->prepare('INSERT INTO products (name, description, price, image, stock, category_id, approved) VALUES (?, ?, ?, ?, ?, ?, 0)');
    $ins->execute([$name, $description, $price, $imagePath, $stock, $category_id]);
    $message = 'Product added.';
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $id = (int)$_POST['edit_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;

    $imagePath = $_POST['existing_image'] ?? 'photos/default.png';
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'photos';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'photos/' . uniqid('p_', true) . '.' . $ext;
        if (move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . DIRECTORY_SEPARATOR . $filename)) {
            // delete old image if it exists and is not the default
            if (!empty($_POST['existing_image']) && $_POST['existing_image'] !== 'photos/default.png') {
                $old = $_POST['existing_image'];
                if (!file_exists($old)) {
                    $alt = __DIR__ . DIRECTORY_SEPARATOR . $old;
                    if (file_exists($alt)) $old = $alt;
                }
                if (file_exists($old)) @unlink($old);
            }
            $imagePath = $filename;
        }
    }

    $up = $conn->prepare('UPDATE products SET name = ?, description = ?, price = ?, image = ?, stock = ?, category_id = ? WHERE id = ?');
    $up->execute([$name, $description, $price, $imagePath, $stock, $category_id, $id]);
    header('Location: admin_products.php');
    exit();
}

// Fetch all products
$products = $conn->query('SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC')->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Manage Products</h2>
    <?php if ($message): ?>
        <p class="msg"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <h3><?php echo $editProduct ? 'Edit Product' : 'Add Product'; ?></h3>
    <form method="POST" enctype="multipart/form-data">
        <label>Name<br><input type="text" name="name" required value="<?php echo $editProduct ? htmlspecialchars($editProduct['name']) : ''; ?>"></label><br>
        <label>Price<br><input type="number" step="0.01" name="price" required value="<?php echo $editProduct ? htmlspecialchars($editProduct['price']) : ''; ?>"></label><br>
        <label>Stock<br><input type="number" name="stock" value="<?php echo $editProduct ? (int)$editProduct['stock'] : 0; ?>"></label><br>
        <label>Category<br>
            <select name="category_id">
                <option value="">-- None --</option>
                <?php foreach ($conn->query('SELECT * FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC) as $cat): ?>
                    <option value="<?php echo (int)$cat['id']; ?>" <?php echo ($editProduct && $editProduct['category_id']==$cat['id'])? 'selected':''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </label><br>
        <label>Description<br><textarea name="description"><?php echo $editProduct ? htmlspecialchars($editProduct['description']) : ''; ?></textarea></label><br>
        <label>Image<br><input type="file" name="image" accept="image/*"></label><br>
        <?php if ($editProduct): ?>
            <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($editProduct['image']); ?>">
            <input type="hidden" name="edit_id" value="<?php echo (int)$editProduct['id']; ?>">
            <button type="submit" name="update_product" class="btn">Update Product</button>
            <a href="admin_products.php" class="btn" style="background:#6c757d;margin-left:6px;">Cancel</a>
        <?php else: ?>
            <button type="submit" name="add_product" class="btn">Add Product</button>
        <?php endif; ?>
    </form>

    <h3 style="margin-top:20px;">Existing Products</h3>
    <table>
        <thead>
            <tr><th>ID</th><th>Image</th><th>Name</th><th>Price</th><th>Stock</th><th>Action</th></tr>
        </thead>
        <tbody>
        <?php foreach ($products as $p): ?>
            <tr>
                <td><?php echo (int)$p['id']; ?></td>
                <td style="width:100px;"><img src="<?php echo htmlspecialchars($p['image'] ?: 'photos/default.png'); ?>" style="width:80px;height:50px;object-fit:cover;border-radius:4px;"></td>
                <td><?php echo htmlspecialchars($p['name']); ?>
                    <?php if (!(int)$p['approved']): ?>
                        <br><small style="color:orange;">(Pending approval)</small>
                    <?php endif; ?>
                </td>
                <td>Rs.<?php echo number_format($p['price'],2); ?></td>
                <td><?php echo (int)$p['stock']; ?></td>
                <td><?php echo htmlspecialchars($p['category_name'] ?? ''); ?></td>
                <td>
                        <a href="admin_products.php?edit=<?php echo (int)$p['id']; ?>">Edit</a> |
                        <?php if (!(int)$p['approved']): ?>
                            <a href="admin_products.php?approve=<?php echo (int)$p['id']; ?>">Approve</a> |
                        <?php else: ?>
                            <a href="admin_products.php?unapprove=<?php echo (int)$p['id']; ?>">Unapprove</a> |
                        <?php endif; ?>
                        <a href="admin_products.php?delete=<?php echo (int)$p['id']; ?>" onclick="return confirm('Delete product?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>