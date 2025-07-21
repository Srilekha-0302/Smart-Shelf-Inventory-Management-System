<?php
require '../includes/auth.php';
require '../includes/db.php';
require '../includes/header.php';
$products = [];
$deleted = false;
$error = null;

// Search by name
if (isset($_POST['search_name'])) {
    $name = trim($_POST['search_name']);
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ?");
    $like = "%$name%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    if (empty($products)) {
        $error = "No product found with that name.";
    }
}

// Handle delete
if (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    $conn->query("DELETE FROM products WHERE id=$id");
    $deleted = true;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Product</title>
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
        .btn.primary {
            background-color: #d09d5e;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        .btn.primary:hover {
            background-color: #b8854b;
        }
        .btn.danger {
            background-color: #ce5b5b;
            color: white;
            font-weight: bold;
        }
        .btn.danger:hover {
            background-color: #b94c4c;
        }
        a.btn.secondary {
            display: inline-block;
            margin-top: 15px;
            background-color: #a57c65;
            color: white;
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
        }
        a.btn.secondary:hover {
            background-color: #8a6552;
        }
        .message {
            margin: 10px 0;
            font-weight: bold;
        }
        .message.success {
            color: green;
        }
        .message.error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗑️ Delete Product</h1>
        <div class="form-container">
            <?php if ($deleted): ?>
                <div class="message success">✅ Product deleted successfully!</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="message error">⚠️ <?= $error ?></div>
            <?php endif; ?>

            <!-- Search Form -->
            <form method="post">
                <input type="text" name="search_name" placeholder="Search Product by Name" required>
                <button type="submit" class="btn primary">Search</button>
            </form>

            <!-- If products are found, show list -->
            <?php if (!empty($products)): ?>
                <form method="post">
                    <label for="product_id">Select Product to Delete:</label>
                    <select name="id" id="product_id" required>
                        <option value="">-- Select a product --</option>
                        <?php foreach ($products as $prod): ?>
                            <option value="<?= $prod['id'] ?>">
                                <?= $prod['name'] ?> (Qty: <?= $prod['quantity'] ?> | ₹<?= $prod['price'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="delete" class="btn danger" onclick="return confirm('Delete this product?')">Yes, Delete</button>
                </form>
            <?php endif; ?>

            <a href="dashboard.php" class="btn secondary">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
