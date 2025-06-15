<?php
session_start();
include("db_config.php"); // Ensure you have a database connection file

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}


// Fetch Total Income
$income_query = "SELECT SUM(amount) AS total_income FROM payments WHERE category = 'income'";
$income_result = $conn->query($income_query);
if ($income_result && $income_result->num_rows > 0) {
    $income_row = $income_result->fetch_assoc();
    $total_income = $income_row['total_income'] ?? 0;
} else {
    $total_income = 0;
}

// Fetch Total Expensesit
$expense_query = "SELECT SUM(amount) AS total_expenses FROM payments WHERE category = 'expense'";
$expense_result = $conn->query($expense_query);
if ($expense_result && $expense_result->num_rows > 0) {
    $expense_row = $expense_result->fetch_assoc();
    $total_expenses = $expense_row['total_expenses'] ?? 0;
} else {
    $total_expenses = 0;
}

// Calculate Net Profit
$net_profit = $total_income - $total_expenses;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>

    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Lora', serif;
        }
        body {
            display: flex;
            height: 100vh;
            background: #f4f4f4;
        }
        .sidebar {
            width: 250px;
            background: #7b1fa2;
            color: white;
            height: 100vh;
            position: fixed;
            padding-top: 20px;
        }
        .sidebar h2 {
            text-align: center;
            font-size: 22px;
            margin-bottom: 20px;
        }
        .sidebar img {
            display: block;
            margin: 0 auto;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 10px;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            font-size: 16px;
            transition: 0.3s;
        }
        .sidebar a i {
            margin-right: 12px;
            font-size: 18px;
        }
        .sidebar a:hover {
            background: #5e1386;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
            background: none; /* Removed background */
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .header h1 {
            font-size: 22px;
            color: #7b1fa2;
        }
        .logout {
            background: red;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
            border: 2px solid darkred; /* Red box for logout */
        }
        .logout:hover {
            background: darkred;
        }
        .dashboard-widgets {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .widget {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: 0.3s;
        }
        .widget h2 {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }
        .widget p {
            font-size: 20px;
            font-weight: bold;
            color: #7b1fa2;
        }
        .widget:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <img src="logo.png" alt="Restaurant Logo">
    <h2>Perfect Plate</h2>
    <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i>  Dashboard</a>
    <a href="manage_expenses.php"><i class="fas fa-wallet"></i>  Manage Expense</a>

    <a href="manage_users.php"><i class="fas fa-users"></i>  Manage Users</a>
    <a href="manage_invoice.php"><i class="fas fa-file-invoice"></i>  Manage Invoices</a>
    <a href="manage_receipts.php"><i class="fas fa-receipt"></i>  Manage Receipts</a>
    <a href="manage_vendors.php"><i class="fas fa-truck"></i>  Manage Vendors</a>
    <a href="manage_vendor_invoices.php"><i class="fas fa-file-invoice-dollar"></i>  Manage Vendor Invoices</a>
    <a href="manage_vendor_receipts.php"><i class="fas fa-money-check"></i>  Manage Vendor Receipts</a>
    <a href="manage_payments.php"><i class="fas fa-credit-card"></i>  Manage Payments</a>
    <a href="manage_items.php"><i class="fas fa-utensils"></i>  Manage Items</a>
    <a href="view_reports.php"><i class="fas fa-chart-bar"></i>  View Reports</a>
    
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i>  Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="header">
        <h1>Admin Dashboard</h1>
        <button class="logout" onclick="window.location.href='logout.php'">Logout</button>
    </div>

    <div class="dashboard-widgets">
        <div class="widget">
            <h2>Total Income</h2>
            <p>₹ <?php echo number_format((float)$total_income, 2); ?></p>
        </div>
        <div class="widget">
            <h2>Total Expenses</h2>
            <p>₹ <?php echo number_format((float)$total_expenses, 2); ?></p>
        </div>
        <div class="widget">
            <h2>Net Profit</h2>
            <p>₹ <?php echo number_format((float)$net_profit, 2); ?></p>
        </div>
    </div>
</div>

</body>
</html>
