<?php
require_once 'config.php';

// Check admin authorization
checkAdminAuth();

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add category from modal on this page
    if (isset($_POST['add_category'])) {
        $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        if (!empty($category_name)) {
            $query = "INSERT INTO categories (category_name, description) VALUES ('$category_name', '$description')";
            if (mysqli_query($conn, $query)) {
                $_SESSION['success'] = "Category added successfully!";
            } else {
                $_SESSION['error'] = "Error adding category: " . mysqli_error($conn);
            }
        }
        redirect('parts.php');
    }

    if (isset($_POST['add'])) {
        $part_number = mysqli_real_escape_string($conn, $_POST['part_number']);
        $part_name = mysqli_real_escape_string($conn, $_POST['part_name']);
        $category_id = $_POST['category_id'] ?: 'NULL';
        $company_id = $_POST['company_id'] ?: 'NULL';
        $model_id = $_POST['model_id'] ?: 'NULL';
        $unit_price = $_POST['unit_price'];
        
        // Check if part number already exists
        $check_query = "SELECT id FROM parts_master WHERE part_number = '$part_number'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $_SESSION['error'] = "Part number already exists!";
            redirect('parts.php');
        }
        
        $query = "INSERT INTO parts_master (part_number, part_name, category_id, company_id, model_id, unit_price) 
                  VALUES ('$part_number', '$part_name', $category_id, $company_id, $model_id, $unit_price)";
        
        if (mysqli_query($conn, $query)) {
            // Get the inserted part ID
            $part_id = mysqli_insert_id($conn);
            
            // Initialize stock for this part
            $stock_query = "INSERT INTO stock (part_id, quantity, min_stock_level) VALUES ($part_id, 0, 5)";
            mysqli_query($conn, $stock_query);
            
            $_SESSION['success'] = "Part added successfully!";
        } else {
            $_SESSION['error'] = "Error adding part: " . mysqli_error($conn);
        }
        
        redirect('parts.php');
        
    } elseif (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $part_number = mysqli_real_escape_string($conn, $_POST['part_number']);
        $part_name = mysqli_real_escape_string($conn, $_POST['part_name']);
        $category_id = $_POST['category_id'] ?: 'NULL';
        $company_id = $_POST['company_id'] ?: 'NULL';
        $model_id = $_POST['model_id'] ?: 'NULL';
        $unit_price = $_POST['unit_price'];
        
        // Check if part number already exists for another part
        $check_query = "SELECT id FROM parts_master WHERE part_number = '$part_number' AND id != $id";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $_SESSION['error'] = "Part number already exists for another part!";
            redirect('parts.php');
        }
        
        $query = "UPDATE parts_master SET 
                  part_number='$part_number', 
                  part_name='$part_name', 
                  category_id=$category_id, 
                  company_id=$company_id, 
                  model_id=$model_id, 
                  unit_price=$unit_price 
                  WHERE id=$id";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Part updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating part: " . mysqli_error($conn);
        }
        
        redirect('parts.php');
        
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        
        // Check if part is used in any transactions
        $check_sales = mysqli_query($conn, "SELECT id FROM sale_items WHERE part_id = $id LIMIT 1");
        $check_purchases = mysqli_query($conn, "SELECT id FROM purchase_items WHERE part_id = $id LIMIT 1");
        
        if (mysqli_num_rows($check_sales) > 0 || mysqli_num_rows($check_purchases) > 0) {
            $_SESSION['error'] = "Cannot delete part because it has transaction history!";
            redirect('parts.php');
        }
        
        $query = "DELETE FROM parts_master WHERE id=$id";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Part deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting part: " . mysqli_error($conn);
        }
        
        redirect('parts.php');
    }
}

// Fetch data for dropdowns
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");
$companies = mysqli_query($conn, "SELECT * FROM bike_companies ORDER BY name");
$models = mysqli_query($conn, "SELECT m.*, c.name as company_name FROM bike_models m JOIN bike_companies c ON m.company_id = c.id ORDER BY c.name, m.model_name");

// Get selected category from GET parameter for filtering
$selected_category_id = $_GET['category'] ?? null;
$category_filter = '';
if ($selected_category_id) {
    $category_filter = "AND p.category_id = " . intval($selected_category_id);
}

