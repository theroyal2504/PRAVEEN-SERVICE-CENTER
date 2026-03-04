<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// Fetch bike models for dropdown
$bike_models = mysqli_query($conn, "SELECT m.*, c.name as company_name 
                                    FROM bike_models m 
                                    JOIN bike_companies c ON m.company_id = c.id 
                                    ORDER BY c.name, m.model_name");

// Only admin can add/edit/delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isAdmin()) {
        $_SESSION['error'] = "Access denied. Only admin can modify customers.";
        redirect('customers.php');
    }
    
    if (isset($_POST['add'])) {
        $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $vehicle_registration = strtoupper(mysqli_real_escape_string($conn, $_POST['vehicle_registration']));
        $vehicle_model_id = $_POST['vehicle_model_id'] ?: 'NULL';
        $vehicle_company = mysqli_real_escape_string($conn, $_POST['vehicle_company']);
        $vehicle_year = $_POST['vehicle_year'] ?: 'NULL';
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        
        $query = "INSERT INTO customers (customer_name, phone, vehicle_registration, vehicle_model_id, vehicle_company, vehicle_year, email, address) 
                  VALUES ('$customer_name', '$phone', '$vehicle_registration', $vehicle_model_id, '$vehicle_company', $vehicle_year, '$email', '$address')";
        mysqli_query($conn, $query);
        $_SESSION['success'] = "Customer added successfully!";
        redirect('customers.php');
        
    } elseif (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $vehicle_registration = strtoupper(mysqli_real_escape_string($conn, $_POST['vehicle_registration']));
        $vehicle_model_id = $_POST['vehicle_model_id'] ?: 'NULL';
        $vehicle_company = mysqli_real_escape_string($conn, $_POST['vehicle_company']);
        $vehicle_year = $_POST['vehicle_year'] ?: 'NULL';
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        
        $query = "UPDATE customers SET 
                  customer_name='$customer_name', 
                  phone='$phone',
                  vehicle_registration='$vehicle_registration',
                  vehicle_model_id=$vehicle_model_id,
                  vehicle_company='$vehicle_company',
                  vehicle_year=$vehicle_year,
                  email='$email', 
                  address='$address' 
                  WHERE id=$id";
        mysqli_query($conn, $query);
        $_SESSION['success'] = "Customer updated successfully!";
        redirect('customers.php');
        
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $query = "DELETE FROM customers WHERE id=$id";
        mysqli_query($conn, $query);
        $_SESSION['success'] = "Customer deleted successfully!";
        redirect('customers.php');
    }
}

