<?php
include 'db_config.php';

if (isset($_POST['vendor_invoice_id']) && isset($_POST['new_status'])) {
    $vendor_invoice_id = $_POST['vendor_invoice_id'];
    $new_status = $_POST['new_status'];

    // Fetch vendor invoice details
    $sql = "SELECT total_amount FROM vendor_invoices WHERE vendor_invoice_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $vendor_invoice_id);
    $stmt->execute();
    $stmt->bind_result($total_amount);
    $stmt->fetch();
    $stmt->close();

    // Update invoice status
    $update_sql = "UPDATE vendor_invoices SET status = ? WHERE vendor_invoice_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $new_status, $vendor_invoice_id);
    $stmt->execute();
    $stmt->close();

    // If marked as Paid, update or insert into payments table
    if ($new_status == "Paid") {
        // Check if payment already exists
        $check_payment = "SELECT payment_id FROM payments WHERE vendor_invoice_id = ?";
        $stmt = $conn->prepare($check_payment);
        $stmt->bind_param("i", $vendor_invoice_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            // Insert new payment record
            $stmt->close();
            $payment_sql = "INSERT INTO payments (vendor_invoice_id, amount, category, status) 
                            VALUES (?, ?, 'expense', 'Completed')";
            $stmt = $conn->prepare($payment_sql);
            $stmt->bind_param("id", $vendor_invoice_id, $total_amount);
            $stmt->execute();
            $stmt->close();
        } else {
            // Update existing payment record
            $stmt->close();
            $update_payment = "UPDATE payments SET amount = ?, status = 'Completed' WHERE vendor_invoice_id = ?";
            $stmt = $conn->prepare($update_payment);
            $stmt->bind_param("di", $total_amount, $vendor_invoice_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Redirect back
    header("Location: manage_vendor_invoices.php");
    exit();
}
?>
