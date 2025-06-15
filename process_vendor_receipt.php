<?php
include 'db_config.php';

$payment_type = $_POST['payment_type'];
$receipt_amount = $_POST['receipt_amount'];
$payment_method = $_POST['payment_method'];
$receipt_date = $_POST['receipt_date'];

if ($payment_type === "invoice") {
    // Invoice Payment
    $vendor_invoice_id = $_POST['vendor_invoice_id'];

   // Get vendor ID and category from the selected invoice
$stmt = $conn->prepare("SELECT vendor_id, category FROM vendor_invoices WHERE vendor_invoice_id = ?");
$stmt->bind_param("i", $vendor_invoice_id);
$stmt->execute();
$stmt->bind_result($vendor_id, $category);
$stmt->fetch();
$stmt->close();

if (!$vendor_id) {
    die("Error: Vendor not found for the selected invoice.");
}

// Insert receipt with category fetched from the invoice
$receipt_sql = "INSERT INTO vendor_receipts (vendor_invoice_id, vendor_id, amount, payment_method, receipt_date, category) 
                VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($receipt_sql);
$stmt->bind_param("iidsss", $vendor_invoice_id, $vendor_id, $receipt_amount, $payment_method, $receipt_date, $category);
$stmt->execute();
$vendor_receipt_id = $stmt->insert_id; // Get inserted receipt ID
$stmt->close();

} else {
    // Direct Payment
    $vendor_name = trim($_POST['vendor_name']);
    $vendor_contact = trim($_POST['vendor_contact']);
    $vendor_address = trim($_POST['vendor_address']);
    $vendor_gst = trim($_POST['vendor_gst']);
    $category = trim($_POST['category']);

    // Check if vendor exists
    $stmt = $conn->prepare("SELECT vendor_id FROM vendors WHERE vendor_name = ? AND contact_info = ?");
    $stmt->bind_param("ss", $vendor_name, $vendor_contact);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($vendor_id);
        $stmt->fetch();
    } else {
        // Insert new vendor
        $stmt->close();
        $insert_vendor = "INSERT INTO vendors (vendor_name, contact_info, address, gst_number) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_vendor);
        $stmt->bind_param("ssss", $vendor_name, $vendor_contact, $vendor_address, $vendor_gst);
        $stmt->execute();
        $vendor_id = $stmt->insert_id;
    }
    $stmt->close();

    // Insert direct payment receipt
    $receipt_sql = "INSERT INTO vendor_receipts (vendor_invoice_id, vendor_id, amount, payment_method, receipt_date,category) 
                    VALUES (NULL, ?, ?, ?, ?,?)";
    $stmt = $conn->prepare($receipt_sql);
    $stmt->bind_param("idsss", $vendor_id, $receipt_amount, $payment_method, $receipt_date,$category);
    $stmt->execute();
    $vendor_receipt_id = $stmt->insert_id; // Get inserted receipt ID
    $stmt->close();

    // Store direct payment in payments table
    $payment_sql = "INSERT INTO payments (vendor_receipt_id, amount, category, status) VALUES (?, ?, 'expense', 'Completed')";
    $stmt = $conn->prepare($payment_sql);
    $stmt->bind_param("id", $vendor_receipt_id, $receipt_amount);
    $stmt->execute();
    $stmt->close();
}

// Redirect to manage_vendor_receipts page
header("Location: manage_vendor_receipts.php");
exit();
?>
