<?php
include("db_config.php");

if (isset($_POST['vendor_name'], $_POST['contact_info'], $_POST['address'], $_POST['gst_number'])) {
    $vendor_id = $_POST['vendor_id'];
    $vendor_name = $_POST['vendor_name'];
    $contact_info = $_POST['contact_info'];
    $address = $_POST['address'];
    $gst_number = $_POST['gst_number'];

    if ($vendor_id) {
        // Update vendor
        $query = "UPDATE vendors SET vendor_name=?, contact_info=?, address=?, gst_number=? WHERE vendor_id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $vendor_name, $contact_info, $address, $gst_number, $vendor_id);
        $stmt->execute();
        echo "Vendor updated successfully!";
    } else {
        // Add new vendor
        $query = "INSERT INTO vendors (vendor_name, contact_info, address, gst_number) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $vendor_name, $contact_info, $address, $gst_number);
        $stmt->execute();
        echo "Vendor added successfully!";
    }
}
?>
