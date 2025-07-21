<?php
require '../includes/auth.php';
require '../includes/db.php';
require '../includes/header.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- Get sales data for charts ---
$daily = $conn->query("SELECT DATE(sold_at) as date, SUM(total) as revenue FROM sales_log GROUP BY DATE(sold_at) ORDER BY date DESC LIMIT 30");
$weekly = $conn->query("SELECT YEARWEEK(sold_at) as week, SUM(total) as revenue FROM sales_log GROUP BY YEARWEEK(sold_at) ORDER BY week DESC LIMIT 10");
$monthly = $conn->query("SELECT DATE_FORMAT(sold_at, '%Y-%m') as month, SUM(total) as revenue FROM sales_log GROUP BY month ORDER BY month DESC LIMIT 12");

// --- Top selling products ---
$top_products = $conn->query("SELECT product_name, SUM(quantity) as total_sold FROM sales_log GROUP BY product_name ORDER BY total_sold DESC LIMIT 10");

// --- Hourly sales heatmap ---
$hourly = $conn->query("SELECT HOUR(sold_at) as hour, COUNT(*) as count FROM sales_log GROUP BY hour ORDER BY hour ASC");

// --- Monthly summary (total revenue) ---
$monthly_summary = $conn->query("SELECT DATE_FORMAT(sold_at, '%Y-%m') as month, SUM(total) as total_sales FROM sales_log GROUP BY month ORDER BY month DESC");

