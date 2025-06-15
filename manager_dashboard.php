<?php
session_start();
include("db_config.php"); // Ensure you have a database connection file

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit();
}
// Prevent caching


// Fetch Total Invoices
$invoiceResult = $conn->query("SELECT COUNT(*) as total_invoices FROM Invoices");
$totalInvoices = $invoiceResult->fetch_assoc()['total_invoices'];

// Fetch total receipts (counting rows in the Receipts table)
$receiptResult = $conn->query("SELECT COUNT(*) as total_receipts FROM Receipts");
$totalReceipts = $receiptResult->fetch_assoc()['total_receipts'];

// Fetch recent transactions (Latest invoices and receipts)
$recentTransactions = $conn->query("
    (SELECT 'Invoice' as type, invoice_id as id, total_amount as amount, invoice_date as date FROM Invoices ORDER BY invoice_date DESC LIMIT 5)
    UNION
    (SELECT 'Receipt' as type, receipt_id as id, total_amount as amount, receipt_date as date FROM Receipts ORDER BY receipt_date DESC LIMIT 5)
    ORDER BY date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
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
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background-color:rgb(123, 31, 162); color: white; }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <img src="logo.png" alt="Restaurant Logo">
    <h2>Perfect Plate</h2>
    <a href="manager_dashboard.php"><i class="fas fa-tachometer-alt"></i>  Dashboard</a>
    <a href="manage_expenses.php"><i class="fas fa-wallet"></i>  Manage Expense</a>
    <a href="manage_invoice.php"><i class="fas fa-file-invoice"></i>  Manage Invoices</a>
    <a href="manage_receipts.php"><i class="fas fa-receipt"></i>  Manage Receipts</a>
    <a href="manage_vendor_invoices.php"><i class="fas fa-file-invoice-dollar"></i>  Manage Vendor Invoices</a>
    <a href="manage_vendor_receipts.php"><i class="fas fa-money-check"></i>  Manage Vendor Receipts</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i>  Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="header">
        <h1>Manager Dashboard</h1>
        <button class="logout" onclick="window.location.href='logout.php'">Logout</button>
    </div>

    <div class="dashboard-widgets">
        <div class="widget">
            <h2><i class="fa fa-file-invoice-dollar text-primary"></i>Total Invoices</h2>
            <p><?php echo $totalInvoices; ?></p>
        </div>
        <div class="widget">
            <h2><i class="fa fa-receipt text-success"></i>Total receipts</h2>
            <p><?php echo $totalReceipts; ?></p>
        </div>
        
    </div>
    <div class="table">
        <h2>Recent Transactions</h2>
        <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; margin-top: 20px; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>ID</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $recentTransactions->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['type']; ?></td>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['amount']; ?></td>
                        <td><?php echo $row['date']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
                </div>
</div>

</body>
</html>
