<?php
include 'db_config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Invoice</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Custom Styles -->
    <style>
        body {
            padding : 150px;
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            max-width: 2000px;
            margin-top: 30px;
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
        table { width: 50%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background-color:rgb(123, 31, 162); color: white; }
        .print-btn { padding: 5px 10px; background: green; color: white; border: none; cursor: pointer; border-radius: 5px; }
        .print { padding: 5px 10px; background: red; color: white; border: none; cursor: pointer; border-radius: 5px; }
    </style>
</head>
<body>
   
    <div class="container">
<img src="logo.png" alt="Restaurant Logo" class="restaurant-logo">
    <div class="restaurant-name">Perfect Plate</div>
<center>
    <h2 class="text-center mt-3"> Create Invoice</h2>
    </center>
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success; ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>
<br>

    <form action="process_invoice.php" method="POST">
    <div class="form-group">
        <h3>Customer Information</h3>
    </div>

    <div class="form-group">
        <label>Name:</label>
        <input type="text" name="customer_name" class="form-control" required>
    </div>
        
    <div class="form-group">
        <label>Contact:</label>
        <input type="text" name="customer_contact" class="form-control" required>
    </div>

    <div class="form-group">
        <h3>Items Purchased</h3>
        <table id="items_table">
            <tr>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>GST</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
            <tr>
                <td>
                    <select name="item_name[]" class="item_dropdown" required>
                        <option value="">Select Item</option>
                        <?php
                        $sql = "SELECT item_name FROM items";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['item_name'] . "'>" . $row['item_name'] . "</option>";
                        }
                        ?>
                    </select>
                </td>
                <td><input type="number" name="quantity[]" class="quantity" min="1" required></td>
                <td><input type="text" name="price[]" class="price" readonly></td>
                <td><input type="text" name="gst[]" class="gst" readonly></td>
                <td><input type="text" name="total[]" class="total" readonly></td>
                <td><button type="button" onclick="removeRow(this)" class="print">Remove</button></td>
            </tr>
        </table>
     </div>

     <div class="form-group">
        <button type="button" onclick="addRow()" class="print-btn">Add Item</button>
    </div>

    <div class="form-group">
        <h3>Total Amount</h3>
    </div>

    <div class="form-group">
<label>Total:</label>
<input type="text" id="total_amount" name="total_amount" class="form-control" readonly>
                    </div>
                    <div class="form-group">
        <h3>Payment Details</h3>
                    </div>
                    <div class="form-group">
        <label>Payment Method:</label>
        <select name="payment_method" class="form-control" required>
            <option value="Cash">Cash</option>
            <option value="Card">Card</option>
            <option value="UPI">UPI</option>
            <option value="Bank Transfer">Bank Transfer</option>
        </select>
                    </div>

                    <div class="form-group">
        <label>Payment Status:</label>
        <select name="payment_status" id="payment_status" class="form-control" required>
            <option value="Paid">Paid</option>
            <option value="Unpaid">Unpaid</option>
        </select>
                    </div>
        <div id="payment_due_date_section" style="display: none;">
        <div class="form-group">
    <label>Payment Due Date:</label>
    <input type="date" name="payment_due_date"  class="form-control">
                    </div>
</div>

        <div id="advance_payment" style="display: none;">
        <div class="form-group">
            <label>Advance Paid:</label>
            <input type="number" name="advance_amount" step="0.01"  class="form-control">
                    </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="manage_invoice.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Manage Invoices
            </a>
        <input type="submit" value="Generate Invoice" class="btn btn-primary">
        </div>
    </form>
                    
                    </div>
    <script>
    $(document).on("change", ".item_dropdown", function () {
        var item_name = $(this).val();
        var row = $(this).closest("tr");
        $.ajax({
            url: "fetch_item_details.php",
            type: "POST",
            data: { item_name: item_name },
            dataType: "json",
            success: function (data) {
                row.find(".price").val(data.price);
                row.find(".gst").val(data.gst);
                updateTotal(row);
                updateGrandTotal(); // Update total amount when item changes
            }
        });
    });

    $(document).on("input", ".quantity", function () {
        var row = $(this).closest("tr");
        updateTotal(row);
        updateGrandTotal(); // Update total amount when quantity changes
    });

    function updateTotal(row) {
        var price = parseFloat(row.find(".price").val()) || 0;
        var gst = parseFloat(row.find(".gst").val()) || 0;
        var quantity = parseInt(row.find(".quantity").val()) || 1;
        var total = quantity * price + (quantity * price * gst / 100);
        row.find(".total").val(total.toFixed(2));
    }

    function updateGrandTotal() {
        var grandTotal = 0;
        $(".total").each(function () {
            grandTotal += parseFloat($(this).val()) || 0;
        });
        $("#total_amount").val(grandTotal.toFixed(2));
    }

    function addRow() {
        var newRow = $("#items_table tr:last").clone();
        $("#items_table").append(newRow);
        updateGrandTotal(); // Update when row is added
    }

    function removeRow(button) {
        $(button).closest("tr").remove();
        updateGrandTotal(); // Update when row is removed
    }

    $("#payment_status").change(function () {
        if ($(this).val() === "Unpaid") {
            $("#advance_payment").show();
            $("#payment_due_date_section").show(); // Show due date field
        } else {
            $("#advance_payment").hide();
            $("#payment_due_date_section").hide(); // Hide due date field
        }
    });
</script>

</body>
</html>
