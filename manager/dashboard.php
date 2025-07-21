<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../includes/header.php';
require '../includes/auth.php';
require '../includes/db.php';

// --- FILTERS & PAGINATION ---
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'name_asc';
$page = max((int)($_GET['page'] ?? 1), 1);
$limit = 20;
$offset = ($page - 1) * $limit;

// --- Build filter query ---
$where = "WHERE 1";
$params = [];

if (!empty($search)) {
    $where .= " AND name LIKE ?";
    $params[] = "%$search%";
}
if (!empty($category)) {
    $where .= " AND category = ?";
    $params[] = $category;
}

// --- Sorting ---
$sort_sql = "ORDER BY name ASC";
switch ($sort) {
    case 'price_asc': $sort_sql = "ORDER BY price ASC"; break;
    case 'price_desc': $sort_sql = "ORDER BY price DESC"; break;
    case 'quantity_asc': $sort_sql = "ORDER BY quantity ASC"; break;
    case 'quantity_desc': $sort_sql = "ORDER BY quantity DESC"; break;
    case 'expiry_asc': $sort_sql = "ORDER BY expiry_date ASC"; break;
    case 'expiry_desc': $sort_sql = "ORDER BY expiry_date DESC"; break;
}

// --- Get filtered products with limit ---
$stmt = $conn->prepare("SELECT * FROM products $where $sort_sql LIMIT $limit OFFSET $offset");
if (!empty($params)) {
    $types = str_repeat("s", count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// --- Get total for pagination ---
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM products $where");
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_rows = $count_stmt->get_result()->fetch_row()[0];
$total_pages = ceil($total_rows / $limit);

// --- Load low stock, expiring, popular ---
$low_stock = $conn->query("SELECT name, quantity FROM products WHERE quantity < 10 ORDER BY quantity ASC LIMIT 10");
$expiry_limit = date('Y-m-d', strtotime('+30 days'));
$expiring = $conn->query("SELECT name, expiry_date FROM products WHERE expiry_date <= '$expiry_limit' ORDER BY expiry_date ASC LIMIT 10");
$popular = $conn->query("SELECT name, times_purchased FROM products ORDER BY times_purchased DESC LIMIT 10");

// --- Load all unique categories ---
$categories = $conn->query("SELECT DISTINCT category FROM products ORDER BY category ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manager Dashboard</title>
    <style>
        /* Add your theme styles here (same as before)... */
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
        .container { max-width: 1200px; margin: auto; padding: 30px; }
        h1 { text-align: center; font-size: 34px; color: #4c3b28; margin-bottom: 30px; }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px; margin-bottom: 30px;
        }
        .card {
            height: 300px; overflow-y: auto;
            padding: 20px; border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .card ul { list-style: none; padding-left: 0; }
        .card li { margin-bottom: 10px; font-size: 15px; }
        .card.low-stock { background-color: #ffefd5; border: 1px solid #f7c08a; }
        .card.expiry { background-color: #f9dcdc; border: 1px solid #e09999; }
        .card.popular { background-color: #e5fce7; border: 1px solid #8bc34a; }

        .buttons { text-align: center; margin-bottom: 30px; }
        .btn {
            padding: 10px 16px; margin: 5px;
            border-radius: 8px;
            display: inline-block;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s ease;
        }
        .btn.primary { background-color: #d09d5e; color: white; }
        .btn.secondary { background-color: #a57c65; color: white; }
        .btn.danger { background-color: #ce5b5b; color: white; }
        .btn.small { padding: 6px 12px; font-size: 13px; }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fffaf3;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #e0d6c7;
            text-align: left;
            font-size: 14px;
        }
        tr:hover { background-color: #f4eee7; }

        hr { margin: 40px 0 20px; border-top: 1px solid #d4c2ad; }

        .filter-form {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .filter-form input, .filter-form select {
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .pagination {
            margin-top: 15px;
            text-align: center;
        }
        .pagination a {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 3px;
            background-color: #f4eee7;
            border-radius: 5px;
            color: #333;
            text-decoration: none;
        }
        .pagination a.active {
            font-weight: bold;
            background-color: #d09d5e;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Welcome, Manager</h1>

        <div class="dashboard-grid">
            <div class="card low-stock">
                <h2>⚠️ Low Stock</h2>
                <ul>
                    <?php while($row = $low_stock->fetch_assoc()): ?>
                        <li><?= htmlspecialchars($row['name']) ?> — <?= $row['quantity'] ?> units</li>
                    <?php endwhile; ?>
                </ul>
            </div>
            <div class="card expiry">
                <h2>⏰ Expiring Soon</h2>
                <ul>
                    <?php while($row = $expiring->fetch_assoc()): ?>
                        <li><?= htmlspecialchars($row['name']) ?> — <?= $row['expiry_date'] ?></li>
                    <?php endwhile; ?>
                </ul>
            </div>
            <div class="card popular">
                <h2>🔥 Most Popular</h2>
                <ul>
                    <?php while($row = $popular->fetch_assoc()): ?>
                        <li><?= htmlspecialchars($row['name']) ?> — <?= $row['times_purchased'] ?> sales</li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>

        <div class="buttons">
            <a href="add_product.php" class="btn primary">➕ Add Product</a>
            <a href="update_product.php" class="btn secondary">✏️ Update Product</a>
            <a href="delete_product.php" class="btn danger">🗑️ Delete Product</a>
            <a href="manager_tools.php" class="btn primary">📊 Sales Analytics & Reports</a>
            <a href="../auth/logout.php" class="btn danger">🚪 Logout</a>
        </div>

        <hr>

        <form method="GET" class="filter-form">
            <input type="text" name="search" placeholder="🔍 Search name..." value="<?= htmlspecialchars($search) ?>">
            <select name="category">
                <option value="">All Categories</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= $cat['category'] ?>" <?= $category === $cat['category'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['category']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <select name="sort">
                <option value="name_asc" <?= $sort == 'name_asc' ? 'selected' : '' ?>>Name ↑</option>
                <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Price ↑</option>
                <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Price ↓</option>
                <option value="quantity_asc" <?= $sort == 'quantity_asc' ? 'selected' : '' ?>>Quantity ↑</option>
                <option value="quantity_desc" <?= $sort == 'quantity_desc' ? 'selected' : '' ?>>Quantity ↓</option>
                <option value="expiry_asc" <?= $sort == 'expiry_asc' ? 'selected' : '' ?>>Expiry ↑</option>
                <option value="expiry_desc" <?= $sort == 'expiry_desc' ? 'selected' : '' ?>>Expiry ↓</option>
            </select>
            <button type="submit" class="btn small primary">Filter</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Qty</th>
                    <th>Price (₹)</th>
                    <th>Expiry</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td><?= number_format($row['price'], 2) ?></td>
                    <td><?= $row['expiry_date'] ?></td>
                    <td>
                        <a href="update_product.php?id=<?= $row['id'] ?>" class="btn small secondary">Edit</a>
                        <a href="delete_product.php?id=<?= $row['id'] ?>" class="btn small danger" onclick="return confirm('Delete this product?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                   class="<?= $i == $page ? 'active' : '' ?>">
                   <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
</body>
</html>
