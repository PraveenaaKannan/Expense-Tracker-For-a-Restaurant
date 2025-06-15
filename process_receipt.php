<?php
include 'db_config.php';

$customer_name = trim($_POST['customer_name']) ?: "Walk-in Customer";
$customer_contact = trim($_POST['customer_contact']) ?: "N/A";
$order_type = $_POST['order_type'];
$payment_method = $_POST['payment_method'];
$total_amount = 0;

// 🛠️ Check if customer exists in `customers` table
$sql = "SELECT customer_id FROM customers WHERE customer_name = ? AND contact_info = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $customer_name, $customer_contact);
$stmt->execute();
$result = $stmt->get_result();
$customer_id = $result->fetch_assoc()['customer_id'] ?? null;
$stmt->close();

// 🔹 If customer doesn't exist, insert into `customers`
if (!$customer_id) {
    $sql = "INSERT INTO customers (customer_name, contact_info) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $customer_name, $customer_contact);
    $stmt->execute();
    $customer_id = $stmt->insert_id;
    $stmt->close();
}

// ✅ Insert into `receipts` table with `customer_id`
$sql = "INSERT INTO receipts (customer_id, customer_name, contact_info, order_type, total_amount, payment_method) 
        VALUES (?, ?, ?, ?, 0, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issss", $customer_id, $customer_name, $customer_contact, $order_type, $payment_method);
$stmt->execute();
$receipt_id = $stmt->insert_id;
$stmt->close();

// 🔹 Insert purchased items & calculate total
foreach ($_POST['item_name'] as $index => $item_name) {
    $quantity = $_POST['quantity'][$index];
    $price = $_POST['price'][$index];
    $gst = $_POST['gst'][$index];
    $total = $_POST['total'][$index];

    $total_amount += $total;

    $sql = "INSERT INTO receipt_items (receipt_id, item_name, quantity, unit_price, gst, total_price) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issddd", $receipt_id, $item_name, $quantity, $price, $gst, $total);
    $stmt->execute();
    $stmt->close();
}

// 🔹 Update total amount in `receipts`
$sql = "UPDATE receipts SET total_amount = ? WHERE receipt_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("di", $total_amount, $receipt_id);
$stmt->execute();
$stmt->close();

// 💰 Store in `payments` table under `income`
$sql = "INSERT INTO payments (receipt_id, customer_id,payment_method, amount, category,status) VALUES (?, ?, ?,?, 'income','completed')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $receipt_id, $customer_id, $payment_method, $total_amount);
$stmt->execute();
$stmt->close();

header("Location: print_receipt.php?receipt_id=$receipt_id");
exit();
?>