// --- Export handlers ---
if (isset($_GET['export']) && $_GET['export'] === 'sales_csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sales_log.csv"');
    $export = $conn->query("SELECT * FROM sales_log");
    echo "Product Name,Quantity,Price,Total,Sold At\n";
    while ($r = $export->fetch_assoc()) {
        echo "{$r['product_name']},{$r['quantity']},{$r['price']},{$r['total']},{$r['sold_at']}\n";
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>📊 Manager Tools</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0; padding: 0;
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
            font-size: 32px;
            margin-bottom: 20px;
            color: #4c3b28;
        }
        .card {
            background-color: #fffaf3;
            border: 1px solid #e0d6c7;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        canvas {
            width: 100% !important;
            height: auto !important;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #e0d6c7;
            font-size: 14px;
            text-align: left;
        }
        .btn {
            display: inline-block;
            padding: 10px 16px;
            background-color: #d09d5e;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 5px 0;
        }
        .btn:hover {
            background-color: #ba874d;
        } */
         /* Manager Tools Styles */
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
    font-size: 32px;
    color: #4c3b28;
    margin-bottom: 30px;
}
.chart-section, .summary-cards {
    margin-bottom: 40px;
}
.card {
    background: #fffaf3;
    border-radius: 16px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    padding: 20px;
    margin-bottom: 20px;
}
.table-container {
    overflow-x: auto;
}
table {
    width: 100%;
    border-collapse: collapse;
    background-color: #fffdf7;
}
th, td {
    padding: 12px;
    border-bottom: 1px solid #e0d6c7;
    text-align: left;
    font-size: 14px;
}
tr:hover {
    background-color: #f4eee7;
}
.filter-form {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}
.filter-form input, .filter-form select {
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
.btn {
    padding: 10px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    cursor: pointer;
}
.btn.primary { background-color: #d09d5e; color: white; }
.btn.secondary { background-color: #a57c65; color: white; }
.btn.danger { background-color: #ce5b5b; color: white; }
.btn:hover { opacity: 0.9; }

    </style>
</head>
<body>
    <div class="container">
        <h1>📈 Manager Sales & Reports</h1>

        <div class="card">
            <h2>📆 Daily Revenue (Last 30 days)</h2>
            <canvas id="dailyChart"></canvas>
        </div>

        <div class="card">
            <h2>📅 Weekly Revenue</h2>
            <canvas id="weeklyChart"></canvas>
        </div>

        <div class="card">
            <h2>📅 Monthly Revenue</h2>
            <canvas id="monthlyChart"></canvas>
        </div>

        <div class="card">
            <h2>🔥 Top 10 Selling Products</h2>
            <table>
                <tr><th>Product</th><th>Total Sold</th></tr>
                <?php while($row = $top_products->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                        <td><?= $row['total_sold'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <div class="card">
            <h2>🕒 Hourly Sales Heatmap</h2>
            <table>
                <tr>
                    <?php for ($h = 0; $h < 24; $h++): ?>
                        <th><?= $h ?>h</th>
                    <?php endfor; ?>
                </tr>
                <tr>
                    <?php
                    $hour_counts = array_fill(0, 24, 0);
                    while ($row = $hourly->fetch_assoc()) {
                        $hour_counts[(int)$row['hour']] = $row['count'];
                    }
                    $max = max($hour_counts);
                    foreach ($hour_counts as $count) {
                        $intensity = $max ? intval(($count / $max) * 255) : 0;
                        echo "<td style='background-color: rgb(255,".(255-$intensity).",".(255-$intensity).")'>$count</td>";
                    }
                    ?>
                </tr>
            </table>
            <a href="dashboard.php" class="btn" style="margin-bottom: 20px;">⬅️ Back to Dashboard</a>

        </div>

        <div class="card">
            <h2>📤 Export & Reports</h2>
            <a href="?export=sales_csv" class="btn">⬇️ Download Sales CSV</a>
            <p><strong>Monthly Totals:</strong></p>
            <table>
                <tr><th>Month</th><th>Total Revenue (₹)</th></tr>
                <?php while($r = $monthly_summary->fetch_assoc()): ?>
                    <tr>
                        <td><?= $r['month'] ?></td>
                        <td>₹<?= number_format($r['total_sales'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

<script>
const dailyChart = document.getElementById('dailyChart').getContext('2d');
const weeklyChart = document.getElementById('weeklyChart').getContext('2d');
const monthlyChart = document.getElementById('monthlyChart').getContext('2d');

<?php
// Prepare JS arrays
$daily_data = ['labels' => [], 'values' => []];
while($r = $daily->fetch_assoc()) {
    array_unshift($daily_data['labels'], $r['date']);
    array_unshift($daily_data['values'], $r['revenue']);
}
$weekly_data = ['labels' => [], 'values' => []];
while($r = $weekly->fetch_assoc()) {
    array_unshift($weekly_data['labels'], $r['week']);
    array_unshift($weekly_data['values'], $r['revenue']);
}
$monthly_data = ['labels' => [], 'values' => []];
while($r = $monthly->fetch_assoc()) {
    array_unshift($monthly_data['labels'], $r['month']);
    array_unshift($monthly_data['values'], $r['revenue']);
}
?>

new Chart(dailyChart, {
    type: 'line',
    data: {
        labels: <?= json_encode($daily_data['labels']) ?>,
        datasets: [{
            label: 'Revenue (₹)',
            data: <?= json_encode($daily_data['values']) ?>,
            fill: true,
            borderColor: '#d09d5e',
            backgroundColor: 'rgba(208, 157, 94, 0.3)',
            tension: 0.3
        }]
    }
});
new Chart(weeklyChart, {
    type: 'line',
    data: {
        labels: <?= json_encode($weekly_data['labels']) ?>,
        datasets: [{
            label: 'Revenue (₹)',
            data: <?= json_encode($weekly_data['values']) ?>,
            borderColor: '#a57c65',
            backgroundColor: 'rgba(165, 124, 101, 0.3)',
            fill: true,
            tension: 0.3
        }]
    }
});
new Chart(monthlyChart, {
    type: 'bar',
    data: {
        labels: <?= json_encode($monthly_data['labels']) ?>,
        datasets: [{
            label: 'Revenue (₹)',
            data: <?= json_encode($monthly_data['values']) ?>,
            backgroundColor: '#8bc34a'
        }]
    }
});
</script>
</body>
</html>
