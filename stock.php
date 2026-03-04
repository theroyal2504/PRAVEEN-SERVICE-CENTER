<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// Update minimum stock level
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_min_stock'])) {
    $part_id = $_POST['part_id'];
    $min_stock_level = $_POST['min_stock_level'];
    
    $query = "UPDATE stock SET min_stock_level = $min_stock_level WHERE part_id = $part_id";
    mysqli_query($conn, $query);
    
    $_SESSION['success'] = "Minimum stock level updated!";
    redirect('stock.php');
}

// Fetch stock with part details
$stock = mysqli_query($conn, "SELECT s.*, p.part_number, p.part_name, p.unit_price,
                              c.category_name, bc.name as company_name, bm.model_name,
                              CASE 
                                  WHEN s.quantity <= s.min_stock_level THEN 'danger'
                                  WHEN s.quantity <= s.min_stock_level * 2 THEN 'warning'
                                  ELSE 'success'
                              END as stock_status
                              FROM stock s
                              JOIN parts_master p ON s.part_id = p.id
                              LEFT JOIN categories c ON p.category_id = c.id
                              LEFT JOIN bike_companies bc ON p.company_id = bc.id
                              LEFT JOIN bike_models bm ON p.model_id = bm.id
                              ORDER BY 
                              CASE 
                                  WHEN s.quantity <= s.min_stock_level THEN 1
                                  WHEN s.quantity <= s.min_stock_level * 2 THEN 2
                                  ELSE 3
                              END, p.part_name");

// Get low stock count
$low_stock_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM stock WHERE quantity <= min_stock_level");
$low_stock = mysqli_fetch_assoc($low_stock_count)['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Management - Bike Management System</title>
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
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stock Summary -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Critical Stock</h6>
                                <h2 class="mb-0"><?php echo $low_stock; ?></h2>
                            </div>
                            <i class="bi bi-exclamation-triangle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Current Stock Status</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Part #</th>
                                <th>Part Name</th>
                                <th>Category</th>
                                <th>Company/Model</th>
                                <th>Current Stock</th>
                                <th>Min Stock Level</th>
                                <th>Unit Price</th>
                                <th>Stock Value</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_value = 0;
                            while($item = mysqli_fetch_assoc($stock)): 
                                $stock_value = $item['quantity'] * $item['unit_price'];
                                $total_value += $stock_value;
                            ?>
                            <tr class="table-<?php echo $item['stock_status']; ?>">
                                <td><?php echo htmlspecialchars($item['part_number']); ?></td>
                                <td><?php echo htmlspecialchars($item['part_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['category_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($item['company_name'] ?? 'N/A'); ?>
                                    <?php if($item['model_name']): ?>
                                        <br><small><?php echo htmlspecialchars($item['model_name']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?php echo $item['stock_status']; ?> fs-6">
                                        <?php echo $item['quantity']; ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" class="d-flex align-items-center">
                                        <input type="hidden" name="part_id" value="<?php echo $item['part_id']; ?>">
                                        <input type="number" name="min_stock_level" value="<?php echo $item['min_stock_level']; ?>" 
                                               class="form-control form-control-sm" style="width: 80px;" min="1">
                                        <button type="submit" name="update_min_stock" class="btn btn-sm btn-primary ms-2">
                                            <i class="bi bi-check"></i>
                                        </button>
                                    </form>
                                </td>
                                <td>₹<?php echo number_format($item['unit_price'], 2); ?></td>
                                <td>₹<?php echo number_format($stock_value, 2); ?></td>
                                <td>
                                    <?php if($item['stock_status'] == 'danger'): ?>
                                        <span class="badge bg-danger">Low Stock</span>
                                    <?php elseif($item['stock_status'] == 'warning'): ?>
                                        <span class="badge bg-warning">Moderate</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Good</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="stock_movement.php?part_id=<?php echo $item['part_id']; ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-graph-up"></i> History
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <td colspan="7" class="text-end"><strong>Total Stock Value:</strong></td>
                                <td colspan="3"><strong>₹<?php echo number_format($total_value, 2); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>