<?php
require '../includes/auth.php';
require '../includes/db.php';

$sales = $conn->query("SELECT * FROM sales_log ORDER BY sold_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales Report</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h1>📊 Sales Report</h1>
    <table>
        <thead>
            <tr>
                <th>Date/Time</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $sales->fetch_assoc()): ?>
            <tr>
                <td><?= $row['sold_at'] ?></td>
                <td><?= $row['product_name'] ?></td>
                <td><?= $row['quantity'] ?></td>
                <td>₹<?= $row['price'] ?></td>
                <td>₹<?= $row['total'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <br>
    <a href="dashboard.php" class="btn primary">⬅ Back to Dashboard</a>
</div>
</body>
</html>
