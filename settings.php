<?php
require_once 'config.php';

if (!isAdmin()) {
    $_SESSION['error'] = "Access denied. Admin only.";
    redirect('dashboard.php');
}

// Update settings
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST as $key => $value) {
        if ($key != 'submit') {
            $value = mysqli_real_escape_string($conn, $value);
            mysqli_query($conn, "UPDATE system_settings SET setting_value = '$value' WHERE setting_key = '$key'");
        }
    }
    $_SESSION['success'] = "Settings updated successfully!";
    redirect('settings.php');
}

// Fetch settings
$settings = [];
$result = mysqli_query($conn, "SELECT * FROM system_settings");
while ($row = mysqli_fetch_assoc($result)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Bike Management System</title>
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

    <div class="container mt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">System Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Invoice Settings</h6>
                            <hr>
                            <div class="mb-3">
                                <label for="invoice_prefix" class="form-label">Invoice Prefix</label>
                                <input type="text" class="form-control" id="invoice_prefix" name="invoice_prefix" 
                                       value="<?php echo htmlspecialchars($settings['invoice_prefix']); ?>" required>
                                <small class="text-muted">Example: INV, SALE, BILL</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="invoice_format" class="form-label">Invoice Format Preview</label>
                                <div class="alert alert-info">
                                    <?php 
                                    $current_month = date('Ym');
                                    $next_number = intval($settings['invoice_last_number'] ?? 0) + 1;
                                    echo htmlspecialchars($settings['invoice_prefix']) . '-' . $current_month . '-' . str_pad($next_number, 5, '0', STR_PAD_LEFT);
                                    ?>
                                </div>
                                <small>Format: PREFIX-YYYYMM-00001</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-primary">Business Information</h6>
                            <hr>
                            <div class="mb-3">
                                <label for="business_name" class="form-label">Business Name</label>
                                <input type="text" class="form-control" id="business_name" name="business_name" 
                                       value="<?php echo htmlspecialchars($settings['business_name']); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="business_address" class="form-label">Business Address</label>
                                <textarea class="form-control" id="business_address" name="business_address" rows="2"><?php echo htmlspecialchars($settings['business_address']); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="business_phone" class="form-label">Business Phone</label>
                                <input type="text" class="form-control" id="business_phone" name="business_phone" 
                                       value="<?php echo htmlspecialchars($settings['business_phone']); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="business_email" class="form-label">Business Email</label>
                                <input type="email" class="form-control" id="business_email" name="business_email" 
                                       value="<?php echo htmlspecialchars($settings['business_email']); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="gst_number" class="form-label">GST Number</label>
                                <input type="text" class="form-control" id="gst_number" name="gst_number" 
                                       value="<?php echo htmlspecialchars($settings['gst_number']); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <button type="submit" name="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>