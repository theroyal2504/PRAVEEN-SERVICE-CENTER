<?php
require_once 'config.php';

// Check admin authorization
checkAdminAuth();

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $company_id = $_POST['company_id'];
        $model_name = mysqli_real_escape_string($conn, $_POST['model_name']);
        $year = $_POST['year'];
        $engine_cc = $_POST['engine_cc'];
        
        $query = "INSERT INTO bike_models (company_id, model_name, year, engine_cc) VALUES ($company_id, '$model_name', $year, $engine_cc)";
        mysqli_query($conn, $query);
        $_SESSION['success'] = "Model added successfully!";
        redirect('models.php');
        
    } elseif (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $company_id = $_POST['company_id'];
        $model_name = mysqli_real_escape_string($conn, $_POST['model_name']);
        $year = $_POST['year'];
        $engine_cc = $_POST['engine_cc'];
        
        $query = "UPDATE bike_models SET company_id=$company_id, model_name='$model_name', year=$year, engine_cc=$engine_cc WHERE id=$id";
        mysqli_query($conn, $query);
        $_SESSION['success'] = "Model updated successfully!";
        redirect('models.php');
        
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $query = "DELETE FROM bike_models WHERE id=$id";
        mysqli_query($conn, $query);
        $_SESSION['success'] = "Model deleted successfully!";
        redirect('models.php');
    }
}

// Fetch companies for dropdown
$companies = mysqli_query($conn, "SELECT * FROM bike_companies ORDER BY name");

// Fetch all models with company names
$models = mysqli_query($conn, "SELECT m.*, c.name as company_name FROM bike_models m JOIN bike_companies c ON m.company_id = c.id ORDER BY c.name, m.model_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bike Models - Bike Management System</title>
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
                        <h5 class="mb-0">Add New Model</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="company_id" class="form-label">Company</label>
                                <select class="form-control" id="company_id" name="company_id" required>
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
                                <label for="model_name" class="form-label">Model Name</label>
                                <input type="text" class="form-control" id="model_name" name="model_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="year" class="form-label">Year</label>
                                <input type="number" class="form-control" id="year" name="year" min="1900" max="2024" required>
                            </div>
                            <div class="mb-3">
                                <label for="engine_cc" class="form-label">Engine CC</label>
                                <input type="number" class="form-control" id="engine_cc" name="engine_cc" required>
                            </div>
                            <button type="submit" name="add" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add Model
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Bike Models List</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Company</th>
                                    <th>Model Name</th>
                                    <th>Year</th>
                                    <th>Engine CC</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($models)): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['model_name']); ?></td>
                                    <td><?php echo $row['year']; ?></td>
                                    <td><?php echo $row['engine_cc']; ?> CC</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                
                                <!-- Edit Modal -->
                                <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Model</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Company</label>
                                                        <select class="form-control" name="company_id" required>
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
                                                        <label class="form-label">Model Name</label>
                                                        <input type="text" class="form-control" name="model_name" value="<?php echo htmlspecialchars($row['model_name']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Year</label>
                                                        <input type="number" class="form-control" name="year" value="<?php echo $row['year']; ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Engine CC</label>
                                                        <input type="number" class="form-control" name="engine_cc" value="<?php echo $row['engine_cc']; ?>" required>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>