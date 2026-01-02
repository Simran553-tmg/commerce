<?php
include 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message = "Email and Password are required!";
    } else {

        // Fetch user by email (select all columns so missing `role` won't cause SQL errors)
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];

                // Determine role if present in the users table; default to 'user'
                $role = isset($user['role']) ? $user['role'] : 'user';
                $_SESSION['role'] = $role;

                // Redirect based on role
                if ($role === 'admin') {
                    header("Location: dashboard.php");
                    exit;
                } else {
                    header("Location: dashboard.php");
                    exit;
                }
            } else {
                $message = "Invalid password!";
            }
        } else {
            $message = "Email not registered!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Login</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; }
        form {
            background: white;
            padding: 20px;
            width: 300px;
            margin: 50px auto;
            border-radius: 5px;
        }
        input, button {
            width: 100%;
            padding: 8px;
            margin-top: 10px;
        }
        button {
            background: #28a745;
            color: white;
            border: none;
        }
        .msg {
            text-align: center;
            margin-bottom: 10px;
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>

<form method="POST">
    <h3>User Login</h3>

    <?php if ($message): ?>
        <div class="msg"><?php echo $message; ?></div>
    <?php endif; ?>

    <input type="email" name="email" placeholder="Email" required>

    <input type="password" name="password" placeholder="Password" required>

    <button type="submit">Login</button>
</form>

</body>
</html>
