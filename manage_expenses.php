<?php

session_start();
include 'db_config.php';
$dashboard_link = ($_SESSION['role'] === 'admin') ? 'admin_dashboard.php' : 'manager_dashboard.php';
// Fetch Users
// Prevent caching

$query = "SELECT * FROM expenses";
$result = $conn->query($query);

// Delete User
if (isset($_POST['delete_expense'])) {
    $expense_id = $_POST['expense_id'];
    $delete_query = "DELETE FROM expenses WHERE expense_id = '$expense_id'";
    if ($conn->query($delete_query)) {
        echo "<script>alert('Expense deleted successfully!'); window.location.href='manage_expenses.php';</script>";
    } else {
        echo "<script>alert('Error deleting Expense!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Expenses</title>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <!-- FontAwesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

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
        .btn-add {
            background: #2ecc71;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            transition: 0.3s;
        }
        .btn-add:hover {
            background: #27ae60;
        }
        .user-table {
            margin-top: 20px;
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .user-table th, .user-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .user-table th {
            background: #7b1fa2;
            color: white;
        }
        .user-table tr:hover {
            background: #f1f1f1;
        }
        .btn-edit, .btn-delete {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-edit {
            background: #3498db;
            color: white;
        }
        .btn-edit:hover {
            background: #217dbb;
        }
        .btn-delete {
            background: #e74c3c;
            color: white;
        }
        .btn-delete:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<!-- Sidebar -->
<div class="sidebar">
    <img src="logo.png" alt="Restaurant Logo">
    <h2>Perfect Plate</h2>

    <a href="<?= $dashboard_link ?>"><i class="fas fa-tachometer-alt"></i>  Dashboard</a>
    <a href="logout.php">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>


<!-- Main Content -->
<div class="main-content">
    <div class="header">
        <h1>Manage Expenses</h1>
        <a href="add_expense.php" class="btn-add"><i class="fas fa-user-plus"></i> Add Expense</a>
    </div>

    <table class="user-table">
        <thead>
            <tr>
                <th>Expense ID</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Expense Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['expense_id']); ?></td>
                <td><?php echo htmlspecialchars($row['category']); ?></td>
                <td><?php echo htmlspecialchars($row['amount']); ?></td>
                <td><?php echo htmlspecialchars($row['expense_date']); ?></td>
                <td>
                    <a href="edit_expense.php?expense_id=<?php echo htmlspecialchars($row['expense_id']); ?>" class="btn-edit">Edit</a>
                    <form method="POST" style="display:inline-block;">
                        <input type="hidden" name="expense_id" value="<?php echo htmlspecialchars($row['expense_id']); ?>">
                        <button type="submit" name="delete_expense" class="btn-delete" onclick="return confirm('Are you sure?');">Delete</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>
