<?php
session_start();
include 'db_config.php';
$dashboard_link = ($_SESSION['role'] === 'admin') ? 'admin_dashboard.php' : 'manager_dashboard.php';
// Fetch invoices
// Prevent caching

$sql = "SELECT i.invoice_id, c.customer_name, c.contact_info, i.total_amount, i.status, i.payment_due_date 
        FROM invoices i 
        JOIN customers c ON i.customer_id = c.customer_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Invoices</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style> .sidebar {
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
        .print { padding: 5px 10px; background-color:rgb(123, 31, 162); color: white; border: none; border-radius:5px; cursor: pointer; }
        </style>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<div class="sidebar">
    <img src="logo.png" alt="Restaurant Logo">
    <h2>Perfect Plate</h2>
    <a href="<?= $dashboard_link ?>"><i class="fas fa-tachometer-alt"></i>  Dashboard</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i>  Logout</a>
</div>
<div class= "main-content">
    <div class="header">
        <h1>Manage Invoices</h1>
        <a href="create_invoice.php"><button class="print">Create New Invoice</button></a>
    </div>
    <br>

<table border="1">
    <tr>
        <th>Invoice ID</th>
        <th>Customer Name</th>
        <th>Contact</th>
        <th>Total Amount</th>
        <th>Payment Status</th>
        <th>Payment Due Date</th>
        <th>Actions</th>
    </tr>
    
    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?php echo $row['invoice_id']; ?></td>
        <td><?php echo $row['customer_name']; ?></td>
        <td><?php echo $row['contact_info']; ?></td>
        <td><?php echo number_format($row['total_amount'], 2); ?></td>
        <td>
            <select class="update_status" data-invoice-id="<?php echo $row['invoice_id']; ?>">
                <option value="Paid" <?php echo ($row['status'] == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                <option value="Unpaid" <?php echo ($row['status'] == 'Unpaid') ? 'selected' : ''; ?>>Unpaid</option>
            </select>
        </td>
        <td><?php echo ($row['status'] == 'Unpaid') ? $row['payment_due_date'] : "-"; ?></td>
        <td>
            <button class="print-btn" onclick="printInvoice(<?php echo $row['invoice_id']; ?>)">Print Invoice</button>
        </td>
    </tr>
    <?php } ?>
    </div>
</table>

<script>
$(document).on("change", ".update_status", function() {
    var invoice_id = $(this).data("invoice-id");
    var new_status = $(this).val();

    $.ajax({
        url: "update_invoice_status.php",
        type: "POST",
        data: { invoice_id: invoice_id, new_status: new_status },
        success: function(response) {
            alert(response);
            location.reload(); // Refresh the page to update status
        }
    });
});

function printInvoice(invoice_id) {
    window.open("print_invoice.php?invoice_id=" + invoice_id, "_blank");
}
</script>

</body>
</html>