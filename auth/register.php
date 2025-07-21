<?php
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role']; // 'manager' or 'cashier'

    // Check if username already exists
    $check = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $checkResult = $check->get_result();

    if ($checkResult->num_rows > 0) {
        $error = "Username already exists.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $role);

        if ($stmt->execute()) {
            $success = "✅ Registered successfully. <a href='login.php'>Login</a>";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h1>Register New User</h1>

    <?php
    if (isset($success)) echo "<div class='success'>$success</div>";
    if (isset($error)) echo "<div class='error'>$error</div>";
    ?>

    <form method="POST">
        <input type="text" name="username" required placeholder="Username"><br>
        <input type="password" name="password" required placeholder="Password"><br>
        <select name="role" required>
            <option value="manager">Manager</option>
            <option value="cashier">Cashier</option>
        </select><br>
        <button type="submit" class="btn primary">Register</button>
    </form>
</div>
</body>
</html>
 