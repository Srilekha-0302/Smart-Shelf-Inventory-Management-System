<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'cashier') {
    header("Location: ../auth/login.php");
    exit();
}
require '../includes/db.php';

$success = "";
$error = "";

// Get categories for dropdown
$category_result = $conn->query("SELECT DISTINCT category FROM products ORDER BY category ASC");
$categories = [];
while ($row = $category_result->fetch_assoc()) {
    $categories[] = $row['category'];
}

// Get products
$products = [];
$product_result = $conn->query("SELECT * FROM products ORDER BY name ASC");
while ($row = $product_result->fetch_assoc()) {
    $products[] = $row;
}

// Handle checkout form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $cart = $_POST['cart'] ?? [];

    if (!empty($cart)) {
        foreach ($cart as $item) {
            if (!isset($item['name']) || !isset($item['qty'])) continue;

            $name = $conn->real_escape_string($item['name']);
            $qty = (int)$item['qty'];

            $product = $conn->query("SELECT * FROM products WHERE name = '$name'")->fetch_assoc();
            if ($product && $product['quantity'] >= $qty) {
                $price = $product['price'];
                $total = $qty * $price;

                // Log the sale
                $stmt = $conn->prepare("INSERT INTO sales_log (product_name, quantity, price, total) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sidd", $name, $qty, $price, $total);
                $stmt->execute();

                // Update stock
                $new_qty = $product['quantity'] - $qty;
                $conn->query("UPDATE products SET quantity = $new_qty, times_purchased = times_purchased + $qty WHERE name = '$name'");
            } else {
                $error .= "$name has insufficient stock.<br>";
            }
        }

        if ($error === "") {
            $success = "✅ Checkout completed successfully!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cashier Checkout</title>
    
    <style>
    /* Animated soft background */
    body {
        font-family: 'Segoe UI', sans-serif;
        margin: 0;
        padding: 0;
        min-height: 100vh;
        background: linear-gradient(-45deg, #ffe8d6, #d0f4de, #fcd5ce, #cddafd);
        background-size: 400% 400%;
        animation: gradientBG 18s ease infinite;
        color: #3d2b1f;
    }

    @keyframes gradientBG {
        0% {background-position: 0% 50%;}
        50% {background-position: 100% 50%;}
        100% {background-position: 0% 50%;}
    }

    .container {
        max-width: 1000px;
        margin: 50px auto;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(8px);
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 12px 30px rgba(0,0,0,0.1);
        transition: all 0.3s ease-in-out;
    }

    h1 {
        text-align: center;
        font-size: 36px;
        color: #4a2c2a;
        margin-bottom: 30px;
    }

    .form-group {
        margin-bottom: 25px;
    }

    label {
        font-weight: 600;
        display: block;
        margin-bottom: 8px;
        color: #5e3d2b;
    }

    input[type="text"], input[type="number"], select {
        width: 100%;
        padding: 12px 14px;
        font-size: 15px;
        border: 1px solid #ccc;
        border-radius: 12px;
        background-color: #fffefc;
        transition: 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    input:focus, select:focus {
        outline: none;
        border-color: #d09d5e;
        box-shadow: 0 0 0 3px rgba(208, 157, 94, 0.3);
    }

    .btn {
        background: linear-gradient(to right, #f6ae2d, #d09d5e);
        color: white;
        font-weight: 600;
        padding: 12px 20px;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        box-shadow: 0 5px 15px rgba(240, 165, 0, 0.3);
        transition: transform 0.2s ease, box-shadow 0.3s ease;
    }

    .btn:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 20px rgba(240, 165, 0, 0.4);
    }

    table {
        width: 100%;
        margin-top: 30px;
        border-collapse: collapse;
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
    }

    th {
        background-color: #fbe1c5;
        color: #3e2d1a;
        font-weight: 600;
    }

    th, td {
        padding: 14px;
        border-bottom: 1px solid #eee;
        text-align: center;
        font-size: 14px;
    }

    tr:hover {
        background-color: #fff7ed;
    }

    #totalAmount {
        font-size: 20px;
        font-weight: bold;
        color: #2f1f10;
        margin-top: 20px;
        text-align: right;
    }

    .msg {
        padding: 12px 20px;
        border-radius: 10px;
        font-weight: 500;
        margin-bottom: 20px;
    }

    .success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #34d399;
    }

    .error {
        background: #ffe4e6;
        color: #9f1239;
        border: 1px solid #f87171;
    }

    .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.logout-link a {
    background: linear-gradient(to right, #ff4e50, #f9d423);
    color: white;
    text-decoration: none;
    padding: 12px 24px;
    border-radius: 14px;
    font-size: 16px;
    font-weight: bold;
    box-shadow: 0 5px 15px rgba(255, 100, 0, 0.3);
    transition: all 0.3s ease-in-out;
}

.logout-link a:hover {
    background: linear-gradient(to right, #ff6a6a, #fceabb);
    transform: scale(1.05);
    box-shadow: 0 8px 22px rgba(255, 80, 80, 0.4);
}


    .logout-link {
        text-align: right;
        margin-bottom: 10px;
    }

    .logout-link a {
        background-color: #ffe5e5;
        color: #b30000;
        text-decoration: none;
        padding: 8px 16px;
        border-radius: 10px;
        font-weight: bold;
        transition: 0.3s ease;
    }

    .logout-link a:hover {
        background-color: #ffcfcf;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
</style>

</head>
<body>
<div class="container">
    <!-- <div class="header">
        <h1>🧾 Cashier Checkout</h1>
        <a href="../auth/logout.php"><button class="logout-btn">🚪 Logout</button></a>
    </div> -->

    <div class="header">
    <h1>🧾 Cashier Checkout</h1>
        <div class="logout-link">
            <a href="../auth/logout.php">🚪 Logout</a>
        </div>
    </div>

    <?php if ($success): ?><div class="msg success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="msg error"><?= $error ?></div><?php endif; ?>

    <div class="form-group">
        <label>Filter by Category:</label>
        <select id="categoryFilter">
            <option value="">-- All Categories --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Search Product:</label>
        <input type="text" id="productSearch" placeholder="Type to search...">
    </div>

    <div class="form-group">
        <label>Select Product:</label>
        <select id="productDropdown">
            <option value="">-- Choose Product --</option>
            <?php foreach ($products as $prod): ?>
                <option value="<?= htmlspecialchars($prod['name']) ?>"
                        data-category="<?= htmlspecialchars($prod['category']) ?>"
                        data-price="<?= $prod['price'] ?>"
                        data-stock="<?= $prod['quantity'] ?>">
                    <?= htmlspecialchars($prod['name']) ?> (₹<?= $prod['price'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Quantity:</label>
        <input type="number" id="qtyInput" value="1" min="1">
    </div>

    <button class="btn" onclick="addToCart()">➕ Add to Cart</button>

    <form method="POST">
        <input type="hidden" name="checkout" value="1">
        <table id="cartTable">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>🗑</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <h3>Total: ₹<span id="totalAmount">0.00</span></h3>
        <br>
        <button class="btn" type="submit">🛒 Final Checkout</button>
    </form>
</div>

<script>
const productDropdown = document.getElementById("productDropdown");
const qtyInput = document.getElementById("qtyInput");
const cartTable = document.getElementById("cartTable").querySelector("tbody");
const totalAmount = document.getElementById("totalAmount");
const categoryFilter = document.getElementById("categoryFilter");
const productSearch = document.getElementById("productSearch");

categoryFilter.addEventListener("change", filterProducts);
productSearch.addEventListener("input", filterProducts);

function filterProducts() {
    const category = categoryFilter.value.toLowerCase();
    const search = productSearch.value.toLowerCase();

    let firstVisible = null;

    Array.from(productDropdown.options).forEach(opt => {
        if (opt.value === "") return;
        const cat = opt.dataset.category.toLowerCase();
        const name = opt.textContent.toLowerCase();

        const matches = (!category || cat.includes(category)) && (!search || name.includes(search));
        opt.style.display = matches ? "block" : "none";

        if (matches && !firstVisible) firstVisible = opt;
    });

    if (firstVisible) {
        productDropdown.value = firstVisible.value;
    } else {
        productDropdown.value = "";
    }
}

function addToCart() {
    const selected = productDropdown.selectedOptions[0];
    const productName = productDropdown.value;
    const qty = parseInt(qtyInput.value);

    if (!productName || qty < 1) return alert("Select a product and enter quantity.");

    const price = parseFloat(selected.dataset.price);
    const stock = parseInt(selected.dataset.stock);
    if (qty > stock) return alert("Quantity exceeds available stock.");

    const total = price * qty;

    const row = document.createElement("tr");
    row.innerHTML = `
        <td><input type="hidden" name="cart[${productName}][name]" value="${productName}">${productName}</td>
        <td><input type="hidden" name="cart[${productName}][qty]" value="${qty}">${qty}</td>
        <td>₹${price.toFixed(2)}</td>
        <td>₹${total.toFixed(2)}</td>
        <td><button onclick="removeRow(this)" type="button">❌</button></td>
    `;
    cartTable.appendChild(row);
    updateTotal();
}

function removeRow(btn) {
    btn.closest("tr").remove();
    updateTotal();
}

function updateTotal() {
    let total = 0;
    cartTable.querySelectorAll("tr").forEach(row => {
        const t = row.children[3].textContent.replace('₹','');
        total += parseFloat(t);
    });
    totalAmount.textContent = total.toFixed(2);
}

</script>
</body>
</html>