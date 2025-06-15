<?php
include 'db_config.php';

// Fetch only PAID vendor invoices
$sql = "SELECT vi.vendor_invoice_id, v.vendor_id, v.vendor_name, vi.total_amount 
        FROM vendor_invoices vi 
        JOIN vendors v ON vi.vendor_id = v.vendor_id 
        WHERE vi.status = 'Paid'";
$result = $conn->query($sql);

// Fetch all vendors
$vendors = $conn->query("SELECT vendor_id, vendor_name FROM vendors");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Vendor Receipt</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            max-width: 1000px;
            margin-top: 50px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #6a0dad;
            border: none;
        }
        .btn-primary:hover {
            background-color: #520d8a;
        }
        .form-control {
            border-radius: 8px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .restaurant-logo {
            display: block;
            margin: 0 auto;
            width: 100px;
        }
        .restaurant-name {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            font-family: 'Lora', serif;
            color: #6a0dad;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="logo.png" alt="Restaurant Logo" class="restaurant-logo">
        <div class="restaurant-name">Perfect Plate</div>
        <h2 class="text-center mt-3"><i class="fas fa-user-plus"></i> Enter Vendor Receipts</h2>
    
    <form action="process_vendor_receipt.php" method="POST">
    <div class="form-group">
        <label>
            <input type="radio" name="payment_type" value="invoice"  id="select_invoice" required>
            Invoice Payment
        </label>
    </div>
    <div class="form-group">
        <label>
            <input type="radio" name="payment_type" value="direct"  id="select_direct">
            Direct Payment
        </label>
        </div>
        <div class="form-group"> 
        <div id="invoice_section" style="display: none;">
            <label for="vendor_invoice_id">Select Paid Invoice:</label>
            <select name="vendor_invoice_id" id="vendor_invoice_id" class="form-control">
                <option value="">-- Select Paid Invoice --</option>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <option value="<?= $row['vendor_invoice_id']; ?>" data-vendor="<?= $row['vendor_id']; ?>">
                        <?= $row['vendor_name'] . " - ₹" . $row['total_amount']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
     </div>

        <div id="direct_section" style="display: none;">
            <div class="form-group">
            <label for="vendor_name">Vendor Name:</label>
            <input type="text" name="vendor_name" id="vendor_name" class="form-control">
        </div>
        <div class="form-group">
            <label for="vendor_contact">Vendor Contact:</label>
            <input type="text" name="vendor_contact" id="vendor_contact" class="form-control">
            </div>
        </div>
        <div class="form-group">
        <label for="receipt_amount">Receipt Amount (₹):</label>
        <input type="number" step="0.01" name="receipt_amount" class="form-control" required>
        </div>
        <div class="form-group">
        <label for="payment_method">Payment Method:</label>
        <select name="payment_method"  class="form-control" required>
            <option value="Cash">Cash</option>
            <option value="Bank Transfer">Bank Transfer</option>
            <option value="UPI">UPI</option>
            <option value="Card">Card</option>
        </select>
        </div>
        <div class="form-group">
        <label>Category:</label>
        <input type="text" name="category" placeholder="e.g., Food, Utilities, Rent" class="form-control" required>
        </div>
        <div class="form-group">
        <label for="receipt_date">Receipt Date:</label>
        <input type="date" name="receipt_date" class="form-control" required>
                </div>
        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Submit Receipt</button>
            <a href="manage_vendor_receipts.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Vendor Receipts</a>
        </div>
    </form>



    <script>
        document.getElementById("select_invoice").addEventListener("change", function() {
            document.getElementById("invoice_section").style.display = "block";
            document.getElementById("direct_section").style.display = "none";
            document.getElementById("vendor_name").value = "";
            document.getElementById("vendor_contact").value = "";
        });

        document.getElementById("select_direct").addEventListener("change", function() {
            document.getElementById("invoice_section").style.display = "none";
            document.getElementById("direct_section").style.display = "block";
            document.getElementById("vendor_invoice_id").value = "";
        });
    </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
