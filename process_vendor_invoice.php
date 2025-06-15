<?php
include 'db_config.php';

// Get vendor details
$vendor_name = trim($_POST['vendor_name']);
$vendor_contact = trim($_POST['vendor_contact']);
$vendor_address = trim($_POST['vendor_address']);
$gst_number = trim($_POST['gst_number']);
$invoice_date = $_POST['invoice_date'];
$total_amount = $_POST['total_amount'];
$payment_status = $_POST['payment_status'];
$payment_due_date = ($payment_status == "Unpaid") ? $_POST['payment_due_date'] : NULL;
$category = $_POST['category']; // Example: Food, Utilities, etc.

// Check if the vendor exists
$check_vendor_sql = "SELECT vendor_id FROM vendors WHERE vendor_name = ? AND contact_info = ?";
$stmt = $conn->prepare($check_vendor_sql);
$stmt->bind_param("ss", $vendor_name, $vendor_contact);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Vendor exists, fetch vendor_id
    $stmt->bind_result($vendor_id);
    $stmt->fetch();
    $stmt->close();
} else {
    // Insert new vendor
    $stmt->close();
    $insert_vendor_sql = "INSERT INTO vendors (vendor_name, contact_info, address, gst_number) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_vendor_sql);
    $stmt->bind_param("ssss", $vendor_name, $vendor_contact, $vendor_address, $gst_number);
    $stmt->execute();
    $vendor_id = $stmt->insert_id; // Get the new vendor_id
    $stmt->close();
}

// Insert vendor invoice
$insert_invoice_sql = "INSERT INTO vendor_invoices (vendor_id, invoice_date, total_amount, status, category, payment_due_date) 
                       VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_invoice_sql);
$stmt->bind_param("isdsss", $vendor_id, $invoice_date, $total_amount, $payment_status, $category, $payment_due_date);
$stmt->execute();
$vendor_invoice_id = $stmt->insert_id; // Get vendor_invoice_id
$stmt->close();

// Check if payment exists for the invoice
$check_payment_sql = "SELECT payment_id FROM payments WHERE vendor_invoice_id = ?";
$stmt = $conn->prepare($check_payment_sql);
$stmt->bind_param("i", $vendor_invoice_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    // No payment entry, insert new
    $stmt->close();
    $insert_payment_sql = "INSERT INTO payments (vendor_invoice_id, amount, category, status) 
                           VALUES (?, ?, 'expense', ?)";
    $stmt = $conn->prepare($insert_payment_sql);
    $payment_status = ($payment_status == "Paid") ? "Completed" : "Pending";
    $stmt->bind_param("ids", $vendor_invoice_id, $total_amount, $payment_status);
    $stmt->execute();
    $stmt->close();
} else {
    // Payment entry exists, update it
    $stmt->close();
    $update_payment_sql = "UPDATE payments SET amount = ?, status = ? WHERE vendor_invoice_id = ?";
    $stmt = $conn->prepare($update_payment_sql);
    $stmt->bind_param("dsi", $total_amount, $payment_status, $vendor_invoice_id);
    $stmt->execute();
    $stmt->close();
}

// Redirect to manage vendor invoices page
header("Location: manage_vendor_invoices.php");
exit();
?>
