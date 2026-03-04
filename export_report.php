<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$type = $_GET['type'] ?? 'sales';
$start_date = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end'] ?? date('Y-m-d');

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $type . '_report_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

if ($type == 'sales') {
    // Sales Report Headers
    fputcsv($output, ['Date', 'Invoice #', 'Customer', 'Payment Method', 'Items Count', 'Total Amount', 'Created By']);
    
    $query = "SELECT s.sale_date, s.invoice_number, 
                     COALESCE(c.customer_name, 'Walk-in') as customer,
                     s.payment_method,
                     (SELECT COUNT(*) FROM sale_items WHERE sale_id = s.id) as item_count,
                     s.total_amount,
                     u.username
              FROM sales s
              LEFT JOIN customers c ON s.customer_id = c.id
              LEFT JOIN users u ON s.created_by = u.id
              WHERE s.sale_date BETWEEN '$start_date' AND '$end_date'
              ORDER BY s.sale_date DESC";
    
    $result = mysqli_query($conn, $query);
    while($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['sale_date'],
            $row['invoice_number'],
            $row['customer'],
            $row['payment_method'],
            $row['item_count'],
            $row['total_amount'],
            $row['username']
        ]);
    }
}
elseif ($type == 'purchases') {
    // Purchase Report Headers
    fputcsv($output, ['Date', 'Invoice #', 'Supplier', 'Items Count', 'Total Amount', 'Created By']);
    
    $query = "SELECT p.purchase_date, p.invoice_number, s.supplier_name,
                     (SELECT COUNT(*) FROM purchase_items WHERE purchase_id = p.id) as item_count,
                     p.total_amount, u.username
              FROM purchases p
              LEFT JOIN suppliers s ON p.supplier_id = s.id
              LEFT JOIN users u ON p.created_by = u.id
              WHERE p.purchase_date BETWEEN '$start_date' AND '$end_date'
              ORDER BY p.purchase_date DESC";
    
    $result = mysqli_query($conn, $query);
    while($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['purchase_date'],
            $row['invoice_number'],
            $row['supplier_name'],
            $row['item_count'],
            $row['total_amount'],
            $row['username']
        ]);
    }
}
elseif ($type == 'stock') {
    // Stock Report Headers
    fputcsv($output, ['Part #', 'Part Name', 'Category', 'Company', 'Model', 'Current Stock', 'Min Stock', 'Unit Price', 'Stock Value']);
    
    $query = "SELECT p.part_number, p.part_name, 
                     COALESCE(c.category_name, 'N/A') as category,
                     COALESCE(bc.name, 'N/A') as company,
                     COALESCE(bm.model_name, 'N/A') as model,
                     s.quantity, s.min_stock_level, p.unit_price,
                     (s.quantity * p.unit_price) as stock_value
              FROM stock s
              JOIN parts_master p ON s.part_id = p.id
              LEFT JOIN categories c ON p.category_id = c.id
              LEFT JOIN bike_companies bc ON p.company_id = bc.id
              LEFT JOIN bike_models bm ON p.model_id = bm.id
              ORDER BY stock_value DESC";
    
    $result = mysqli_query($conn, $query);
    while($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['part_number'],
            $row['part_name'],
            $row['category'],
            $row['company'],
            $row['model'],
            $row['quantity'],
            $row['min_stock_level'],
            $row['unit_price'],
            $row['stock_value']
        ]);
    }
}

fclose($output);
exit();
?>