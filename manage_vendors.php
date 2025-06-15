<?php
session_start();
include("db_config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}


// Fetch vendors from the database
$vendor_query = "SELECT * FROM vendors";
$vendor_result = $conn->query($vendor_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Vendors</title>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Lora', serif; }
        body { display: flex; height: 100vh; background: #f4f4f4; }
        .sidebar { width: 250px; background: #7b1fa2; color: white; height: 100vh; position: fixed; padding-top: 20px; }
        .sidebar h2 { text-align: center; margin-bottom: 20px; }
        .sidebar a { display: flex; align-items: center; padding: 12px 20px; color: white; text-decoration: none; font-size: 16px; }
        .sidebar a i { margin-right: 12px; }
        .sidebar a:hover { background: #5e1386; }
        .main-content { margin-left: 250px; padding: 20px; width: calc(100% - 250px); }
        .header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px; border-radius: 8px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); }
        .header h1 { color: #7b1fa2; }
        .logout { background: red; color: white; padding: 8px 12px; border: 2px solid darkred; border-radius: 6px; cursor: pointer; }
        .table-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #7b1fa2; color: white; }
        tr:hover { background: #f1e4f7; }
        .add-vendor-btn { background: #7b1fa2; color: white; padding: 10px 15px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .edit-btn, .delete-btn { padding: 8px 12px; border: none; cursor: pointer; border-radius: 6px; font-weight: bold; }
        .edit-btn { background: #ffc107; color: #333; }
        .delete-btn { background: red; color: white; }
        .modal { display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2); width: 400px; }
        .modal input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 6px; }
        .modal button { width: 100%; padding: 10px; background: #7b1fa2; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .modal button:hover { background: #5e1386; }
        .close-modal { text-align: right; font-size: 18px; cursor: pointer; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Perfect Plate</h2>
    <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
    <div class="header">
        <h1>Manage Vendors</h1>
        <button class="add-vendor-btn" onclick="openAddVendorModal()">+ Add Vendor</button>
    </div>

    <div class="table-container">
        <table id="vendorTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Vendor Name</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>GST Number</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $vendor_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['vendor_id']; ?></td>
                        <td><?php echo $row['vendor_name']; ?></td>
                        <td><?php echo $row['contact_info']; ?></td>
                        <td><?php echo $row['address']; ?></td>
                        <td><?php echo $row['gst_number']; ?></td>
                        <td>
                            <button class="edit-btn" onclick="editVendor(<?php echo $row['vendor_id']; ?>)">Edit</button>
                            <button class="delete-btn" onclick="deleteVendor(<?php echo $row['vendor_id']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Vendor Modal -->
<div class="modal" id="vendorModal">
    <div class="close-modal" onclick="closeModal()">✖</div>
    <h2 id='modalTitle'>Add Vendor</h2>
    <input type="hidden" id="vendor_id">
    <input type="text" id="vendor_name" placeholder="Vendor Name">
    <input type="text" id="contact_info" placeholder="Contact Info">
    <input type="text" id="address" placeholder="Address">
    <input type="text" id="gst_number" placeholder="GST Number">
    <button onclick="saveVendor()">Save Vendor</button>
</div>

<script>
    $(document).ready(function() {
    $('#vendorTable').DataTable();
});

// Open Add Vendor Modal
function openAddVendorModal() {
    $('#modalTitle').text('Add Vendor'); // Set title for Add
    $('#vendor_id').val('');
    $('#vendor_name').val('');
    $('#contact_info').val('');
    $('#address').val('');
    $('#gst_number').val('');
    $('#vendorModal').show();
}

// Close Modal
function closeModal() {
    $('#vendorModal').hide();
}

// Open Edit Vendor Modal
function editVendor(id) {
    $.ajax({
        url: 'get_vendor.php',
        type: 'POST',
        data: { vendor_id: id },
        dataType: 'json',
        success: function(response) {
            $('#modalTitle').text('Edit Vendor'); // Set title for Edit
            $('#vendor_id').val(response.vendor_id);
            $('#vendor_name').val(response.vendor_name);
            $('#contact_info').val(response.contact_info);
            $('#address').val(response.address);
            $('#gst_number').val(response.gst_number);
            $('#vendorModal').show();
        }
    });
}

// Save Vendor (Add or Update)
function saveVendor() {
    var vendorData = {
        vendor_id: $('#vendor_id').val(),
        vendor_name: $('#vendor_name').val(),
        contact_info: $('#contact_info').val(),
        address: $('#address').val(),
        gst_number: $('#gst_number').val()
    };

    $.ajax({
        url: 'save_vendor.php',
        type: 'POST',
        data: vendorData,
        success: function(response) {
            alert(response);
            location.reload(); // Refresh page after update
        }
    });
}

// Delete Vendor
function deleteVendor(id) {
    if (confirm("Are you sure you want to delete this vendor?")) {
        $.ajax({
            url: 'delete_vendor.php',
            type: 'POST',
            data: { vendor_id: id },
            success: function(response) {
                alert(response);
                location.reload(); // Refresh page after delete
            }
        });
    }
}

</script>


</body>
</html>
