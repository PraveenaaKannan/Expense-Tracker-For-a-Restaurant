<?php
include 'db_config.php';

// Fetch available items from the database
$item_query = "SELECT item_name, price, gst FROM items";
$item_result = $conn->query($item_query);
$items = [];
while ($row = $item_result->fetch_assoc()) {
    $items[$row['item_name']] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Receipt</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            max-width: 1300px;
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
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
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

    <h2 class="text-center mt-3"> Create Receipt (Dine-In / Takeaway)</h2>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success; ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>
<br>

<form action="process_receipt.php" method="POST">
<div class="form-group">
    <label>Customer Name (Optional for Takeaway):</label>
    <input type="text" name="customer_name" class="form-control">
    </div>

    <div class="form-group">
    <label>Contact (Optional):</label>
    <input type="text" name="customer_contact" class="form-control">
    </div>
    
    <div class="form-group">
    <label>Order Type:</label>
    <select name="order_type" class="form-control">
        <option value="Dine-In">Dine-In</option>
        <option value="Takeaway">Takeaway</option>
    </select>
    </div>

    <div class="form-group">
    <h3>Purchased Items:</h3>
    <table id="items_table">
        <tr>
            <th>Item</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>GST</th>
            <th>Total</th>
            <th>Action</th>
        </tr>
        <tr>
            <td>
                <select name="item_name[]" class="item_select">
                    <option value="">Select Item</option>
                    <?php foreach ($items as $item_name => $item) { ?>
                        <option value="<?php echo $item_name; ?>" data-price="<?php echo $item['price']; ?>" data-gst="<?php echo $item['gst']; ?>">
                            <?php echo $item_name; ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
            <td><input type="number" name="quantity[]" class="quantity" min="1" value="1"></td>
            <td><input type="text" name="price[]" class="price" readonly></td>
            <td><input type="text" name="gst[]" class="gst" readonly></td>
            <td><input type="text" name="total[]" class="total" readonly></td>
            <td><button type="button" class="print">Remove</button></td>
        </tr>
    </table>
   </div>
   <div class="form-group">
    <button type="button" id="add_item" class="print-btn">Add Item</button>
    </div>

    <div class="form-group">
    <h3>Total Amount: <span id="total_amount">0.00</span></h3>
    </div>

    <div class="form-group">
    <label>Payment Method:</label>
    <select name="payment_method" class="form-control">
        <option value="Cash">Cash</option>
        <option value="Card">Card</option>
        <option value="UPI">UPI</option>
    </select>
    </div>

    <div class="d-flex justify-content-between">
            <a href="manage_receipts.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Manage Receipts
            </a>
    <input type="submit" value="Generate Receipt" class="btn btn-primary">
    </div>
</form>
</div>
                 
<script>
$(document).ready(function() {
    function calculateTotal() {
        let totalAmount = 0;
        $("tr").each(function() {
            let total = parseFloat($(this).find(".total").val()) || 0;
            totalAmount += total;
        });
        $("#total_amount").text(totalAmount.toFixed(2));
    }

    $(document).on("change", ".item_select", function() {
        let price = parseFloat($(this).find(":selected").data("price")) || 0;
        let gst = parseFloat($(this).find(":selected").data("gst")) || 0;
        let quantity = parseInt($(this).closest("tr").find(".quantity").val()) || 1;
        let total = quantity * (price + (price * gst / 100));

        $(this).closest("tr").find(".price").val(price.toFixed(2));
        $(this).closest("tr").find(".gst").val(gst.toFixed(2));
        $(this).closest("tr").find(".total").val(total.toFixed(2));

        calculateTotal();
    });

    $(document).on("input", ".quantity", function() {
        $(this).closest("tr").find(".item_select").trigger("change");
    });

    $("#add_item").click(function() {
        let row = `<tr>
            <td>
                <select name="item_name[]" class="item_select">
                    <option value="">Select Item</option>
                    <?php foreach ($items as $item_name => $item) { ?>
                        <option value="<?php echo $item_name; ?>" data-price="<?php echo $item['price']; ?>" data-gst="<?php echo $item['gst']; ?>">
                            <?php echo $item_name; ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
            <td><input type="number" name="quantity[]" class="quantity" min="1" value="1"></td>
            <td><input type="text" name="price[]" class="price" readonly></td>
            <td><input type="text" name="gst[]" class="gst" readonly></td>
            <td><input type="text" name="total[]" class="total" readonly></td>
            <td><button type="button" class="remove_item">Remove</button></td>
        </tr>`;
        $("#items_table").append(row);
    });

    $(document).on("click", ".remove_item", function() {
        $(this).closest("tr").remove();
        calculateTotal();
    });
});
</script>

</body>
</html>
