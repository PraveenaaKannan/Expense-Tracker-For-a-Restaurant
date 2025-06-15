<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Vendor Invoice</title>
     <!-- Bootstrap CSS -->
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Custom Styles -->
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
    <!-- Restaurant Logo & Name -->
    <img src="logo.png" alt="Restaurant Logo" class="restaurant-logo">
    <div class="restaurant-name">Perfect Plate</div>

    <h2 class="text-center mt-3"><i class="fas fa-user-plus"></i> Vendor Invoice Entry</h2>
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success; ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>
    <br>

    <form action="process_vendor_invoice.php" method="POST">
        <div class="form-group">
        <h3>Vendor Details</h3>
        </div>

        <div class="form-group">
        <label>Vendor Name:</label>
        <input type="text" name="vendor_name" class="form-control" required>
        </div>

        <div class="form-group">
        <label>Contact Info:</label>
        <input type="text" name="vendor_contact"  class="form-control"required>
        </div>

        <div class="form-group">
        <label>Address:</label>
        <input type="text" name="vendor_address" class="form-control" required>
        </div>
        
        <div class="form-group">
        <label>GST Number:</label>
        <input type="text" name="gst_number" class="form-control">
        </div>

        <div class="form-group">
        <h3>Invoice Details</h3>
        <label>Invoice Date:</label>
        <input type="date" name="invoice_date" class="form-control" required>
        </div>

        <div class="form-group">
        <label>Total Amount:</label>
        <input type="number" step="0.01" name="total_amount" class="form-control" required>
        </div>

        <div class="form-group">
        <label>Payment Status:</label>
        <select name="payment_status" class="form-control">
            <option value="Paid">Paid</option>
            <option value="Unpaid">Unpaid</option>
        </select>
        </div>

        <div class="form-group">
        <label>Payment Due Date (if unpaid):</label>
        <input type="date" name="payment_due_date" class="form-control">
        </div>

        <div class="form-group">
        <label>Category:</label>
        <input type="text" name="category" placeholder="e.g., Food, Utilities, Rent" class="form-control" required>
        </div>

        <div class="d-flex justify-content-between">
        <a href="manage_vendor_invoices.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Manage Vendor Invoices
            </a>
            
        <button type="submit" class="btn btn-primary">Submit Invoice</button>
        </div>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
