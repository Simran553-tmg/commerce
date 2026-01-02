<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "eproject";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Ensure essential tables exist; if not, try to create them from create_tables.sql
try {
    $t = $conn->query("SHOW TABLES LIKE 'products'");
    $productsExists = ($t && $t->rowCount() > 0);

    $t2 = $conn->query("SHOW TABLES LIKE 'users'");
    $usersExists = ($t2 && $t2->rowCount() > 0);

    if (!($productsExists && $usersExists)) {
        $sqlFile = __DIR__ . '/create_tables.sql';
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            // Split statements by semicolon and execute each
            $stmts = preg_split('/;\s*\n/', $sql);
            foreach ($stmts as $stmt) {
                $s = trim($stmt);
                if ($s === '' || strpos($s, '--') === 0) continue;
                try {
                    $conn->exec($s);
                } catch (PDOException $e) {
                    // ignore individual statement errors to allow partial installs
                }
            }
        }
    }
} catch (PDOException $e) {
    // If SHOW TABLES fails, silently continue — pages will handle missing tables gracefully
}

// Ensure products has new columns (category_id, approved) if table exists but missing columns
try {
    $hasProducts = $conn->query("SHOW TABLES LIKE 'products'")->rowCount() > 0;
    if ($hasProducts) {
        $cols = [];
        $r = $conn->query("SHOW COLUMNS FROM products");
        foreach ($r->fetchAll(PDO::FETCH_ASSOC) as $c) $cols[] = $c['Field'];
        if (!in_array('category_id', $cols)) {
            $conn->exec("ALTER TABLE products ADD COLUMN category_id INT DEFAULT NULL");
        }
        if (!in_array('approved', $cols)) {
            $conn->exec("ALTER TABLE products ADD COLUMN approved TINYINT(1) NOT NULL DEFAULT 1");
        }
    }
    $hasCategories = $conn->query("SHOW TABLES LIKE 'categories'")->rowCount() > 0;
    if (!$hasCategories) {
        $conn->exec("CREATE TABLE IF NOT EXISTS categories (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL UNIQUE, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $conn->exec("INSERT IGNORE INTO categories (name) VALUES ('Electronics'), ('Fashion'), ('Home'), ('Toys')");
    }
    // Ensure orders and order_items tables exist
    $hasOrders = $conn->query("SHOW TABLES LIKE 'orders'")->rowCount() > 0;
    if (!$hasOrders) {
        $conn->exec("CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            status VARCHAR(50) NOT NULL DEFAULT 'pending',
            otp VARCHAR(10) DEFAULT NULL,
            otp_verified TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    $hasOrderItems = $conn->query("SHOW TABLES LIKE 'order_items'")->rowCount() > 0;
    if (!$hasOrderItems) {
        $conn->exec("CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            qty INT NOT NULL DEFAULT 1,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
} catch (PDOException $e) {
    // ignore
}
?>