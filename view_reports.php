<?php
include 'db_config.php';
session_start();

// Dashboard Link
$dashboard_link = ($_SESSION['role'] == 'admin') ? 'admin_dashboard.php' : 'manager_dashboard.php';


// Get selected chart and filter
$chart = $_GET['chart'] ?? 'income_vs_expense';
$filter = $_GET['filter'] ?? 'all';

// Set filter range
$dateCondition = '';
switch ($filter) {
    case 'day':
        $dateCondition = "DATE(payment_date) = CURDATE()";
        break;
    case 'week':
        $dateCondition = "YEARWEEK(payment_date, 1) = YEARWEEK(CURDATE(), 1)";
        break;
    case 'month':
        $dateCondition = "MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())";
        break;
    case 'custom':
        $from = $_GET['from'] ?? '';
        $to = $_GET['to'] ?? '';
        if ($from && $to) {
            $dateCondition = "DATE(payment_date) BETWEEN '$from' AND '$to'";
        }
        break;
}

// Dynamic WHERE
$where = $dateCondition ? "WHERE $dateCondition" : '';

// Fetch data
$payments = $conn->query("SELECT * FROM payments $where ORDER BY payment_date");
$expenses = $conn->query("SELECT category, amount, payment_date FROM payments WHERE category = 'expense' " . ($dateCondition ? "AND $dateCondition" : ''));
$invoices = $conn->query("SELECT invoice_id, status, total_amount, invoice_date FROM invoices");
$vendor_invoices = $conn->query("SELECT vendor_invoice_id, status, total_amount, invoice_date FROM vendor_invoices");

// Vendor Receipts (with payment join)
$vendor_receipts = $conn->query("
    SELECT vr.category, p.amount, p.payment_date 
    FROM vendor_receipts vr
    JOIN payments p ON vr.vendor_receipt_id = p.invoice_id 
    WHERE p.category = 'expense' " . ($dateCondition ? "AND $dateCondition" : '')
);

// Prepare JavaScript data arrays
$incomeData = [];
$expenseData = [];
$expenseCategory = [];

while ($row = $payments->fetch_assoc()) {
    $date = $row['payment_date'];
    if ($row['category'] === 'income') {
        $incomeData[$date] = ($incomeData[$date] ?? 0) + $row['amount'];
    } elseif ($row['category'] === 'expense') {
        $expenseData[$date] = ($expenseData[$date] ?? 0) + $row['amount'];
    }
}

// Reset category-wise expense tracking
$fromDate = isset($_POST['from_date']) ? $_POST['from_date'] : null;
$toDate   = isset($_POST['to_date']) ? $_POST['to_date'] : null;

$dateColumn = 'expense_date'; // Change to your actual column name

$expenseCategory = [];

if ($fromDate && $toDate) {
    $condition = "WHERE $dateColumn BETWEEN '$fromDate' AND '$toDate'";
} else {
    $condition = '';
}

$expense_query = $conn->query("SELECT category, amount FROM expenses $condition");

while ($row = $expense_query->fetch_assoc()) {
    $cat = $row['category'];
    $expenseCategory[$cat] = ($expenseCategory[$cat] ?? 0) + $row['amount'];
}
// From vendor_invoices table
$dateConditions = "invoice_date BETWEEN '$fromDate' AND '$toDate'";


$vendorInvoicesCat = $conn->query("SELECT category, total_amount FROM vendor_invoices " . ($dateConditions ? "WHERE $dateConditions" : ''));

while ($row = $vendorInvoicesCat->fetch_assoc()) {
    $cat = $row['category'];
    $expenseCategory[$cat] = ($expenseCategory[$cat] ?? 0) + $row['total_amount'];
}


