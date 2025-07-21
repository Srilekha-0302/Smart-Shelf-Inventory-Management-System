<?php
require '../includes/auth.php';
require '../includes/db.php';
require '../includes/header.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];
    $expiry = $_POST['expiry_date'];

    $stmt = $conn->prepare("INSERT INTO products (name, quantity, price, expiry_date, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("sids", $name, $quantity, $price, $expiry);
    $stmt->execute();

    $success = "Product added successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Product</title>
    <style>
body {
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
    padding: 0;
    background: linear-gradient(135deg, #fef6e4, #e0f7fa, #f3e5f5);
    background-size: 400% 400%;
    animation: softBG 20s ease infinite;
    color: #3e2d1a;
}
@keyframes softBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
.container {
    max-width: 1200px;
    margin: auto;
    padding: 30px;
}
h1 {
    text-align: center;
    font-size: 30px;
    color: #4c3b28;
    margin-bottom: 20px;
}
.form-container {
    max-width: 500px;
    margin: auto;
    padding: 30px;
    background: #fffdf7;
    border-radius: 16px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}
form input, form button {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 15px;
}
form button {
    background-color: #d09d5e;
    color: white;
    font-weight: bold;
    cursor: pointer;
}
form button:hover {
    background-color: #b8854b;
}
a.btn.secondary {
    display: inline-block;
    margin-top: 15px;
    text-align: center;
    background-color: #a57c65;
    color: white;
    padding: 10px 16px;
    border-radius: 8px;
    text-decoration: none;
}
a.btn.secondary:hover {
    background-color: #8a6552;
}
</style>
</head>
<body>
    <div class="container">
        <h1>➕ Add New Product</h1>
        <div class="form-container">
            <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
            <form method="post">
                <input type="text" name="name" placeholder="Product Name" required>
                <input type="number" name="quantity" placeholder="Quantity" required>
                <input type="number" step="0.01" name="price" placeholder="Price (₹)" required>
                <input type="date" name="expiry_date" required>
                <button type="submit" class="btn primary">Add Product</button>
            </form>
            <a href="dashboard.php" class="btn secondary">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
