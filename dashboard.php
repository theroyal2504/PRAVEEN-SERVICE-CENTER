<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// Get dashboard statistics
$stats = [];

// Total customers
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM customers");
$stats['customers'] = mysqli_fetch_assoc($result)['count'];

// Total bikes (parts in stock)
$result = mysqli_query($conn, "SELECT COUNT(DISTINCT part_id) as count FROM stock WHERE quantity > 0");
$stats['bikes'] = mysqli_fetch_assoc($result)['count'];

// Pending jobs
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM pending_jobs WHERE status = 'pending'");
$stats['pending_jobs'] = mysqli_fetch_assoc($result)['count'];

// Low stock items
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM stock s JOIN parts_master p ON s.part_id = p.id WHERE s.quantity <= s.min_stock_level");
$stats['low_stock'] = mysqli_fetch_assoc($result)['count'];

// Get today's sales
$today = date('Y-m-d');
$result = mysqli_query($conn, "SELECT SUM(total_amount) as today_sales, COUNT(*) as today_transactions FROM sales WHERE sale_date = '$today'");
$today_data = mysqli_fetch_assoc($result);

// Get outstanding dues - FIXED SYNTAX HERE
$dues_query = mysqli_query($conn, "SELECT 
                                    COUNT(*) as total_due_invoices,
                                    SUM(CASE 
                                        WHEN grand_total IS NOT NULL AND grand_total > 0 
                                        THEN (grand_total - paid_amount)
                                        ELSE (total_amount - paid_amount)
                                    END) as total_due_amount
                                FROM sales 
                                WHERE (CASE 
                                        WHEN grand_total IS NOT NULL AND grand_total > 0 
                                        THEN grand_total
                                        ELSE total_amount
                                    END) > paid_amount");
$dues = mysqli_fetch_assoc($dues_query);

// Get recent dues with correct calculation - SHOW ONLY DUE BILLS - FIXED SYNTAX HERE
$recent_dues = mysqli_query($conn, "SELECT 
                                    s.id, 
                                    s.invoice_number, 
                                    s.sale_date, 
                                    s.total_amount,
                                    s.grand_total,
                                    s.paid_amount, 
                                    s.due_amount,
                                    s.payment_status,
                                    s.discount_type,
                                    s.discount_value,
                                    s.discount_amount,
                                    s.subtotal,
                                    COALESCE(s.grand_total, s.total_amount) as actual_total,
                                    (COALESCE(s.grand_total, s.total_amount) - s.paid_amount) as actual_due_amount,
                                    c.customer_name, 
                                    c.phone, 
                                    c.vehicle_registration,
                                    c.id as customer_id
                                  FROM sales s
                                  LEFT JOIN customers c ON s.customer_id = c.id
                                  WHERE (COALESCE(s.grand_total, s.total_amount) - s.paid_amount) > 0
                                  ORDER BY s.sale_date DESC
                                  LIMIT 15");

// Rest of your HTML code continues exactly as you had it...
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Bike Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        /* Statistics Cards (larger) */
        .row > .col-md-3 { padding-bottom: 8px; }
        .row > .col-md-3 .card .card-body { padding: 10px; }
        .row > .col-md-3 .card .card-body h6 { font-size: 14px; margin-bottom: 4px; }
        .row > .col-md-3 .card .card-body h2 { font-size: 22px; margin-bottom: 4px; }
        .row > .col-md-3 .card .card-body small { font-size: 12px; display:block; margin-top:0; }
        .row > .col-md-3 i.bi { font-size: 28px; }

        /* Reduce gap between Statistics Cards and Today's Performance */
        .statistics-cards { margin-bottom: 6px; }
        .statistics-cards .col-md-3 { margin-bottom: 6px; }
        .row.mt-2 { margin-top: 6px; }

        /* Master Entries styling */
        .master-entries .card { border-radius: 10px; overflow: hidden; transition: transform .15s ease, box-shadow .15s ease; box-shadow: 0 2px 6px rgba(0,0,0,0.06); }
        .master-entries .card:hover { transform: translateY(-6px); box-shadow: 0 10px 30px rgba(0,0,0,0.12); }
        .master-entries .card .card-body { padding: 18px 12px; }
        .master-entries .card .card-body i.bi { font-size: 48px; opacity: .98; }
        .master-entries .card .card-body h5 { font-size: 18px; margin-top: 8px; margin-bottom: 6px; font-weight: 700; }
        .master-entries .card .card-body p { font-size: 14px; margin-bottom: 8px; color: #6c757d; }
        .master-entries .card .btn { border-radius: 20px; padding: .35rem .8rem; font-size: 14px; }
        .master-entries .card .text-muted.small { font-size: 13px; }
        @media (max-width: 1200px) { .master-entries .card .card-body i.bi { font-size: 42px; } }
        @media (max-width: 768px) { .master-entries .card .card-body i.bi { font-size: 36px; } }
        @media (max-width: 576px) { .master-entries .card .card-body i.bi { font-size: 30px; } }

        /* Staff Operations styling - modern, compact cards */
        .staff-ops .card { border-radius: 12px; overflow: hidden; transition: transform .14s ease, box-shadow .14s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .staff-ops .card:hover { transform: translateY(-6px); box-shadow: 0 12px 28px rgba(0,0,0,0.12); }
        .staff-ops .card .card-body { padding: 14px 10px; }
        .staff-ops .card .card-body i.bi { font-size: 30px; margin-bottom: 6px; color: inherit; }
        .staff-ops .card .card-body h6 { font-size: 14px; margin-bottom: 4px; font-weight: 600; }
        .staff-ops .card .card-body small { font-size: 12px; color: #6c757d; }
        .staff-ops .card.bg-light { background: linear-gradient(180deg,#ffffff,#fbfbfb); }
        .staff-ops .card .btn { font-size: 13px; border-radius: 18px; padding: .3rem .6rem; }
        .staff-ops .col-md-2 { padding-bottom: 10px; }
        @media (max-width: 768px) { .staff-ops .card .card-body i.bi { font-size: 26px; } }

        /* Today's Performance & Dues (larger) */
        .row.mt-2 .card .card-body { padding: 10px; }
        .row.mt-2 .card .card-body h6 { font-size: 14px; margin-bottom: 4px; }
        .row.mt-2 .card .card-body h2 { font-size: 20px; margin-bottom: 4px; }
        .row.mt-2 .card .card-body small { font-size: 12px; display:block; margin-top:0; }
        .row.mt-2 i.bi { font-size: 30px; }
        /* tighter card spacing */
        .row.mt-2 .col-md-6 { padding-bottom: 8px; }

        @media (max-width: 992px) {
            .row > .col-md-3 .card .card-body h2 { font-size: 20px; }
            .row > .col-md-3 i.bi { font-size: 24px; }
            .row.mt-2 .card .card-body h2 { font-size: 18px; }
            .row.mt-2 i.bi { font-size: 24px; }
        }
        @media (max-width: 576px) {
            .row.mt-2 .card .card-body h2 { font-size: 16px; }
            .row.mt-2 i.bi { font-size: 20px; }
            .row > .col-md-3 .card .card-body h2 { font-size: 16px; }
            .row > .col-md-3 i.bi { font-size: 20px; }
        }
        
        /* Due amount styling */
        .due-amount {
            font-weight: bold;
        }
        .due-positive {
            color: #dc3545;
        }
        .due-zero {
            color: #28a745;
        }
        
        /* Collect payment button styling */
        .collect-payment-btn {
            background-color: #ffc107;
            color: #000;
            border: none;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            transition: all 0.3s;
        }
        .collect-payment-btn:hover {
            background-color: #e0a800;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
        }
        .collect-payment-btn i {
            margin-right: 4px;
        }
        
        /* Quick payment modal */
        .quick-payment-modal .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .quick-payment-modal .modal-title i {
            margin-right: 8px;
        }
        .due-highlight {
            font-size: 1.2em;
            font-weight: bold;
            color: #dc3545;
            padding: 10px;
            background: #f8d7da;
            border-radius: 8px;
            text-align: center;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-bicycle"></i> PRAVEEN SERVICE CENTER
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
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Welcome Banner -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <h5 class="alert-heading">Welcome back, <?php echo $_SESSION['username']; ?>!</h5>
                    <p class="mb-0">Today is <?php echo date('l, d F Y'); ?></p>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row statistics-cards">
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Customers</h6>
                                <h2 class="mb-0"><?php echo $stats['customers']; ?></h2>
                                <small>Registered customers</small>
                            </div>
                            <i class="bi bi-people fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Bikes</h6>
                                <h2 class="mb-0"><?php echo $stats['bikes']; ?></h2>
                                <small>Parts in stock</small>
                            </div>
                            <i class="bi bi-bicycle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Pending Jobs</h6>
                                <h2 class="mb-0"><?php echo $stats['pending_jobs']; ?></h2>
                                <small>Service jobs pending</small>
                            </div>
                            <i class="bi bi-tools fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Low Stock Items</h6>
                                <h2 class="mb-0"><?php echo $stats['low_stock']; ?></h2>
                                <small>Need reorder</small>
                            </div>
                            <i class="bi bi-exclamation-triangle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Performance & Dues -->
        <div class="row mt-2">
            <div class="col-md-6 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Today's Sales</h6>
                                <h2 class="mb-0">₹<?php echo number_format($today_data['today_sales'] ?? 0, 2); ?></h2>
                                <small><?php echo $today_data['today_transactions'] ?? 0; ?> transactions today</small>
                            </div>
                            <i class="bi bi-graph-up-arrow fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Outstanding Dues</h6>
                                <h2 class="mb-0">₹<?php echo number_format($dues['total_due_amount'] ?? 0, 2); ?></h2>
                                <small><?php echo $dues['total_due_invoices'] ?? 0; ?> pending invoices</small>
                            </div>
                            <i class="bi bi-cash-stack fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Master Entries Section - Only visible to Admin -->
        <?php if (isAdmin()): ?>
        <div class="master-entries">
        <div class="row mt-4">
            <div class="col-12">
                <h4 class="mb-3 border-bottom pb-2">
                    <i class="bi bi-database"></i> Master Entries (Admin Only)
                </h4>
            </div>
            
            <!-- Bike Companies -->
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-primary">
                    <div class="card-body text-center">
                        <i class="bi bi-building fs-1 text-primary"></i>
                        <h5 class="card-title mt-2">Bike Companies</h5>
                        <p class="card-text text-muted small">Add/Edit bike & scooty companies</p>
                        <a href="companies.php" class="btn btn-outline-primary btn-sm w-100">Manage</a>
                        <small class="text-muted d-block mt-2">Purchase entry & stock update</small>
                    </div>
                </div>
            </div>
            
            <!-- Bike Models -->
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-success">
                    <div class="card-body text-center">
                        <i class="bi bi-gear fs-1 text-success"></i>
                        <h5 class="card-title mt-2">Bike Models</h5>
                        <p class="card-text text-muted small">Add models for each company</p>
                        <a href="models.php" class="btn btn-outline-success btn-sm w-100">Manage</a>
                        <small class="text-muted d-block mt-2">View current stock & movements</small>
                    </div>
                </div>
            </div>
            
            <!-- Categories -->
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-warning">
                    <div class="card-body text-center">
                        <i class="bi bi-tags fs-1 text-warning"></i>
                        <h5 class="card-title mt-2">Categories</h5>
                        <p class="card-text text-muted small">Manage part categories</p>
                        <a href="categories.php" class="btn btn-outline-warning btn-sm w-100">Manage</a>
                        <small class="text-muted d-block mt-2">Profit/Loss, Sales reports</small>
                    </div>
                </div>
            </div>
            
            <!-- Parts Master -->
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-info">
                    <div class="card-body text-center">
                        <i class="bi bi-box-seam fs-1 text-info"></i>
                        <h5 class="card-title mt-2">Parts Master</h5>
                        <p class="card-text text-muted small">Add parts with part numbers</p>
                        <a href="parts.php" class="btn btn-outline-info btn-sm w-100">Manage</a>
                        <small class="text-muted d-block mt-2">View all parts</small>
                    </div>
                </div>
            </div>
            
            <!-- Suppliers -->
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-secondary">
                    <div class="card-body text-center">
                        <i class="bi bi-truck fs-1 text-secondary"></i>
                        <h5 class="card-title mt-2">Suppliers</h5>
                        <p class="card-text text-muted small">Manage vendors/suppliers</p>
                        <a href="suppliers.php" class="btn btn-outline-secondary btn-sm w-100">Manage</a>
                        <small class="text-muted d-block mt-2">Vendor management</small>
                    </div>
                </div>
            </div>
            
            <!-- Customers (Admin can manage) -->
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-primary">
                    <div class="card-body text-center">
                        <i class="bi bi-people fs-1 text-primary"></i>
                        <h5 class="card-title mt-2">Customers</h5>
                        <p class="card-text text-muted small">Manage customers with vehicle details</p>
                        <a href="customers.php" class="btn btn-outline-primary btn-sm w-100">Manage</a>
                        <small class="text-muted d-block mt-2">Add/Edit/Delete customers</small>
                    </div>
                </div>
            </div>
            
            <!-- System Settings -->
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-dark">
                    <div class="card-body text-center">
                        <i class="bi bi-gear fs-1 text-dark"></i>
                        <h5 class="card-title mt-2">System Settings</h5>
                        <p class="card-text text-muted small">Configure invoice & business info</p>
                        <a href="settings.php" class="btn btn-outline-dark btn-sm w-100">Configure</a>
                        <small class="text-muted d-block mt-2">Invoice format, GST, etc.</small>
                    </div>
                </div>
            </div>
            
            <!-- Reports & Analytics -->
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-danger">
                    <div class="card-body text-center">
                        <i class="bi bi-graph-up fs-1 text-danger"></i>
                        <h5 class="card-title mt-2">Profit & Loss</h5>
                        <p class="card-text text-muted small">View revenue, costs and profits</p>
                        <a href="profit_loss.php" class="btn btn-outline-danger btn-sm w-100">View P&L</a>
                        <small class="text-muted d-block mt-2">Financial analysis</small>
                    </div>
                </div>
            </div>
            <!-- Simple Accounting -->
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-success">
                    <div class="card-body text-center">
                        <i class="bi bi-calculator fs-1 text-success"></i>
                        <h5 class="card-title mt-2">Simple Accounting</h5>
                        <p class="card-text text-muted small">Track income, expenses & balance</p>
                        <a href="simple_accounting.php" class="btn btn-outline-success btn-sm w-100">View Accounting</a>
                        <small class="text-muted d-block mt-2">Today's income & total balance</small>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <?php endif; ?>

        <!-- Staff Operations - Visible to both Admin and Staff -->
        <div class="row mt-4 staff-ops">
            <div class="col-12">
                <h4 class="mb-3 border-bottom pb-2">
                    <i class="bi bi-tools"></i> Staff Operations
                </h4>
            </div>
            
            <div class="col-md-2 mb-3">
                <a href="purchases.php" class="text-decoration-none">
                    <div class="card bg-light h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-cart-plus fs-1 text-primary"></i>
                            <h6 class="mt-2">New Purchase</h6>
                            <small class="text-muted">Add stock</small>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-2 mb-3">
                <a href="sales.php" class="text-decoration-none">
                    <div class="card bg-light h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-cash-stack fs-1 text-success"></i>
                            <h6 class="mt-2">New Sale</h6>
                            <small class="text-muted">Create invoice</small>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-2 mb-3">
                <a href="jobs.php" class="text-decoration-none">
                    <div class="card bg-light h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-wrench fs-1 text-warning"></i>
                            <h6 class="mt-2">Pending Jobs</h6>
                            <small class="text-muted">Service jobs</small>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-2 mb-3">
                <a href="stock.php" class="text-decoration-none">
                    <div class="card bg-light h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-boxes fs-1 text-info"></i>
                            <h6 class="mt-2">Stock Report</h6>
                            <small class="text-muted">Current stock</small>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Customers View - Staff can only view -->
            <?php if (isStaff()): ?>
            <div class="col-md-2 mb-3">
                <a href="customers.php" class="text-decoration-none">
                    <div class="card bg-light h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-people fs-1 text-secondary"></i>
                            <h6 class="mt-2">View Customers</h6>
                            <small class="text-muted">Customer list</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>
            
            <div class="col-md-2 mb-3">
                <a href="reports.php" class="text-decoration-none">
                    <div class="card bg-light h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-file-text fs-1 text-danger"></i>
                            <h6 class="mt-2">Reports</h6>
                            <small class="text-muted">Analytics</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Outstanding Dues Section (with Subtotal and Discount) -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-exclamation-triangle"></i> 
                            Recent Outstanding Dues 
                            <span class="badge bg-light text-dark ms-2"><?php echo mysqli_num_rows($recent_dues); ?> Bills</span>
                        </h5>
                        <a href="sales.php" class="btn btn-sm btn-light">View All Sales</a>
                    </div>
                    <div class="card-body">
                        <?php
                        if(mysqli_num_rows($recent_dues) > 0):
                        ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Vehicle</th>
                                        <th>Sub Total</th>
                                        <th>Discount</th>
                                        <th>Grand Total</th>
                                        <th>Paid</th>
                                        <th>Due Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Reset pointer
                                    mysqli_data_seek($recent_dues, 0);
                                    while($due = mysqli_fetch_assoc($recent_dues)): 
                                        // Get subtotal from sale items
                                        $subtotal_query = mysqli_query($conn, "SELECT SUM(quantity * selling_price) as subtotal 
                                                                              FROM sale_items WHERE sale_id = " . $due['id']);
                                        $subtotal_data = mysqli_fetch_assoc($subtotal_query);
                                        $subtotal = $subtotal_data['subtotal'] ?? $due['actual_total'];
                                        
                                        // Get discount info
                                        $discount_amount = $due['discount_amount'] ?? 0;
                                        $discount_type = $due['discount_type'] ?? 'fixed';
                                        $discount_value = $due['discount_value'] ?? 0;
                                        
                                        $total_bill = $due['actual_total'];
                                        $due_amount = $due['actual_due_amount'];
                                        $paid_percentage = $total_bill > 0 ? ($due['paid_amount'] / $total_bill) * 100 : 0;
                                    ?>
                                    <tr>
                                        <td><strong><?php echo $due['invoice_number']; ?></strong></td>
                                        <td><?php echo date('d-m-Y', strtotime($due['sale_date'])); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($due['customer_name'] ?? 'Walk-in'); ?>
                                            <?php if($due['phone']): ?>
                                                <br><small class="text-muted"><?php echo $due['phone']; ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($due['vehicle_registration']): ?>
                                                <span class="badge bg-info"><?php echo $due['vehicle_registration']; ?></span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-secondary">
                                            <strong>₹<?php echo number_format($subtotal, 2); ?></strong>
                                        </td>
                                        <td class="text-info">
                                            <?php if($discount_amount > 0): ?>
                                                <strong>-₹<?php echo number_format($discount_amount, 2); ?></strong>
                                                <?php if($discount_type == 'percentage'): ?>
                                                    <br><small>(<?php echo $discount_value; ?>%)</small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-primary">
                                            <strong>₹<?php echo number_format($total_bill, 2); ?></strong>
                                        </td>
                                        <td class="text-success">
                                            ₹<?php echo number_format($due['paid_amount'], 2); ?>
                                            <br><small class="text-muted"><?php echo round($paid_percentage, 1); ?>%</small>
                                        </td>
                                        <td class="text-danger due-amount">
                                            <strong>₹<?php echo number_format($due_amount, 2); ?></strong>
                                        </td>
                                        <td>
                                            <?php 
                                            $status_class = 'warning';
                                            $status_text = 'Partial';
                                            if($due_amount == $total_bill) {
                                                $status_class = 'danger';
                                                $status_text = 'Pending';
                                            } elseif($due_amount == 0) {
                                                $status_class = 'success';
                                                $status_text = 'Paid';
                                            }
                                            ?>
                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                <?php echo $status_text; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <!-- Payment options -->
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="sale_view.php?id=<?php echo $due['id']; ?>" 
                                                   class="btn btn-info" 
                                                   title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-warning" 
                                                        onclick="openQuickPayment(<?php echo $due['id']; ?>, '<?php echo $due['invoice_number']; ?>', <?php echo $due_amount; ?>)"
                                                        title="Collect Payment">
                                                    <i class="bi bi-cash"></i>
                                                </button>
                                                <a href="invoice.php?id=<?php echo $due['id']; ?>" 
                                                   class="btn btn-secondary" 
                                                   target="_blank" 
                                                   title="Print Invoice">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                                <tfoot class="table-secondary">
                                    <?php
                                    // Calculate totals
                                    $total_subtotal = 0;
                                    $total_discount = 0;
                                    $total_grand = 0;
                                    $total_paid_foot = 0;
                                    $total_due_foot = 0;
                                    
                                    mysqli_data_seek($recent_dues, 0);
                                    while($due = mysqli_fetch_assoc($recent_dues)) {
                                        $subtotal_query = mysqli_query($conn, "SELECT SUM(quantity * selling_price) as subtotal 
                                                                              FROM sale_items WHERE sale_id = " . $due['id']);
                                        $subtotal_data = mysqli_fetch_assoc($subtotal_query);
                                        $subtotal = $subtotal_data['subtotal'] ?? $due['actual_total'];
                                        
                                        $total_subtotal += $subtotal;
                                        $total_discount += ($due['discount_amount'] ?? 0);
                                        $total_grand += $due['actual_total'];
                                        $total_paid_foot += $due['paid_amount'];
                                        $total_due_foot += $due['actual_due_amount'];
                                    }
                                    ?>
                                    <tr>
                                        <th colspan="4" class="text-end">Totals:</th>
                                        <th class="text-secondary">₹<?php echo number_format($total_subtotal, 2); ?></th>
                                        <th class="text-info">-₹<?php echo number_format($total_discount, 2); ?></th>
                                        <th class="text-primary">₹<?php echo number_format($total_grand, 2); ?></th>
                                        <th class="text-success">₹<?php echo number_format($total_paid_foot, 2); ?></th>
                                        <th class="text-danger">₹<?php echo number_format($total_due_foot, 2); ?></th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <!-- Progress bar for total due collection -->
                        <?php
                        $collection_percentage = $total_grand > 0 ? ($total_paid_foot / $total_grand) * 100 : 0;
                        ?>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>
                                    <strong>Summary:</strong> 
                                    Subtotal: ₹<?php echo number_format($total_subtotal, 2); ?> | 
                                    Discount: ₹<?php echo number_format($total_discount, 2); ?> | 
                                    Grand Total: ₹<?php echo number_format($total_grand, 2); ?> | 
                                    <span class="text-success">Paid: ₹<?php echo number_format($total_paid_foot, 2); ?></span> | 
                                    <span class="text-danger">Due: ₹<?php echo number_format($total_due_foot, 2); ?></span>
                                </span>
                                <span><strong>Collection Rate:</strong> <?php echo round($collection_percentage, 1); ?>%</span>
                            </div>
                            <div class="progress mt-1" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: <?php echo $collection_percentage; ?>%"></div>
                                <div class="progress-bar bg-warning" style="width: <?php echo 100 - $collection_percentage; ?>%"></div>
                            </div>
                        </div>
                        
                        <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle-fill text-success fs-1"></i>
                            <p class="text-muted mt-2 mb-0">No outstanding dues! All invoices are paid. 🎉</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Footer -->
        <div class="row mt-4 mb-4">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-info-circle"></i> Current Month: <?php echo date('F Y'); ?></span>
                            <span><i class="bi bi-calendar"></i> Invoice Prefix: 
                                <?php 
                                $prefix = mysqli_fetch_assoc(mysqli_query($conn, "SELECT setting_value FROM system_settings WHERE setting_key = 'invoice_prefix'"));
                                echo $prefix ? $prefix['setting_value'] : 'INV';
                                ?>
                            </span>
                            <span><i class="bi bi-clock-history"></i> Last Login: <?php echo date('d-m-Y H:i'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Payment Modal -->
    <div class="modal fade quick-payment-modal" id="quickPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-cash-stack"></i> Quick Payment Collection
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="sale_view.php" id="quickPaymentForm">
                    <div class="modal-body">
                        <input type="hidden" name="sale_id" id="quick_sale_id">
                        <input type="hidden" name="add_payment" value="1">
                        
                        <div class="alert alert-info" id="invoice_info"></div>
                        
                        <div class="due-highlight mb-3" id="due_display"></div>
                        
                        <div class="mb-3">
                            <label for="quick_payment_amount" class="form-label">Payment Amount *</label>
                            <input type="number" step="0.01" class="form-control" id="quick_payment_amount" name="payment_amount" required>
                            <small class="text-muted">Enter amount to collect</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="quick_payment_method" class="form-label">Payment Method *</label>
                            <select class="form-control" id="quick_payment_method" name="payment_method" required>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="online">Online</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="quick_reference_number" class="form-label">Reference Number (Optional)</label>
                            <input type="text" class="form-control" id="quick_reference_number" name="reference_number">
                        </div>
                        
                        <div class="mb-3">
                            <label for="quick_notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="quick_notes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitQuickPayment">
                            <i class="bi bi-check-circle"></i> Confirm Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Function to open quick payment modal
    function openQuickPayment(saleId, invoiceNumber, dueAmount) {
        document.getElementById('quick_sale_id').value = saleId;
        document.getElementById('invoice_info').innerHTML = '<strong>Invoice #:</strong> ' + invoiceNumber;
        document.getElementById('due_display').innerHTML = 'Due Amount: ₹' + dueAmount.toFixed(2);
        document.getElementById('quick_payment_amount').max = dueAmount;
        document.getElementById('quick_payment_amount').value = dueAmount;
        
        // Show the modal
        var modal = new bootstrap.Modal(document.getElementById('quickPaymentModal'));
        modal.show();
    }
    
    // Validate payment amount
    document.getElementById('quick_payment_amount').addEventListener('input', function() {
        const maxAmount = parseFloat(this.max);
        const currentAmount = parseFloat(this.value) || 0;
        
        if (currentAmount > maxAmount) {
            this.value = maxAmount;
            alert('Payment amount cannot exceed due amount');
        }
    });
    
    // Form submission validation
    document.getElementById('quickPaymentForm').addEventListener('submit', function(e) {
        const paymentAmount = parseFloat(document.getElementById('quick_payment_amount').value) || 0;
        if (paymentAmount <= 0) {
            e.preventDefault();
            alert('Please enter a valid payment amount');
        }
    });
    </script>
</body>
</html>