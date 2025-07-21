<?php
require '../includes/auth.php';
require '../includes/db.php';
require '../includes/header.php';
$products = [];
$selectedProduct = null;
$updated = false;
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

// Load selected product to edit
if (isset($_POST['select_product'])) {
    $id = $_POST['product_id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $selectedProduct = $stmt->get_result()->fetch_assoc();
}

// Handle update
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $expiry = $_POST['expiry_date'];

    $stmt = $conn->prepare("UPDATE products SET quantity=?, price=?, expiry_date=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("idsi", $quantity, $price, $expiry, $id);
    $stmt->execute();
    $updated = true;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Product</title>
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
            max-width: 800px;
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
            background: #fffdf7;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        form input, form select, form button {
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
        }
        .btn.primary:hover {
            background-color: #b8854b;
        }
        .btn.secondary {
            background-color: #a57c65;
            color: white;
            font-weight: bold;
        }
        .btn.secondary:hover {
            background-color: #8a6552;
        }
        a.btn.secondary {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
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
        <h1>✏️ Update Product</h1>
        <div class="form-container">

            <?php if ($updated): ?>
                <div class="message success">✅ Product updated successfully!</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="message error">⚠️ <?= $error ?></div>
            <?php endif; ?>

            <!-- Search Form -->
            <form method="post">
                <input type="text" name="search_name" placeholder="Search Product by Name" required>
                <button type="submit" class="btn primary">Search</button>
            </form>

            <!-- If results exist, let user choose -->
            <?php if (!empty($products)): ?>
                <form method="post">
                    <select name="product_id" required>
                        <option value="">-- Select a product to update --</option>
                        <?php foreach ($products as $prod): ?>
                            <option value="<?= $prod['id'] ?>">
                                <?= $prod['name'] ?> (Qty: <?= $prod['quantity'] ?> | ₹<?= $prod['price'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="select_product" class="btn secondary">Edit Selected</button>
                </form>
            <?php endif; ?>

            <!-- Update Form -->
            <?php if ($selectedProduct): ?>
                <form method="post">
                    <input type="hidden" name="id" value="<?= $selectedProduct['id'] ?>">
                    <label>Quantity</label>
                    <input type="number" name="quantity" value="<?= $selectedProduct['quantity'] ?>" required>
                    <label>Price (₹)</label>
                    <input type="number" step="0.01" name="price" value="<?= $selectedProduct['price'] ?>" required>
                    <label>Expiry Date</label>
                    <input type="date" name="expiry_date" value="<?= $selectedProduct['expiry_date'] ?>" required>
                    <button type="submit" name="update" class="btn secondary">✅ Update Product</button>
                </form>
            <?php endif; ?>

            <a href="dashboard.php" class="btn secondary">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
