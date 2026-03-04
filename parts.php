<?php
require_once 'config.php';

// Check admin authorization
checkAdminAuth();

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $part_number = mysqli_real_escape_string($conn, $_POST['part_number']);
        $part_name = mysqli_real_escape_string($conn, $_POST['part_name']);
        $category_id = $_POST['category_id'] ?: 'NULL';
        $company_id = $_POST['company_id'] ?: 'NULL';
        $model_id = $_POST['model_id'] ?: 'NULL';
        $unit_price = $_POST['unit_price'];
        
        $query = "INSERT INTO parts_master (part_number, part_name, category_id, company_id, model_id, unit_price) 
                  VALUES ('$part_number', '$part_name', $category_id, $company_id, $model_id, $unit_price)";
        mysqli_query($conn, $query);
        
        // Get the inserted part ID
        $part_id = mysqli_insert_id($conn);
        
        // Initialize stock for this part
        $stock_query = "INSERT INTO stock (part_id, quantity, min_stock_level) VALUES ($part_id, 0, 5)";
        mysqli_query($conn, $stock_query);
        
        $_SESSION['success'] = "Part added successfully!";
        redirect('parts.php');
        
    } elseif (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $part_number = mysqli_real_escape_string($conn, $_POST['part_number']);
        $part_name = mysqli_real_escape_string($conn, $_POST['part_name']);
        $category_id = $_POST['category_id'] ?: 'NULL';
        $company_id = $_POST['company_id'] ?: 'NULL';
        $model_id = $_POST['model_id'] ?: 'NULL';
        $unit_price = $_POST['unit_price'];
        
        $query = "UPDATE parts_master SET 
                  part_number='$part_number', 
                  part_name='$part_name', 
                  category_id=$category_id, 
                  company_id=$company_id, 
                  model_id=$model_id, 
                  unit_price=$unit_price 
                  WHERE id=$id";
        mysqli_query($conn, $query);
        
        $_SESSION['success'] = "Part updated successfully!";
        redirect('parts.php');
        
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $query = "DELETE FROM parts_master WHERE id=$id";
        mysqli_query($conn, $query);
        
        $_SESSION['success'] = "Part deleted successfully!";
        redirect('parts.php');
    }
}

// Fetch data for dropdowns
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");
$companies = mysqli_query($conn, "SELECT * FROM bike_companies ORDER BY name");
$models = mysqli_query($conn, "SELECT m.*, c.name as company_name FROM bike_models m JOIN bike_companies c ON m.company_id = c.id ORDER BY c.name, m.model_name");

// Fetch all parts with details
$parts = mysqli_query($conn, "SELECT p.*, 
                              c.category_name, 
                              bc.name as company_name, 
                              bm.model_name 
                              FROM parts_master p 
                              LEFT JOIN categories c ON p.category_id = c.id 
                              LEFT JOIN bike_companies bc ON p.company_id = bc.id 
                              LEFT JOIN bike_models bm ON p.model_id = bm.id 
                              ORDER BY p.part_number");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parts Master - Bike Management System</title>
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
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Add New Part</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="part_number" class="form-label">Part Number</label>
                                <input type="text" class="form-control" id="part_number" name="part_number" required>
                            </div>
                            <div class="mb-3">
                                <label for="part_name" class="form-label">Part Name</label>
                                <input type="text" class="form-control" id="part_name" name="part_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-control" id="category_id" name="category_id">
                                    <option value="">Select Category</option>
                                    <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                    <?php 
                                    endwhile; 
                                    mysqli_data_seek($categories, 0);
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="company_id" class="form-label">Company</label>
                                <select class="form-control" id="company_id" name="company_id">
                                    <option value="">Select Company</option>
                                    <?php while($company = mysqli_fetch_assoc($companies)): ?>
                                    <option value="<?php echo $company['id']; ?>"><?php echo htmlspecialchars($company['name']); ?></option>
                                    <?php 
                                    endwhile; 
                                    mysqli_data_seek($companies, 0);
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="model_id" class="form-label">Model</label>
                                <select class="form-control" id="model_id" name="model_id">
                                    <option value="">Select Model</option>
                                    <?php while($model = mysqli_fetch_assoc($models)): ?>
                                    <option value="<?php echo $model['id']; ?>"><?php echo htmlspecialchars($model['company_name'] . ' - ' . $model['model_name']); ?></option>
                                    <?php 
                                    endwhile; 
                                    mysqli_data_seek($models, 0);
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="unit_price" class="form-label">Unit Price</label>
                                <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price" required>
                            </div>
                            <button type="submit" name="add" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add Part
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Parts List</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Part #</th>
                                        <th>Part Name</th>
                                        <th>Category</th>
                                        <th>Company</th>
                                        <th>Model</th>
                                        <th>Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = mysqli_fetch_assoc($parts)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['part_number']); ?></td>
                                        <td><?php echo htmlspecialchars($row['part_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['category_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($row['company_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($row['model_name'] ?? 'N/A'); ?></td>
                                        <td>₹<?php echo number_format($row['unit_price'], 2); ?></td>
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
                                    </tr>
                                    
                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Part</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Part Number</label>
                                                            <input type="text" class="form-control" name="part_number" value="<?php echo htmlspecialchars($row['part_number']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Part Name</label>
                                                            <input type="text" class="form-control" name="part_name" value="<?php echo htmlspecialchars($row['part_name']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Category</label>
                                                            <select class="form-control" name="category_id">
                                                                <option value="">Select Category</option>
                                                                <?php 
                                                                mysqli_data_seek($categories, 0);
                                                                while($cat = mysqli_fetch_assoc($categories)): 
                                                                ?>
                                                                <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $row['category_id']) ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                                                </option>
                                                                <?php endwhile; ?>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Company</label>
                                                            <select class="form-control" name="company_id">
                                                                <option value="">Select Company</option>
                                                                <?php 
                                                                mysqli_data_seek($companies, 0);
                                                                while($company = mysqli_fetch_assoc($companies)): 
                                                                ?>
                                                                <option value="<?php echo $company['id']; ?>" <?php echo ($company['id'] == $row['company_id']) ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($company['name']); ?>
                                                                </option>
                                                                <?php endwhile; ?>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Model</label>
                                                            <select class="form-control" name="model_id">
                                                                <option value="">Select Model</option>
                                                                <?php 
                                                                mysqli_data_seek($models, 0);
                                                                while($model = mysqli_fetch_assoc($models)): 
                                                                ?>
                                                                <option value="<?php echo $model['id']; ?>" <?php echo ($model['id'] == $row['model_id']) ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($model['company_name'] . ' - ' . $model['model_name']); ?>
                                                                </option>
                                                                <?php endwhile; ?>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Unit Price</label>
                                                            <input type="number" step="0.01" class="form-control" name="unit_price" value="<?php echo $row['unit_price']; ?>" required>
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
</body>
</html>