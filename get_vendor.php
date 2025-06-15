<?php
include("db_config.php");

if (isset($_POST['vendor_id'])) {
    $vendor_id = $_POST['vendor_id'];
    $query = "SELECT * FROM vendors WHERE vendor_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $vendor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $vendor = $result->fetch_assoc();
    
    echo json_encode($vendor);
}
?>
