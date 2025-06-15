<?php
// Include database connection
include('db_config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, role, password) VALUES ('$name', '$role', '$password')";
    if (mysqli_query($conn, $sql)) {
        $success = "User added successfully!";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Custom Styles -->
    <style>
        body {
            padding: 175px;
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

    <h2 class="text-center mt-3"><i class="fas fa-user-plus"></i> Add New User</h2>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success; ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label><i class="fas fa-user"></i> Full Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="form-group">
            <label><i class="fas fa-user-tag"></i> Role</label>
            <select name="role" class="form-control" required>
                <option value="admin">Admin</option>
                <option value="manager">Manager</option>
            </select>
        </div>

        <div class="form-group">
            <label><i class="fas fa-lock"></i> Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="d-flex justify-content-between">
            <a href="manage_users.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Manage Users
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save User
            </button>
        </div>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