// Fetch all customers with vehicle details
$customers = mysqli_query($conn, "SELECT c.*, bm.model_name, bc.name as company_name 
                                  FROM customers c
                                  LEFT JOIN bike_models bm ON c.vehicle_model_id = bm.id
                                  LEFT JOIN bike_companies bc ON bm.company_id = bc.id
                                  ORDER BY c.customer_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Bike Management System</title>
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
                    <?php if(isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                    </li>
                    <?php endif; ?>
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

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php if (isAdmin()): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Add New Customer</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="customerForm">
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">Customer Name *</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="text" class="form-control" id="phone" name="phone" required>
                            </div>
                            
                            <div class="card mb-3 bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0">Vehicle Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="vehicle_registration" class="form-label">Vehicle Registration Number</label>
                                        <input type="text" class="form-control" id="vehicle_registration" name="vehicle_registration" 
                                               placeholder="e.g., MH12AB1234" style="text-transform:uppercase">
                                        <small class="text-muted">Enter in uppercase format</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="vehicle_model_id" class="form-label">Select Bike Model</label>
                                        <select class="form-control" id="vehicle_model_id" name="vehicle_model_id" onchange="updateVehicleDetails()">
                                            <option value="">Select Model (Optional)</option>
                                            <?php 
                                            mysqli_data_seek($bike_models, 0);
                                            while($model = mysqli_fetch_assoc($bike_models)): 
                                            ?>
                                            <option value="<?php echo $model['id']; ?>" 
                                                    data-company="<?php echo htmlspecialchars($model['company_name']); ?>"
                                                    data-year="<?php echo $model['year']; ?>">
                                                <?php echo htmlspecialchars($model['company_name'] . ' ' . $model['model_name'] . ' (' . $model['year'] . ')'); ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="vehicle_company" class="form-label">Vehicle Company</label>
                                                <input type="text" class="form-control" id="vehicle_company" name="vehicle_company" readonly>
                                                <small class="text-muted">Auto-filled from model</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="vehicle_year" class="form-label">Vehicle Year</label>
                                                <input type="number" class="form-control" id="vehicle_year" name="vehicle_year" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                            </div>
                            
                            <button type="submit" name="add" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add Customer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
            <?php else: ?>
            <div class="col-md-12">
            <?php endif; ?>
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Customers List</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer Name</th>
                                        <th>Phone</th>
                                        <th>Vehicle Reg No.</th>
                                        <th>Vehicle Details</th>
                                        <th>Email</th>
                                        <th>Address</th>
                                        <?php if (isAdmin()): ?>
                                        <th>Actions</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = mysqli_fetch_assoc($customers)): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                        <td>
                                            <?php if($row['vehicle_registration']): ?>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($row['vehicle_registration']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($row['company_name']): ?>
                                                <?php echo htmlspecialchars($row['company_name'] . ' ' . $row['model_name']); ?>
                                                <br><small class="text-muted"><?php echo $row['vehicle_year']; ?></small>
                                            <?php elseif($row['vehicle_company']): ?>
                                                <?php echo htmlspecialchars($row['vehicle_company']); ?>
                                                <br><small class="text-muted"><?php echo $row['vehicle_year']; ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">No vehicle</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                                        <?php if (isAdmin()): ?>
                                        <td>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                    
                                    <?php if (isAdmin()): ?>
                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Customer</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Customer Name *</label>
                                                            <input type="text" class="form-control" name="customer_name" value="<?php echo htmlspecialchars($row['customer_name']); ?>" required>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Phone Number *</label>
                                                            <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($row['phone']); ?>" required>
                                                        </div>
                                                        
                                                        <div class="card mb-3 bg-light">
                                                            <div class="card-header">
                                                                <h6 class="mb-0">Vehicle Information</h6>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Vehicle Registration Number</label>
                                                                    <input type="text" class="form-control" name="vehicle_registration" 
                                                                           value="<?php echo htmlspecialchars($row['vehicle_registration']); ?>"
                                                                           style="text-transform:uppercase">
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">Select Bike Model</label>
                                                                    <select class="form-control" name="vehicle_model_id" onchange="updateEditVehicleDetails(this)">
                                                                        <option value="">Select Model (Optional)</option>
                                                                        <?php 
                                                                        mysqli_data_seek($bike_models, 0);
                                                                        while($model = mysqli_fetch_assoc($bike_models)): 
                                                                        ?>
                                                                        <option value="<?php echo $model['id']; ?>" 
                                                                                data-company="<?php echo htmlspecialchars($model['company_name']); ?>"
                                                                                data-year="<?php echo $model['year']; ?>"
                                                                                <?php echo ($model['id'] == $row['vehicle_model_id']) ? 'selected' : ''; ?>>
                                                                            <?php echo htmlspecialchars($model['company_name'] . ' ' . $model['model_name'] . ' (' . $model['year'] . ')'); ?>
                                                                        </option>
                                                                        <?php endwhile; ?>
                                                                    </select>
                                                                </div>
                                                                
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Vehicle Company</label>
                                                                            <input type="text" class="form-control" name="vehicle_company" 
                                                                                   value="<?php echo htmlspecialchars($row['vehicle_company']); ?>">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Vehicle Year</label>
                                                                            <input type="number" class="form-control" name="vehicle_year" 
                                                                                   value="<?php echo $row['vehicle_year']; ?>">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Email</label>
                                                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($row['email']); ?>">
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Address</label>
                                                            <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($row['address']); ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="edit" class="btn btn-primary">Update</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function updateVehicleDetails() {
        const select = document.getElementById('vehicle_model_id');
        const selected = select.options[select.selectedIndex];
        
        if (selected.value) {
            document.getElementById('vehicle_company').value = selected.dataset.company || '';
            document.getElementById('vehicle_year').value = selected.dataset.year || '';
        } else {
            document.getElementById('vehicle_company').value = '';
            document.getElementById('vehicle_year').value = '';
        }
    }
    
    function updateEditVehicleDetails(select) {
        const row = select.closest('.modal-body');
        const selected = select.options[select.selectedIndex];
        const companyInput = row.querySelector('input[name="vehicle_company"]');
        const yearInput = row.querySelector('input[name="vehicle_year"]');
        
        if (selected.value) {
            companyInput.value = selected.dataset.company || '';
            yearInput.value = selected.dataset.year || '';
        }
    }
    
    // Auto-uppercase for registration number
    document.getElementById('vehicle_registration').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>