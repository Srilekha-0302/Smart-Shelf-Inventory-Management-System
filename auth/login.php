<?php
session_start();
require '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = md5($_POST['password']); // use MD5 for now

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user'] = $user;

        if ($user['role'] == 'manager') {
            header("Location: ../manager/dashboard.php");
        } else {
            header("Location: ../cashier/checkout.php");
        }
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: url('images/Gemini_Generated_Image_jcubtajcubtajcub.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: #3f3f3f;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.5); /* 50% opacity, semi-transparent background */
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
            width: 360px;
            animation: fadeIn 0.6s ease-in-out;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
            color: #4e4e4e;
            font-size: 24px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input[type="text"],
        input[type="password"] {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            background-color: rgba(230, 215, 185, 0.8); /* Light wheatish with opacity */
            color: #3f3f3f;
            font-size: 15px;
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: #b5a37e;
            outline: none;
        }

        input::placeholder {
            color: #a3a3a3;
        }

        button {
            padding: 12px;
            border: none;
            border-radius: 6px;
            background: linear-gradient(to right, #9c7f4f, #d1ad75);
            color: white;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: linear-gradient(to right, #d1ad75, #9c7f4f);
        }

        .error {
            background-color: #dc2626;
            color: #f8fafc;
            padding: 10px;
            margin-bottom: 15px;
            text-align: center;
            border-radius: 6px;
            font-size: 14px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>

        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>

        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
