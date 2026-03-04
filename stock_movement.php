<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$part_id = $_GET['part_id'] ?? 0;

// Fetch part details
$part = mysqli_query($conn, "SELECT p.*, s.quantity as current_stock 
                             FROM parts_master p 
                             LEFT JOIN stock s ON p.id = s.part_id 
                             WHERE p.id = $part_id");
$part_details = mysqli_fetch_assoc($part);

if (!$part_details) {
    redirect('stock.php');
}

// Fetch purchase history
$purchases = mysqli_query($conn, "SELECT pi.*, p.purchase_date, p.invoice_number, s.supplier_name,
                                  'purchase' as type
                                  FROM purchase_items pi
                                  JOIN purchases p ON pi.purchase_id = p.id
                                  LEFT JOIN suppliers s ON p.supplier_id = s.id
                                  WHERE pi.part_id = $part_id
                                  ORDER BY p.purchase_date DESC");

// Fetch sale history
$sales = mysqli_query($conn, "SELECT si.*, s.sale_date, s.invoice_number, c.customer_name,
                              'sale' as type
                              FROM sale_items si
                              JOIN sales s ON si.sale_id = s.id
                              LEFT JOIN customers c ON s.customer_id = c.id
                              WHERE si.part_id = $part_id
                              ORDER BY s.sale_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Movement - <?php echo htmlspecialchars($part_details['part_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-bicycle"></i> Bike Management System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="stock.php">
                            <i class="bi bi-boxes"></i> Back to Stock
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Part Details: <?php echo htmlspecialchars($part_details['part_name']); ?></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Part Number:</strong> <?php echo htmlspecialchars($part_details['part_number']); ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Current Stock:</strong> 
                        <span class="badge bg-primary fs-6"><?php echo $part_details['current_stock']; ?></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Unit Price:</strong> ₹<?php echo number_format($part_details['unit_price'], 2); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Purchase History (Stock In)</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Invoice #</th>
                                    <th>Supplier</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($purchase = mysqli_fetch_assoc($purchases)): ?>
                                <tr>
                                    <td><?php echo date('d-m-Y', strtotime($purchase['purchase_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($purchase['invoice_number']); ?></td>
                                    <td><?php echo htmlspecialchars($purchase['supplier_name']); ?></td>
                                    <td class="text-success">+<?php echo $purchase['quantity']; ?></td>
                                    <td>₹<?php echo number_format($purchase['purchase_price'], 2); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Sales History (Stock Out)</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($sale = mysqli_fetch_assoc($sales)): ?>
                                <tr>
                                    <td><?php echo date('d-m-Y', strtotime($sale['sale_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($sale['invoice_number']); ?></td>
                                    <td><?php echo htmlspecialchars($sale['customer_name'] ?? 'Walk-in'); ?></td>
                                    <td class="text-danger">-<?php echo $sale['quantity']; ?></td>
                                    <td>₹<?php echo number_format($sale['selling_price'], 2); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>