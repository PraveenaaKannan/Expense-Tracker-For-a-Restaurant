<?php
include 'db_config.php';

if (isset($_POST['invoice_id']) && isset($_POST['new_status'])) {
    $invoice_id = $_POST['invoice_id'];
    $new_status = $_POST['new_status'];

    // Fetch invoice details
    $sql = "SELECT customer_id, total_amount FROM invoices WHERE invoice_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $invoice_id);
    $stmt->execute();
    $stmt->bind_result($customer_id, $total_amount);
    $stmt->fetch();
    $stmt->close();

    // Update invoice status
    $update_sql = "UPDATE invoices SET status = ? WHERE invoice_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $new_status, $invoice_id);
    $stmt->execute();
    $stmt->close();

    // Generate receipt if paid
    if ($new_status == "Paid") {
        $payment_method = "Cash"; // You can update this dynamically
        $receipt_sql = "INSERT INTO receipts (invoice_id, customer_id, total_amount, payment_method) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($receipt_sql);
        $stmt->bind_param("iids", $invoice_id, $customer_id, $total_amount, $payment_method);
        $stmt->execute();
        $receipt_id = $stmt->insert_id;
        $stmt->close();

        // Check if a payment entry already exists for this invoice
$check_payment_sql = "SELECT payment_id FROM payments WHERE invoice_id = ?";
$stmt = $conn->prepare($check_payment_sql);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    // No existing payment entry, insert a new one
    $stmt->close();
    $payment_sql = "INSERT INTO payments (invoice_id, amount, category, status) 
                    VALUES (?, ?, 'income', 'Completed')";
    $stmt = $conn->prepare($payment_sql);
    $stmt->bind_param("id", $invoice_id, $total_amount);
    $stmt->execute();
    $stmt->close();
} else {
    // Payment entry exists, update the amount and status
    $stmt->close();
    $update_payment_sql = "UPDATE payments SET amount = ?, status = 'Completed' WHERE invoice_id = ?";
    $stmt = $conn->prepare($update_payment_sql);
    $stmt->bind_param("di", $total_amount, $invoice_id);
    $stmt->execute();
    $stmt->close();
}
        echo "Payment status updated and receipt generated!";
    } else {
        echo "Payment status updated!";
    }
}
?>
