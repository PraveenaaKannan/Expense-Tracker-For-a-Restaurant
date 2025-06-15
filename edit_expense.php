<?php
session_start();
include 'db_config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access!");
}

// Get user ID from URL
if (!isset($_GET['expense_id'])) {
    die("Invalid Expense ID.");
}

$expense_id = $_GET['expense_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT category, amount, expense_date  FROM expenses WHERE expense_id = ?");
$stmt->bind_param("i", $expense_id);
$stmt->execute();
$result = $stmt->get_result();
$expenses = $result->fetch_assoc();

if (!$expenses) {
    die("Expense not found.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = trim($_POST['category']);
    $amount = trim($_POST['amount']);
    $expense_date = trim($_POST['expense_date']);

    // Update user details
    $update_stmt = $conn->prepare("UPDATE expenses SET category = ?, amount = ?, expense_date=?  WHERE expense_id = ?");
    $update_stmt->bind_param("sdsi", $category, $amount, $expense_date,  $expense_id);

    if ($update_stmt->execute()) {
        echo "<script>alert('Expense updated successfully!'); window.location='manage_expenses.php';</script>";
    } else {
        echo "<script>alert('Error updating epoense.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Expense</title>
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

    <h2 class="text-center mt-3">Edit Expense</h2>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success; ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

        <form method="POST">
        <div class="form-group"> 
        <label>Category:</label>
    <select name="category" class="form-control"  required>
        <option value="Rent" <?php echo ($expenses['category'] == 'Rent') ? 'selected' : ''; ?>>Rent</option>
        <option value="Utilities"  <?php echo ($expenses['category'] == 'Utilities') ? 'selected' : ''; ?>>Utilities</option>
        <option value="Staff Salary"  <?php echo ($expenses['category'] == 'Staff Salary') ? 'selected' : ''; ?>>Staff Salary</option>
        <option value="Maintenance"  <?php echo ($expenses['category'] == 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
        <option value="Inventory Purchase" <?php echo ($expenses['category'] == 'Inventory Purchase') ? 'selected' : ''; ?>>Inventory Purchase</option>
        <option value="Other" <?php echo ($expenses['category'] == 'Other') ? 'selected' : ''; ?>>Other</option>
    </select>
</div>
<div class="form-group">
    <label>Amount:</label>
            <input type="text" name="amount" value="<?php echo htmlspecialchars($expenses['amount']); ?>" class="form-control" required>
            </div>

            <div class="form-group">

<label>Expense Date:</label>
            <input type="text" name="expense_date" value="<?php echo htmlspecialchars($expenses['expense_date']); ?>" class="form-control" required>
            </div>
            <br>
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="manage_expenses.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
