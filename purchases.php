<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// Handle new purchase - both admin and staff can add
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_purchase'])) {
    $supplier_id = $_POST['supplier_id'];
    $purchase_date = $_POST['purchase_date'];
    $invoice_number = mysqli_real_escape_string($conn, $_POST['invoice_number']);
    $created_by = $_SESSION['user_id'];
    
    // Insert purchase
    $query = "INSERT INTO purchases (supplier_id, purchase_date, invoice_number, created_by) 
              VALUES ($supplier_id, '$purchase_date', '$invoice_number', $created_by)";
    mysqli_query($conn, $query);
    $purchase_id = mysqli_insert_id($conn);
    
    // Insert purchase items
    $part_ids = $_POST['part_id'];
    $quantities = $_POST['quantity'];
    $purchase_prices = $_POST['purchase_price'];
    $selling_prices = $_POST['selling_price'];
    
    $total_amount = 0;
    
    for ($i = 0; $i < count($part_ids); $i++) {
        if (!empty($part_ids[$i]) && $quantities[$i] > 0) {
            $part_id = $part_ids[$i];
            $quantity = $quantities[$i];
            $purchase_price = $purchase_prices[$i];
            $selling_price = $selling_prices[$i];
            
            $item_query = "INSERT INTO purchase_items (purchase_id, part_id, quantity, purchase_price, selling_price) 
                          VALUES ($purchase_id, $part_id, $quantity, $purchase_price, $selling_price)";
            mysqli_query($conn, $item_query);
            
            $total_amount += $quantity * $purchase_price;
            
            // Update stock
            $stock_query = "UPDATE stock SET quantity = quantity + $quantity WHERE part_id = $part_id";
            mysqli_query($conn, $stock_query);
        }
    }
    
    // Update total amount in purchase
    mysqli_query($conn, "UPDATE purchases SET total_amount = $total_amount WHERE id = $purchase_id");
    
    $_SESSION['success'] = "Purchase added successfully!";
    redirect('purchases.php');
}

// Fetch data for dropdowns
$suppliers = mysqli_query($conn, "SELECT * FROM suppliers ORDER BY supplier_name");
$parts = mysqli_query($conn, "SELECT p.*, s.quantity as current_stock FROM parts_master p LEFT JOIN stock s ON p.id = s.part_id ORDER BY p.part_name");

// Fetch recent purchases - both admin and staff can view
$purchases = mysqli_query($conn, "SELECT p.*, s.supplier_name, u.username 
                                  FROM purchases p 
                                  LEFT JOIN suppliers s ON p.supplier_id = s.id 
                                  LEFT JOIN users u ON p.created_by = u.id 
                                  ORDER BY p.purchase_date DESC LIMIT 50");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchases - Bike Management System</title>
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
                        <span class="nav-link text-white">
                            <i class="bi bi-person-circle"></i> <?php echo $_SESSION['username']; ?> (<?php echo $_SESSION['role']; ?>)
                        </span>
                    </li>
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

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">New Purchase Entry</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="purchaseForm">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="supplier_id" class="form-label">Supplier</label>
                                        <select class="form-control" id="supplier_id" name="supplier_id" required>
                                            <option value="">Select Supplier</option>
                                            <?php while($supplier = mysqli_fetch_assoc($suppliers)): ?>
                                            <option value="<?php echo $supplier['id']; ?>"><?php echo htmlspecialchars($supplier['supplier_name']); ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="purchase_date" class="form-label">Purchase Date</label>
                                        <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="invoice_number" class="form-label">Invoice Number</label>
                                        <input type="text" class="form-control" id="invoice_number" name="invoice_number" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered" id="itemsTable">
                                    <thead>
                                        <tr>
                                            <th>Part</th>
                                            <th>Quantity</th>
                                            <th>Purchase Price</th>
                                            <th>Selling Price</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select class="form-control part-select" name="part_id[]" required>
                                                    <option value="">Select Part</option>
                                                    <?php 
                                                    mysqli_data_seek($parts, 0);
                                                    while($part = mysqli_fetch_assoc($parts)): 
                                                    ?>
                                                    <option value="<?php echo $part['id']; ?>" data-price="<?php echo $part['unit_price']; ?>">
                                                        <?php echo htmlspecialchars($part['part_name'] . ' (' . $part['part_number'] . ') - Stock: ' . ($part['current_stock'] ?? 0)); ?>
                                                    </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </td>
                                            <td><input type="number" class="form-control quantity" name="quantity[]" min="1" required></td>
                                            <td><input type="number" step="0.01" class="form-control purchase-price" name="purchase_price[]" required></td>
                                            <td><input type="number" step="0.01" class="form-control selling-price" name="selling_price[]" required></td>
                                            <td><input type="text" class="form-control row-total" readonly></td>
                                            <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-trash"></i></button></td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-end"><strong>Grand Total:</strong></td>
                                            <td><input type="text" class="form-control" id="grandTotal" readonly></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <button type="button" class="btn btn-success" id="addRow">
                                <i class="bi bi-plus-circle"></i> Add Another Item
                            </button>
                            <button type="submit" name="add_purchase" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Purchase
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Purchases</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Invoice #</th>
                                        <th>Supplier</th>
                                        <th>Total Amount</th>
                                        <th>Created By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($purchase = mysqli_fetch_assoc($purchases)): ?>
                                    <tr>
                                        <td><?php echo date('d-m-Y', strtotime($purchase['purchase_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($purchase['invoice_number']); ?></td>
                                        <td><?php echo htmlspecialchars($purchase['supplier_name']); ?></td>
                                        <td>₹<?php echo number_format($purchase['total_amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($purchase['username']); ?></td>
                                        <td>
                                            <a href="purchase_view.php?id=<?php echo $purchase['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add new row
        document.getElementById('addRow').addEventListener('click', function() {
            const tbody = document.querySelector('#itemsTable tbody');
            const newRow = tbody.rows[0].cloneNode(true);
            
            // Clear input values
            newRow.querySelectorAll('input').forEach(input => {
                if (input.type !== 'button') input.value = '';
            });
            
            // Reset select
            const select = newRow.querySelector('select');
            if (select) select.selectedIndex = 0;
            
            tbody.appendChild(newRow);
        });
        
        // Remove row
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-row') || e.target.closest('.remove-row')) {
                const tbody = document.querySelector('#itemsTable tbody');
                if (tbody.rows.length > 1) {
                    e.target.closest('tr').remove();
                    calculateGrandTotal();
                }
            }
        });
        
        // Calculate row total
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('quantity') || e.target.classList.contains('purchase-price')) {
                const row = e.target.closest('tr');
                const quantity = row.querySelector('.quantity').value || 0;
                const price = row.querySelector('.purchase-price').value || 0;
                const total = quantity * price;
                row.querySelector('.row-total').value = total.toFixed(2);
                calculateGrandTotal();
            }
        });
        
        // Auto-fill selling price from part selection
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('part-select')) {
                const selected = e.target.options[e.target.selectedIndex];
                const price = selected.dataset.price;
                if (price) {
                    const row = e.target.closest('tr');
                    row.querySelector('.selling-price').value = price;
                }
            }
        });
        
        function calculateGrandTotal() {
            let grandTotal = 0;
            document.querySelectorAll('.row-total').forEach(input => {
                grandTotal += parseFloat(input.value) || 0;
            });
            document.getElementById('grandTotal').value = grandTotal.toFixed(2);
        }
    });
    </script>
</body>
</html>