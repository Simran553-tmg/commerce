<?php
include 'db.php';

$message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $address = trim($_POST['address']);
    $contactNo = trim($_POST['contactNo']);

    if (empty($name) || empty($email) || empty($password)) {
        $message = "Name, Email and Password are required!";
    } else {

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Check if email exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            $message = "Email already registered!";
        } else {

            // Insert user
            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password, address, contactNo)
                 VALUES (?, ?, ?, ?, ?)"
            );

            if ($stmt->execute([$name, $email, $hashedPassword, $address, $contactNo])) {
                $message = "Registration successful!";
            } else {
                $message = "Registration failed!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
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
            background: #007bff;
            color: white;
            border: none;
        }
        .msg {
            text-align: center;
            color: green;
            margin-bottom: 10px;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>

<form method="POST">
    <h3>User Registration</h3>

    <?php if ($message): ?>
        <div class="msg"><?php echo $message; ?></div>
    <?php endif; ?>

    <input type="text" name="name" placeholder="Full Name" required>

    <input type="email" name="email" placeholder="Email" required>

    <input type="password" name="password" placeholder="Password" required>

    <input type="text" name="address" placeholder="Address">

    <input type="text" name="contactNo" placeholder="Contact Number">

    <button type="submit">Register</button>
</form>

</body>
</html>