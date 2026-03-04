<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$purchase_id = $_GET['id'] ?? 0;

// Fetch purchase details
$purchase = mysqli_query($conn, "SELECT p.*, s.supplier_name, s.contact_person, s.phone, u.username
                                 FROM purchases p
                                 LEFT JOIN suppliers s ON p.supplier_id = s.id
                                 LEFT JOIN users u ON p.created_by = u.id
                                 WHERE p.id = $purchase_id");
$purchase_details = mysqli_fetch_assoc($purchase);

if (!$purchase_details) {
    redirect('purchases.php');
}

// Fetch purchase items
$items = mysqli_query($conn, "SELECT pi.*, p.part_number, p.part_name
                              FROM purchase_items pi
                              JOIN parts_master p ON pi.part_id = p.id
                              WHERE pi.purchase_id = $purchase_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase #<?php echo htmlspecialchars($purchase_details['invoice_number']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-bicycle"></i> Bike Management System
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between mb-3">
            <h4>Purchase Details</h4>
            <a href="purchases.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Purchases
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Purchase Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Invoice #:</strong> <?php echo htmlspecialchars($purchase_details['invoice_number']); ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Date:</strong> <?php echo date('d-m-Y', strtotime($purchase_details['purchase_date'])); ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Supplier:</strong> <?php echo htmlspecialchars($purchase_details['supplier_name']); ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Total Amount:</strong> ₹<?php echo number_format($purchase_details['total_amount'], 2); ?>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-3">
                        <strong>Contact:</strong> <?php echo htmlspecialchars($purchase_details['contact_person']); ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Phone:</strong> <?php echo htmlspecialchars($purchase_details['phone']); ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Created By:</strong> <?php echo htmlspecialchars($purchase_details['username']); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Items Purchased</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Part #</th>
                            <th>Part Name</th>
                            <th>Quantity</th>
                            <th>Purchase Price</th>
                            <th>Selling Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $subtotal = 0;
                        while($item = mysqli_fetch_assoc($items)): 
                            $total = $item['quantity'] * $item['purchase_price'];
                            $subtotal += $total;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['part_number']); ?></td>
                            <td><?php echo htmlspecialchars($item['part_name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>₹<?php echo number_format($item['purchase_price'], 2); ?></td>
                            <td>₹<?php echo number_format($item['selling_price'], 2); ?></td>
                            <td>₹<?php echo number_format($total, 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">Subtotal:</th>
                            <th>₹<?php echo number_format($subtotal, 2); ?></th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-end">Grand Total:</th>
                            <th>₹<?php echo number_format($purchase_details['total_amount'], 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>