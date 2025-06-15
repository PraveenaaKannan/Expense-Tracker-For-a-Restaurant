<?php
include 'db_config.php';

// Get customer info
$customer_name = trim($_POST['customer_name']);
$customer_contact = trim($_POST['customer_contact']);
$payment_status = $_POST['payment_status'];
$payment_method = $_POST['payment_method'];
$advance_amount = (float) ($_POST['advance_amount'] ?? 0);
$payment_due_date = ($payment_status == "Unpaid") ? $_POST['payment_due_date'] : NULL;

// Check if customer exists
$check_customer = "SELECT customer_id FROM customers WHERE customer_name = ? AND contact_info = ?";
$stmt = $conn->prepare($check_customer);
$stmt->bind_param("ss", $customer_name, $customer_contact);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($customer_id);
    $stmt->fetch();
} else {
    // Insert new customer
    $insert_customer = "INSERT INTO customers (customer_name, contact_info) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_customer);
    $stmt->bind_param("ss", $customer_name, $customer_contact);
    $stmt->execute();
    $customer_id = $stmt->insert_id;
}
$stmt->close();

// Insert invoice
$sql = "INSERT INTO invoices (customer_id, total_amount, status, payment_due_date) VALUES (?, 0, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $customer_id, $payment_status, $payment_due_date);
$stmt->execute();
$invoice_id = $stmt->insert_id;
$stmt->close();

// Save items
$total_amount = 0;

foreach ($_POST['item_name'] as $index => $item_name) {
    $quantity = (int) $_POST['quantity'][$index];
    $price = (float) $_POST['price'][$index];
    $gst = (float) $_POST['gst'][$index];
    $total = (float) $_POST['total'][$index];

    $total_amount += $total;

    $sql = "INSERT INTO invoice_items (invoice_id, item_name, quantity, unit_price, gst, total_price) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isiddd", $invoice_id, $item_name, $quantity, $price, $gst, $total);
    $stmt->execute();
    $stmt->close();
}

// Update invoice total
$sql = "UPDATE invoices SET total_amount = ? WHERE invoice_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("di", $total_amount, $invoice_id);
$stmt->execute();
$stmt->close();

// **Payment Handling**
if ($payment_status == "Paid") {
    // Save Payment
    $sql = "INSERT INTO payments (invoice_id, customer_id, amount, payment_method, status) 
            VALUES (?, ?, ?, ?, 'Completed')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iids", $invoice_id, $customer_id, $total_amount, $payment_method);
    $stmt->execute();
    $stmt->close();

    // Generate Receipt
    $sql = "INSERT INTO receipts (invoice_id, customer_id, total_amount, payment_method, receipt_date) 
            VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iids", $invoice_id, $customer_id, $total_amount, $payment_method);
    $stmt->execute();
    $stmt->close();
} elseif ($payment_status == "Unpaid" && $advance_amount > 0) {
    // Save Advance Payment
    $sql = "INSERT INTO payments (invoice_id, customer_id, amount_paid, payment_method, status) 
            VALUES (?, ?, ?, ?, 'Pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iids", $invoice_id, $customer_id, $advance_amount, $payment_method);
    $stmt->execute();
    $stmt->close();
}

// Redirect to invoice print page
header("Location: print_invoice.php?invoice_id=$invoice_id");
exit();
?>
