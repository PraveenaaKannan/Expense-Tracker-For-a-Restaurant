<?php
include 'db_config.php'; // Database connection file

// Handle item creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_item'])) {
    $item_name = $_POST['item_name'];
    $price = $_POST['price'];
    $gst = $_POST['gst'];

    // Prevent duplicate item names
    $checkItem = $conn->prepare("SELECT item_id FROM items WHERE item_name = ?");
    $checkItem->bind_param("s", $item_name);
    $checkItem->execute();
    $checkItem->store_result();

    if ($checkItem->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO items (item_name, price, gst) VALUES (?, ?, ?)");
        $stmt->bind_param("sdd", $item_name, $price, $gst);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "<script>alert('Item already exists!');</script>";
    }
    $checkItem->close();
}

// Handle item deletion
if (isset($_GET['delete'])) {
    $item_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM items WHERE item_id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_items.php");
    exit();
}

// Handle item update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_item'])) {
    $item_id = $_POST['item_id'];
    $item_name = $_POST['item_name'];
    $price = $_POST['price'];
    $gst = $_POST['gst'];

    $stmt = $conn->prepare("UPDATE items SET item_name = ?, price = ?, gst = ? WHERE item_id = ?");
    $stmt->bind_param("sddi", $item_name, $price, $gst, $item_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_items.php");
    exit();
}

// Fetch all items
$result = $conn->query("SELECT * FROM items");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Items</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
    .btn-primary {
            background-color: #6a0dad;
            border: none;
        }
        .form-control {
            border-radius: 8px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .container {
            max-width: 500px;
            margin-top: 50px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
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
        }
        h1 {
            font-size: 22px;
            color: #7b1fa2;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background-color:rgb(123, 31, 162); color: white; }
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
<body><div class="sidebar">
    <img src="logo.png" alt="Restaurant Logo">
    <h2>Perfect Plate</h2>
    <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i>  Dashboard</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i>  Logout</a>
</div>
<div class="main-content">
    <div class="header">
        <h1>Manage Items</h1>
    </div>

    <!-- Add Item Form -->
     <div class="container">
     <h2 class="text-center mt-3"> Add New Items</h2>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error; ?></div>
        <?php endif; ?>
    <form method="post">
        <div class="form-group">
        <label>Item Name:</label>
        <input type="text" name="item_name" class="form-control" required>
</div>
        <div class="form-group">
        <label>Price:</label>
        <input type="number" step="100" name="price" class="form-control" required>
</div>
<div class="form-group">
        <label>GST (%):</label>
        <input type="number" step="0.01" name="gst"class="form-control" required>
        </div>
        <div class="d-flex justify-content-between">
        <button type="submit" class="btn btn-primary" name="add_item">Add Item</button>
        </div>
    </form>
    </div>
    <hr>

    <!-- Item List -->
    <h1>Item List</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Item Name</th>
            <th>Price</th>
            <th>GST (%)</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['item_id']; ?></td>
            <td><?php echo $row['item_name']; ?></td>
            <td><?php echo $row['price']; ?></td>
            <td><?php echo $row['gst']; ?></td>
            <td>
                <a href="manage_items.php?edit=<?php echo $row['item_id']; ?>"><button class="btn-edit">Edit</button></a> | 
                <a href="manage_items.php?delete=<?php echo $row['item_id']; ?>" onclick="return confirm('Are you sure?')"><button class="btn-delete">Delete</button></a>
            </td>
        </tr>
        <?php } ?>
    </table>
        </br>
    <?php
    // If edit is requested, show update form
    if (isset($_GET['edit'])) {
        $item_id = $_GET['edit'];
        $editStmt = $conn->prepare("SELECT * FROM items WHERE item_id = ?");
        $editStmt->bind_param("i", $item_id);
        $editStmt->execute();
        $editResult = $editStmt->get_result()->fetch_assoc();
        $editStmt->close();
    ?>
    <hr>
    <div class="container">
    <h2 class="text-center mt-3"> Edit Items</h2>
        <form method="post">
            <div class="form-group">
            <input type="hidden" name="item_id" class="form-control" value="<?php echo $editResult['item_id']; ?>">
            <label>Item Name:</label>
            <input type="text" name="item_name" class="form-control" value="<?php echo $editResult['item_name']; ?>" required>
    </div>

            <div class="form-group">
            <label>Price:</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $editResult['price']; ?>" required>
            </div>

            <div class="form-group">
            <label>GST (%):</label>
            <input type="number" step="0.01" name="gst" class="form-control" value="<?php echo $editResult['gst']; ?>" required>
            </div>

            <div class="d-flex justify-content-between">
            <button type="submit" name="update_item" class="btn btn-primary">Update Item</button>
            <a href="manage_items.php"><button type="button" class="btn btn-secondary">Cancel</button></a>
            <div class="d-flex justify-content-between">
    </div>
        </form>
    </div>
    <?php } ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</div>

</body>
</html>
