<?php
include 'db_config.php';

if (!isset($_GET['receipt_id'])) {
    die("Receipt ID missing.");
}

$receipt_id = $_GET['receipt_id'];

// Fetch receipt details
$sql = "SELECT r.*, i.invoice_id, i.total_amount AS invoice_total, c.customer_name, c.contact_info 
        FROM receipts r
        LEFT JOIN invoices i ON r.invoice_id = i.invoice_id
        LEFT JOIN customers c ON i.customer_id = c.customer_id
        WHERE r.receipt_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $receipt_id);
$stmt->execute();
$result = $stmt->get_result();
$receipt = $result->fetch_assoc();
$stmt->close();

$total_amount = 0;
$items = [];

if ($receipt['invoice_id']) {
    // Take total amount from the invoice itself
    $total_amount = $receipt['invoice_total'];

    // Fetch items for invoice-based receipts
    $sql = "SELECT item_name, quantity, unit_price, gst, total_price FROM invoice_items WHERE invoice_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $receipt['invoice_id']);
} else {
    // Fetch items for manually created receipts
    $sql = "SELECT item_name, quantity, unit_price, gst, total_price FROM receipt_items WHERE receipt_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $receipt_id);
}

$stmt->execute();
$items_result = $stmt->get_result();
while ($row = $items_result->fetch_assoc()) {
    $items[] = $row;

    // If it's a manual receipt, calculate the total dynamically
    if (!$receipt['invoice_id']) {
        $total_amount += $row['total_price'];
    }
}
$stmt->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?php echo $receipt_id; ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100vh;
            background: url('bg.png') no-repeat center center;
            background-size: cover;
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .receipt-container {
            position: absolute;
            top: 25%;  /* Moved slightly down */
            left: 8%;   /* Moved slightly left */
            right: 10%;
            width: 75%; /* Adjusted width */
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.3);
        }

        h2 {
            margin-bottom: 10px;
            color: #333;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }

        .info {
            text-align: left;
            margin-bottom: 20px;
            font-size: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: rgba(255, 255, 255, 0.9);
        }

        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ccc;
        }

        th {
            background:rgb(0, 0, 0);
            color: white;
        }

        .total {
            font-weight: bold;
        }

        .print-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background: green;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

     .printn-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background: #7F8CAA;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }


        .print-btn:hover {
            background: darkgreen;
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <h2>Receipt</h2>
    <div class="info">
        <p><strong>Receipt ID:</strong> <?php echo $receipt_id; ?></p>
        <p><strong>Date:</strong> <?php echo date("d-M-Y", strtotime($receipt['receipt_date'])); ?></p>
        
        <!-- Show customer details only if it's not a manual receipt -->
        <?php if ($receipt['invoice_id']) { ?>
            <p><strong>Customer:</strong> <?php echo $receipt['customer_name']; ?> (<?php echo $receipt['contact_info']; ?>)</p>
        <?php } ?>
        
        <p><strong>Payment Method:</strong> <?php echo $receipt['payment_method']; ?></p>
    </div>

    <?php if (!empty($items)) { ?>
    <table>
        <tr>
            <th>Item</th>
            <th>Qty</th>
            <th>Price</th>
            <th>GST</th>
            <th>Total</th>
        </tr>
        <?php foreach ($items as $item) { ?>
            <tr>
                <td><?php echo $item['item_name']; ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>₹<?php echo number_format($item['unit_price'], 2); ?></td>
                <td><?php echo $item['gst']; ?>%</td>
                <td>₹<?php echo number_format($item['total_price'], 2); ?></td>
            </tr>
        <?php } ?>
        <tr>
            <td colspan="4" class="total">Total Amount</td>
            <td class="total">₹<?php echo number_format($total_amount, 2); ?></td>
        </tr>
    </table>
    <?php } else { ?>
        <p><strong>No items found for this receipt.</strong></p>
    <?php } ?>

   
   
    <p style="margin-top: 20px; font-size: 14px; color: #666;">
        Thank you for your business!
    </p>
    
    <button class="printn-btn" onclick="window.location.href='manage_receipts.php';" style="display:inline-block;" id="backBtn">Back to Receipts</button>
    <style>
        @media print {
            #backBtn {
                display: none !important;
            }
        }
    </style>
    <br>
    <button class="print-btn" onclick="window.print();" id="printBtn">Print Receipt</button>
    <style>
        @media print {
            #printBtn {
                display: none !important;
            }
        }
    </style>
</div>



</body>
</html>