// Fetch all parts with details (optionally filtered by category)
$parts = mysqli_query($conn, "SELECT p.*, 
                              c.category_name, 
                              bc.name as company_name, 
                              bm.model_name 
                              FROM parts_master p 
                              LEFT JOIN categories c ON p.category_id = c.id 
                              LEFT JOIN bike_companies bc ON p.company_id = bc.id 
                              LEFT JOIN bike_models bm ON p.model_id = bm.id 
                              WHERE 1=1 $category_filter
                              ORDER BY c.category_name, p.part_number");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parts Master - Bike Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .category-sidebar {
            max-height: 600px;
            overflow-y: auto;
            border-right: 2px solid #dee2e6;
            padding-right: 15px;
        }
        .category-item {
            display: block;
            padding: 10px 12px;
            margin-bottom: 5px;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .category-item:hover {
            background-color: #f0f0f0;
            color: #007bff;
            border-left-color: #007bff;
        }
        .category-item.active {
            background-color: #007bff;
            color: white;
            border-left-color: #0056b3;
            font-weight: bold;
        }
        .category-count {
            float: right;
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.85em;
        }
        .category-item.active .category-count {
            background: rgba(255,255,255,0.3);
        }
    </style>
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
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Add New Part</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="parts.php">
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
                                    <?php 
                                    mysqli_data_seek($categories, 0);
                                    while($cat = mysqli_fetch_assoc($categories)): 
                                    ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                    <?php 
                                    endwhile; 
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label d-block">&nbsp;</label>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                    <i class="bi bi-plus"></i> Add New Category
                                </button>
                            </div>
                            <div class="mb-3">
                                <label for="company_id" class="form-label">Company</label>
                                <select class="form-control" id="company_id" name="company_id">
                                    <option value="">Select Company</option>
                                    <?php 
                                    mysqli_data_seek($companies, 0);
                                    while($company = mysqli_fetch_assoc($companies)): 
                                    ?>
                                    <option value="<?php echo $company['id']; ?>"><?php echo htmlspecialchars($company['name']); ?></option>
                                    <?php 
                                    endwhile; 
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="model_id" class="form-label">Model</label>
                                <select class="form-control" id="model_id" name="model_id">
                                    <option value="">Select Model</option>
                                    <?php 
                                    mysqli_data_seek($models, 0);
                                    while($model = mysqli_fetch_assoc($models)): 
                                    ?>
                                    <option value="<?php echo $model['id']; ?>"><?php echo htmlspecialchars($model['company_name'] . ' - ' . $model['model_name']); ?></option>
                                    <?php 
                                    endwhile; 
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="unit_price" class="form-label">Unit Price (₹)</label>
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
                        <h5 class="mb-0">Parts List 
                            <?php if ($selected_category_id): ?>
                                <span class="badge bg-primary">Filtered by Category</span>
                                <a href="parts.php" class="btn btn-sm btn-outline-secondary ms-2"><i class="bi bi-x"></i> Clear Filter</a>
                            <?php endif; ?>
                        </h5>
                    </div>
                    
                    <!-- Category Filter Sidebar -->
                    <div class="card-body pb-0 mb-3">
                        <h6 class="mb-3"><i class="bi bi-tags"></i> Filter by Category</h6>
                        <div class="category-sidebar">
                            <a href="parts.php" class="category-item <?php echo !$selected_category_id ? 'active' : ''; ?>">
                                <i class="bi bi-funnel"></i> All Parts
                                <span class="category-count"><?php echo mysqli_num_rows(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM parts_master")); ?></span>
                            </a>
                            <?php 
                            mysqli_data_seek($categories, 0);
                            while($cat = mysqli_fetch_assoc($categories)): 
                                // Count parts in this category
                                $count_result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM parts_master WHERE category_id = " . $cat['id']);
                                $count_row = mysqli_fetch_assoc($count_result);
                                $count = $count_row['cnt'] ?? 0;
                            ?>
                            <a href="parts.php?category=<?php echo $cat['id']; ?>" class="category-item <?php echo ($selected_category_id == $cat['id']) ? 'active' : ''; ?>">
                                <i class="bi bi-folder"></i> <?php echo htmlspecialchars($cat['category_name']); ?>
                                <span class="category-count"><?php echo $count; ?></span>
                            </a>
                            <?php endwhile; ?>
                        </div>
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
                                    <?php if (mysqli_num_rows($parts) > 0): ?>
                                        <?php while($row = mysqli_fetch_assoc($parts)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['part_number']); ?></td>
                                            <td><?php echo htmlspecialchars($row['part_name']); ?></td>
                                            <td>
                                                <?php if($row['category_name']): ?>
                                                    <span class="badge bg-info"><?php echo htmlspecialchars($row['category_name']); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['company_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['model_name'] ?? 'N/A'); ?></td>
                                            <td>₹<?php echo number_format($row['unit_price'], 2); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this part?');">
                                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" name="delete" class="btn btn-sm btn-danger">
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
                                                        <h5 class="modal-title">Edit Part - <?php echo htmlspecialchars($row['part_name']); ?></h5>
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
                                                                <label class="form-label">Unit Price (₹)</label>
                                                                <input type="number" step="0.01" class="form-control" name="unit_price" value="<?php echo $row['unit_price']; ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="edit" class="btn btn-primary">Update Part</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No parts found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Category Modal (Separate from main form) -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="parts.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" class="form-control" name="category_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>