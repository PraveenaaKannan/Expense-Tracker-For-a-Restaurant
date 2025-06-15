<?php
include 'db_config.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $expense_date = date("Y-m-d H:i:s"); // Current timestamp

    // Insert into expenses table and get the expense_id
    $query = "INSERT INTO expenses (category, amount, expense_date) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sds", $category, $amount, $expense_date);
    $stmt->execute();
    $expense_id = $stmt->insert_id; // Get inserted expense ID
    $stmt->close();

    // Also store in payments table with expense_id
    $payment_query = "INSERT INTO payments (expense_id, category, amount, payment_date, payment_method, status) 
                      VALUES (?, 'expense', ?, ?, ?, 'Completed')";
    $stmt = $conn->prepare($payment_query);
    $stmt->bind_param("idss", $expense_id, $amount, $expense_date, $payment_method);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Expense Added Successfully'); window.location.href='add_expense.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Expense</title>
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
            max-width: 500px;
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

    <h2 class="text-center mt-3"><i class="fas fa-user-plus"></i> Add Restaurant Expense</h2>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success; ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

<form method="POST">
<div class="form-group">
    <label>Expense Category:</label>
    <select name="category" class="form-control" required>
        <option value="Rent">Rent</option>
        <option value="Utilities">Utilities</option>
        <option value="Staff Salary">Staff Salary</option>
        <option value="Maintenance">Maintenance</option>
        <option value="Inventory Purchase">Inventory Purchase</option>
        <option value="Other">Other</option>
    </select>
</div>

<div class="form-group">
    <label>Amount:</label>
    <input type="number" name="amount" class="form-control" required>
    </div>

    <div class="form-group">
    <label>Payment Method:</label>
    <select name="payment_method" class="form-control" required>
        <option value="Cash">Cash</option>
        <option value="Card">Card</option>
        <option value="Bank Transfer">Bank Transfer</option>
        <option value="Other">Other</option>
    </select>
</div>

<div class="d-flex justify-content-between">
            <a href="manage_expenses.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Manage expenses
            </a>
    <button type="submit"class="btn btn-primary">
    <i class="fas fa-save"></i> Add Expense</button>
    </div>
</form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
