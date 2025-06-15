<?php
include 'db_config.php';

if (!isset($_GET['invoice_id'])) {
    die("Invoice ID not provided.");
}

$invoice_id = $_GET['invoice_id'];

// Fetch invoice details
$sql = "SELECT i.invoice_id, i.invoice_date, i.total_amount, i.status, 
               c.customer_name, c.contact_info 
        FROM invoices i 
        JOIN customers c ON i.customer_id = c.customer_id 
        WHERE i.invoice_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$result = $stmt->get_result();
$invoice = $result->fetch_assoc();

if (!$invoice) {
    die("Invoice not found.");
}

// Fetch purchased items
$sql = "SELECT item_name, quantity, unit_price, gst, total_price 
        FROM invoice_items WHERE invoice_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $invoice['invoice_id']; ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: url('bg.png') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Lora', serif;
        }
        .invoice-container {
            width: 80%;
            margin: auto;
            padding: 120px 80px;
            position: relative;
            z-index: 1;
        }
        .invoice-details {
            background: rgba(255, 255, 255, 0);
            padding: 50px;
            font-size: 18px;
            font-weight: bold;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.8);
        }
        table, th, td {
            border: 1px solid black;
            text-align: left;
            padding: 10px;
        }
        .total-amount {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
        }
        .print-btn {
            display: block;
            margin: 30px auto;
            padding: 12px 24px;
            font-size: 16px;
            background:green;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        .printn-btn {
            display: block;
            margin: 30px auto;
            padding: 12px 24px;
            font-size: 16px;
            background:#7F8CAA;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        .print-btn:hover {
            background-color:rgb(0, 255, 76);
        }

        /* Hide print button when printing */
        @media print {
            .print-btn {
                display: none;
            }
            .printn-btn {
                display: none;
            }
        }

    </style>
</head>
<body>

    <div class="invoice-container">
        <div class="invoice-details">
            <br>
            <div class="invoice-header">Invoice #<?php echo $invoice['invoice_id']; ?></div>
            
            <p><strong>Customer Name:</strong> <?php echo $invoice['customer_name']; ?></p>
            <p><strong>Contact Info:</strong> <?php echo $invoice['contact_info']; ?></p>
            <p><strong>Invoice Date:</strong> <?php echo $invoice['invoice_date']; ?></p>
            <p><strong>Status:</strong> <?php echo ucfirst($invoice['status']); ?></p>

            <h3>Purchased Items</h3>
            <table>
                <tr>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>GST (%)</th>
                    <th>Total Price</th>
                </tr>
                <?php foreach ($items as $item) { ?>
                <tr>
                    <td><?php echo $item['item_name']; ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo number_format($item['unit_price'], 2); ?></td>
                    <td><?php echo $item['gst']; ?>%</td>
                    <td><?php echo number_format($item['total_price'], 2); ?></td>
                </tr>
                <?php } ?>
            </table>

            <div class="total-amount">Total Amount: ₹<?php echo number_format($invoice['total_amount'], 2); ?></div>
            <p>
                Thank you for your business! If you have any questions, please contact us.
            </p>
            <button class="print-btn" onclick="window.print()">Print Invoice</button>
            <button class="printn-btn" onclick="window.location.href='manage_invoice.php'">Back to Invoices</button>
        </div>
    </div>

</body>
</html>
