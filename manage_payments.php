<?php
session_start();
include("db_config.php");


// Fetch Users
$query = "SELECT * FROM payments";
$result = $conn->query($query);

// Delete User
if (isset($_POST['delete_payment'])) {
    $payment_id = $_POST['payment_id'];
    $delete_query = "DELETE FROM payments WHERE payment_id = '$payment_id'";
    if ($conn->query($delete_query)) {
        echo "<script>alert('Payment deleted successfully!'); window.location.href='manage_payments.php';</script>";
    } else {
        echo "<script>alert('Error deleting Payment!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments</title>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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

    <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i>  Dashboard</a>
    <a href="logout.php">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>


<!-- Main Content -->
<div class="main-content">
    <div class="header">
        <h1>Manage Payments</h1>

    </div>

    <table class="user-table">
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Advance Amount</th>
                <th>Payment Method</th>
                <th>Payment Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['payment_id']); ?></td>
                <td><?php echo htmlspecialchars($row['category']); ?></td>
                <td><?php echo htmlspecialchars($row['amount']); ?></td>
                <td><?php echo htmlspecialchars($row['amount_paid']); ?></td>
                <td><?php echo htmlspecialchars($row['payment_method']); ?></td>     
                <td><?php echo htmlspecialchars($row['payment_date']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>
                    <form method="POST" style="display:inline-block;">
                        <input type="hidden" name="payment_id" value="<?php echo htmlspecialchars($row['payment_id']); ?>">
                        <button type="submit" name="delete_payment" class="btn-delete" onclick="return confirm('Are you sure?');">Delete</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>
