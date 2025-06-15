<?php
include("db_config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vendor_id'])) {
    $vendor_id = intval($_POST['vendor_id']);
    $delete_query = "DELETE FROM vendors WHERE vendor_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $vendor_id);

    if ($stmt->execute()) {
        echo "Vendor deleted successfully.";
    } else {
        echo "Error deleting vendor: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>