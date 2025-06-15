<?php
session_start();
include 'db_config.php';
$dashboard_link = ($_SESSION['role'] === 'admin') ? 'admin_dashboard.php' : 'manager_dashboard.php';

// Prevent caching

$sql = "SELECT r.*, i.invoice_id, c.customer_name, c.contact_info 
        FROM receipts r
        LEFT JOIN invoices i ON r.invoice_id = i.invoice_id
        LEFT JOIN customers c ON i.customer_id = c.customer_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Receipts</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@700&display=swap" rel="stylesheet">
    <style>
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
            text-align: center;
        }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background-color:rgb(123, 31, 162); color: white; }
        .print-btn { padding: 5px 10px; background: green; color: white; border: none; cursor: pointer; }
        .print { padding: 5px 10px; background-color:rgb(123, 31, 162); color: white; border: none; cursor: pointer; border-radius: 10px; }
        .print-btn:hover { background: darkgreen; }
    </style>
</head>
<body>
<div class="sidebar">
    <img src="logo.png" alt="Restaurant Logo">
    <h2>Perfect Plate</h2>
    <a href="<?= $dashboard_link ?>"><i class="fas fa-tachometer-alt"></i>  Dashboard</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i>  Logout</a>
</div>
<div class="main-content">
    <div class="header">
        <h1>Manage Receipts</h1>
        <a href="create_receipt.php"><button class="print">Create New Receipt</button></a>
    </div>
    <br>

<table border="1">
    <tr>
        <th>Receipt ID</th>
        <th>Invoice ID</th>
        <th>Customer</th>
        <th>Total Amount</th>
        <th>Payment Method</th>
        <th>Date</th>
        <th>Action</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['receipt_id']; ?></td>
            <td><?php echo $row['invoice_id'] ? $row['invoice_id'] : "Manual Entry"; ?></td>
            <td><?php echo $row['customer_name'] . " (" . $row['contact_info'] . ")"; ?></td>
            <td>₹<?php echo number_format($row['total_amount'], 2); ?></td>
            <td><?php echo $row['payment_method']; ?></td>
            <td><?php echo date("d-M-Y", strtotime($row['receipt_date'])); ?></td>
            <td><a href="print_receipt.php?receipt_id=<?php echo $row['receipt_id']; ?>" target="_blank">
                <button class="print-btn">Print</button></a>
            </td>
        </tr>
    <?php } ?>
</table>
</div>

</body>
</html>
