<?php
include 'db_config.php';
session_start();

$chart = $_GET['type'] ?? 'income_vs_expense';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $chart . '_report.csv"');

$output = fopen("php://output", "w");

$dateCondition = '';
$filter = $_GET['filter'] ?? 'all';

switch ($filter) {
    case 'day':
        $dateCondition = "DATE(payment_date) = CURDATE()";
        break;
    case 'week':
        $dateCondition = "YEARWEEK(payment_date, 1) = YEARWEEK(CURDATE(), 1)";
        break;
    case 'month':
        $dateCondition = "MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())";
        break;
    case 'custom':
        $from = $_GET['from'] ?? '';
        $to = $_GET['to'] ?? '';
        if ($from && $to) {
            $dateCondition = "DATE(payment_date) BETWEEN '$from' AND '$to'";
        }
        break;
}
$where = $dateCondition ? "WHERE $dateCondition" : '';

// Chart 1: Income vs Expense
if ($chart === 'income_vs_expense') {
    // Fetch all columns from payments table dynamically
    $result = $conn->query("SELECT * FROM payments $where ORDER BY payment_date");

    // Output header row with all column names
    if ($result && $result->num_rows > 0) {
        $fields = array_keys($result->fetch_assoc());
        fputcsv($output, $fields);

        // Reset result pointer and fetch rows again
        $result->data_seek(0);
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }
    }
}

// Chart 2: Expense by Category
elseif ($chart === 'expense_by_category') {
    // Output header row with all columns from expenses table
    $res1 = $conn->query("SHOW COLUMNS FROM expenses");
    $fields = [];
    while ($col = $res1->fetch_assoc()) {
        $fields[] = $col['Field'];
    }
    fputcsv($output, $fields);

    // Fetch and output all rows from expenses table
    $res2 = $conn->query("SELECT * FROM expenses " . ($dateCondition ? "WHERE $dateCondition" : ''));
    while ($row = $res2->fetch_assoc()) {
        fputcsv($output, $row);
    }

    // Output header row with all columns from vendor_invoices table
    $res3 = $conn->query("SHOW COLUMNS FROM vendor_invoices");
    $fields_vendor = [];
    while ($col = $res3->fetch_assoc()) {
        $fields_vendor[] = $col['Field'];
    }
    fputcsv($output, []); // Blank row for separation
    fputcsv($output, $fields_vendor);

    // Fetch and output all rows from vendor_invoices table
    $res4 = $conn->query("SELECT * FROM vendor_invoices " . ($dateCondition ? "WHERE $dateCondition" : ''));
    while ($row = $res4->fetch_assoc()) {
        fputcsv($output, $row);
    }

    // Output header row with all columns from vendor_receipts table
    $res5 = $conn->query("SHOW COLUMNS FROM vendor_receipts");
    $fields_receipts = [];
    while ($col = $res5->fetch_assoc()) {
        $fields_receipts[] = $col['Field'];
    }
    fputcsv($output, []); // Blank row for separation
    fputcsv($output, $fields_receipts);

    // Fetch and output all rows from vendor_receipts table (with payments join for amount)
    $query = "
        SELECT vr.*, p.amount 
        FROM vendor_receipts vr
        LEFT JOIN payments p ON vr.vendor_receipt_id = p.invoice_id 
        WHERE p.category = 'expense' " . ($dateCondition ? "AND $dateCondition" : '');
    $res6 = $conn->query($query);
    while ($row = $res6->fetch_assoc()) {
        fputcsv($output, $row);
    }
}

// Chart 3: Invoice Status
elseif ($chart === 'invoice_status') {
    // Output all columns from invoices table
    $res1 = $conn->query("SHOW COLUMNS FROM invoices");
    $fields_invoices = [];
    while ($col = $res1->fetch_assoc()) {
        $fields_invoices[] = $col['Field'];
    }
    fputcsv($output, $fields_invoices);

    $res2 = $conn->query("SELECT * FROM invoices " . ($dateCondition ? "WHERE $dateCondition" : ''));
    while ($row = $res2->fetch_assoc()) {
        fputcsv($output, $row);
    }

    // Blank row for separation
    fputcsv($output, []);

    // Output all columns from vendor_invoices table
    $res3 = $conn->query("SHOW COLUMNS FROM vendor_invoices");
    $fields_vendor = [];
    while ($col = $res3->fetch_assoc()) {
        $fields_vendor[] = $col['Field'];
    }
    fputcsv($output, $fields_vendor);

    $res4 = $conn->query("SELECT * FROM vendor_invoices " . ($dateCondition ? "WHERE $dateCondition" : ''));
    while ($row = $res4->fetch_assoc()) {
        fputcsv($output, $row);
    }
}

fclose($output);
exit;
