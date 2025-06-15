<?php
include 'db_config.php';

if (isset($_POST['item_name'])) {
    $item_name = $_POST['item_name'];
    $sql = "SELECT price, gst FROM items WHERE item_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $item_name);
    $stmt->execute();
    $stmt->bind_result($price, $gst);
    $stmt->fetch();
    $stmt->close();

    echo json_encode(["price" => $price, "gst" => $gst]);
}
?>