// From vendor_receipts (joined with payments)
$vendor_receipts = $conn->query("
    SELECT vr.category, p.amount 
    FROM vendor_receipts vr
    JOIN payments p ON vr.vendor_receipt_id = p.invoice_id 
    WHERE p.category = 'expense' " . ($dateCondition ? "AND $dateCondition" : '')
);
while ($row = $vendor_receipts->fetch_assoc()) {
    $cat = $row['category'];
    $expenseCategory[$cat] = ($expenseCategory[$cat] ?? 0) + $row['amount'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Reports</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@700&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
        header {
            width: 100vw;
            background: linear-gradient(90deg, #7b1fa2 60%, #512da8 100%);
            color: white;
            padding: 18px 0 18px 260px;
            font-family: 'Lora', serif;
            font-size: 2.2em;
            letter-spacing: 1px;
            box-shadow: 0 2px 10px #e1bee7;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
        }
        .sidebar {
            width: 260px; background: linear-gradient(135deg, #7b1fa2 60%, #512da8 100%);
            color: white; height: 100vh; position: fixed; padding-top: 35px; box-shadow: 2px 0 10px #e1bee7;
            top: 0; left: 0; z-index: 101;
        }
        .sidebar .logo-container {
            display: flex; justify-content: center; align-items: center; margin-bottom: 28px;
        }
        .sidebar .logo-container img {
            width: 80px; height: 80px; object-fit: contain; border-radius: 50%; background: #fff;
            box-shadow: 0 2px 8px #ede7f6;
        }
        .sidebar a {
            display: flex; align-items: center; gap: 10px;
            color: white; text-decoration: none; font-size: 1.08em;
            padding: 13px 32px; border-radius: 0 30px 30px 0;
            margin-bottom: 6px; transition: background 0.2s, padding-left 0.2s;
        }
        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,0.12); padding-left: 45px;
        }
        .main {
            margin-left: 260px; padding: 98px 40px 40px 40px;
            min-height: 100vh;
        }
        .filters, .export { margin-top: 22px; }
        .filters label { font-weight: 500; color: #512da8; margin-right: 8px; }
        select, input[type="date"] {
            padding: 8px 12px; margin-right: 12px; border-radius: 5px; border: 1px solid #b39ddb;
            background: #fff; font-size: 1em;
        }
        .filters button {
            background:hsl(282, 67.90%, 37.80%); color: white; border: none; padding: 9px 22px;
            border-radius: 5px; font-weight: 600; cursor: pointer; transition: background 0.2s;
        }
        .filters button:hover { background: #512da8; }
        .chart-container {
            margin-top: 38px; background: #fff; border-radius: 12px; box-shadow: 0 2px 12px #ede7f6;
            padding: 32px 28px 18px 28px; max-width: 900px;
        }
        table {
            width: 100%; border-collapse: collapse; background: white; margin-top: 28px;
            border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px #ede7f6;
        }
        th, td {
            padding: 14px; border-bottom: 1px solid #e1bee7; text-align: center; font-size: 1.04em;
        }
        th { background: #7b1fa2; color: white; font-weight: 600; }
        tr:last-child td { border-bottom: none; }
        .export {
            margin-top: 30px; margin-bottom: 10px;
        }
        .export a {
            background: linear-gradient(90deg, #43e97b 0%, #38f9d7 100%);
            color: #222; font-weight: 600; padding: 12px 28px; text-decoration: none;
            border-radius: 6px; box-shadow: 0 2px 8px #b2f7ef; transition: background 0.2s;
            display: inline-block;
        }
        .export a:hover { background: linear-gradient(90deg, #38f9d7 0%, #43e97b 100%); }
        @media (max-width: 900px) {
            header { padding-left: 0; font-size: 1.3em; }
            .main { padding: 70px 8px 20px 8px; }
            .chart-container { padding: 18px 5px 5px 5px; }
            .sidebar { width: 100vw; height: auto; position: static; box-shadow: none; }
            .main { margin-left: 0; }
        }
    </style>
    </head>
    <body>
    <div class="header-container">
        <div class="custom-header">
            View Reports
        </div>
    </div>
    <style>
    .header-container {
        position: relative;
        margin-left: 260px;
        width: calc(100% - 260px);
        max-width: 100%;
        display: flex;
        justify-content: flex-start;
        align-items: center;
        margin-top: 0;
        box-shadow: 0 2px 12px 0 #b39ddb;
        min-height: 80px; /* Increased height */
        height: 100px;     /* Increased height */
    }
    .custom-header {
        font-family: 'Lora', serif;
        width: 100%;
        font-size: 1.5em;
        color: rgb(123, 31, 162);
        background: none;
        padding: 14px;
        font-weight: 700;
        letter-spacing: 1px;
    }
    @media (max-width: 900px) {
        .header-container {
            margin-left: 0;
            width: 100vw;
            justify-content: center;
            max-width: 100vw;
            min-height: 80px;
            height: 100px;
        }
    }
    </style>
    <div class="sidebar">
        <div class="logo-container">
            <img src="logo.png" alt="Logo" style="background:none; box-shadow:0 2px 8px #ede7f6; width:80px; height:80px; object-fit:cover; border-radius:50%;">
        </div>
        <div style="text-align:center; margin-bottom:18px;">
            <h2 style="margin:0;">Perfect Plate</h2>
        </div>
        <a href="<?php echo $dashboard_link; ?>" class="<?= basename($_SERVER['PHP_SELF']) == $dashboard_link ? 'active' : '' ?>">🏠 Dashboard</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="main">
        <form method="GET" class="filters">
            <label>Chart Type:</label>
            <select name="chart">
                <option value="income_vs_expense" <?= $chart === 'income_vs_expense' ? 'selected' : '' ?>>Income vs Expense</option>
                <option value="expense_by_category" <?= $chart === 'expense_by_category' ? 'selected' : '' ?>>Expense by Category</option>
                <option value="invoice_status" <?= $chart === 'invoice_status' ? 'selected' : '' ?>>Invoice Paid vs Unpaid</option>
            </select>

            <label>Filter:</label>
            <select name="filter" onchange="this.form.submit()">
                <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Time</option>
                <option value="day" <?= $filter === 'day' ? 'selected' : '' ?>>Today</option>
                <option value="week" <?= $filter === 'week' ? 'selected' : '' ?>>This Week</option>
                <option value="month" <?= $filter === 'month' ? 'selected' : '' ?>>This Month</option>
                <option value="custom" <?= $filter === 'custom' ? 'selected' : '' ?>>Custom Range</option>
            </select>

            <?php if ($filter === 'custom') { ?>
                <input type="date" name="from" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>" required>
                <input type="date" name="to" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>" required>
            <?php } ?>

            <button type="submit">Apply</button>
        </form>

        <div class="chart-container">
            <canvas id="chartCanvas" height="100"></canvas>
        </div>

        <div class="export">
            <a href="generate_excel.php?type=<?php echo $chart; ?>&filter=<?php echo $filter; ?><?php if($filter==='custom'){ echo '&from='.urlencode($_GET['from']??'').'&to='.urlencode($_GET['to']??''); } ?>">📥 Download Detailed Report</a>
        </div>

        <!-- Data Table Preview -->
        <?php if ($chart === 'income_vs_expense') { ?>
            <table>
                <tr><th>Date</th><th>Income (₹)</th><th>Expense (₹)</th></tr>
                <?php
                $dates = array_unique(array_merge(array_keys($incomeData), array_keys($expenseData)));
                sort($dates);
                foreach ($dates as $d) {
                    echo "<tr><td>$d</td><td>" . ($incomeData[$d] ?? 0) . "</td><td>" . ($expenseData[$d] ?? 0) . "</td></tr>";
                }
                ?>
            </table>
        <?php } elseif ($chart === 'expense_by_category') { ?>
            <table>
                <tr><th>Category</th><th>Total Expense (₹)</th></tr>
                <?php foreach ($expenseCategory as $cat => $val) {
                    echo "<tr><td>$cat</td><td>" . number_format($val, 2) . "</td></tr>";
                } ?>
            </table>
        <?php } elseif ($chart === 'invoice_status') { ?>
            <table>
                <tr><th>Type</th><th>ID</th><th>Status</th><th>Total (₹)</th><th>Date</th></tr>
                <?php
                // Apply filter to invoices and vendor_invoices
                $invoice_where = '';
                if ($filter === 'custom') {
                    $from = $_GET['from'] ?? '';
                    $to = $_GET['to'] ?? '';
                    if ($from && $to) {
                        $invoice_where = "WHERE invoice_date BETWEEN '$from' AND '$to'";
                    }
                } elseif ($filter === 'day') {
                    $invoice_where = "WHERE DATE(invoice_date) = CURDATE()";
                } elseif ($filter === 'week') {
                    $invoice_where = "WHERE YEARWEEK(invoice_date, 1) = YEARWEEK(CURDATE(), 1)";
                } elseif ($filter === 'month') {
                    $invoice_where = "WHERE MONTH(invoice_date) = MONTH(CURDATE()) AND YEAR(invoice_date) = YEAR(CURDATE())";
                }

                // Fetch filtered customer invoices
                $filtered_invoices = $conn->query("SELECT invoice_id, status, total_amount, invoice_date FROM invoices $invoice_where");
                while ($row = $filtered_invoices->fetch_assoc()) {
                    echo "<tr><td>Customer</td><td>INV-" . $row['invoice_id'] . "</td><td>" . ucfirst($row['status']) . "</td><td>" . $row['total_amount'] . "</td><td>" . $row['invoice_date'] . "</td></tr>";
                }

                // Fetch filtered vendor invoices
                $filtered_vendor_invoices = $conn->query("SELECT vendor_invoice_id, status, total_amount, invoice_date FROM vendor_invoices $invoice_where");
                while ($row = $filtered_vendor_invoices->fetch_assoc()) {
                    echo "<tr><td>Vendor</td><td>VEN-" . $row['vendor_invoice_id'] . "</td><td>" . ucfirst($row['status']) . "</td><td>" . $row['total_amount'] . "</td><td>" . $row['invoice_date'] . "</td></tr>";
                }
                ?>
            </table>
        <?php } ?>
    </div>

    <script>
    const chartType = "<?php echo $chart; ?>";
    const ctx = document.getElementById('chartCanvas').getContext('2d');
    let chartData;

    <?php if ($chart === 'income_vs_expense') { ?>
        chartData = {
            type: 'line',
            data: {
                labels: <?= json_encode(array_values(array_unique(array_merge(array_keys($incomeData), array_keys($expenseData))))); ?>,
                datasets: [
                    {
                        label: 'Income',
                        data: <?= json_encode(array_values($incomeData)); ?>,
                        borderColor: '#43e97b',
                        backgroundColor: 'rgba(67,233,123,0.15)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: '#43e97b'
                    },
                    {
                        label: 'Expense',
                        data: <?= json_encode(array_values($expenseData)); ?>,
                        borderColor: '#f44336',
                        backgroundColor: 'rgba(244,67,54,0.12)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: '#f44336'
                    }
                ]
            },
            options: {
                plugins: {
                    legend: { display: true, position: 'top' }
                },
                scales: {
                    x: { title: { display: true, text: 'Date' } },
                    y: { title: { display: true, text: 'Amount (₹)' }, beginAtZero: true }
                }
            }
        };
    <?php } elseif ($chart === 'expense_by_category') { ?>
        chartData = {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_keys($expenseCategory)); ?>,
                datasets: [{
                    data: <?= json_encode(array_values($expenseCategory)); ?>,
                    backgroundColor: [
                        '#8e24aa', '#f44336', '#ff9800', '#4caf50', '#2196f3',
                        '#ffd600', '#00bcd4', '#cddc39', '#ff5722', '#607d8b'
                    ]
                }]
            },
            options: {
                plugins: {
                    legend: { display: true, position: 'right' }
                }
            }
        };
    <?php } elseif ($chart === 'invoice_status') {
        $paid = 0;
        $unpaid = 0;

        // Use filter for invoice status chart
        if ($filter === 'custom') {
            $from = $_GET['from'] ?? '';
            $to = $_GET['to'] ?? '';
            $dateCondition = ($from && $to) ? "invoice_date BETWEEN '$from' AND '$to'" : '';
        } elseif ($filter === 'day') {
            $dateCondition = "DATE(invoice_date) = CURDATE()";
        } elseif ($filter === 'week') {
            $dateCondition = "YEARWEEK(invoice_date, 1) = YEARWEEK(CURDATE(), 1)";
        } elseif ($filter === 'month') {
            $dateCondition = "MONTH(invoice_date) = MONTH(CURDATE()) AND YEAR(invoice_date) = YEAR(CURDATE())";
        } else {
            $dateCondition = '';
        }

        // Count paid and unpaid invoices for customers
        $res1 = $conn->query("SELECT status FROM invoices " . ($dateCondition ? "WHERE $dateCondition" : ''));
        while ($r = $res1->fetch_assoc()) {
            strtolower($r['status']) === 'paid' ? $paid++ : $unpaid++;
        }

        // Count paid and unpaid invoices for vendors
        $res2 = $conn->query("SELECT status FROM vendor_invoices " . ($dateCondition ? "WHERE $dateCondition" : ''));
        while ($r = $res2->fetch_assoc()) {
            strtolower($r['status']) === 'paid' ? $paid++ : $unpaid++;
        }
    ?>
        chartData = {
            type: 'bar',
            data: {
                labels: ['Paid', 'Unpaid'],
                datasets: [{
                    label: 'Invoice Status',
                    data: [<?php echo $paid; ?>, <?php echo $unpaid; ?>],
                    backgroundColor: ['#43e97b', '#f44336'],
                    borderRadius: 8,
                    barPercentage: 0.5
                }]
            },
            options: {
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { title: { display: false } },
                    y: { title: { display: true, text: 'Count' }, beginAtZero: true }
                }
            }
        };
    <?php } ?>

    new Chart(ctx, chartData);
    </script>
 
    </body>
</html>