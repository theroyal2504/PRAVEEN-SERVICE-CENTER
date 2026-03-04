<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// Handle Add/Edit/Delete/Status Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_job'])) {
        $customer_id = $_POST['customer_id'] ?: 'NULL';
        $bike_model_id = $_POST['bike_model_id'];
        $job_description = mysqli_real_escape_string($conn, $_POST['job_description']);
        $estimated_cost = $_POST['estimated_cost'];
        $created_by = $_SESSION['user_id'];
        
        $query = "INSERT INTO pending_jobs (customer_id, bike_model_id, job_description, estimated_cost, created_by) 
                  VALUES ($customer_id, $bike_model_id, '$job_description', $estimated_cost, $created_by)";
        mysqli_query($conn, $query);
        
        $_SESSION['success'] = "Job added successfully!";
        redirect('jobs.php');
        
    } elseif (isset($_POST['update_status'])) {
        $job_id = $_POST['job_id'];
        $status = $_POST['status'];
        
        $query = "UPDATE pending_jobs SET status = '$status' WHERE id = $job_id";
        mysqli_query($conn, $query);
        
        $_SESSION['success'] = "Job status updated!";
        redirect('jobs.php');
        
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $query = "DELETE FROM pending_jobs WHERE id=$id";
        mysqli_query($conn, $query);
        
        $_SESSION['success'] = "Job deleted!";
        redirect('jobs.php');
    }
}

// Fetch data for dropdowns
$customers = mysqli_query($conn, "SELECT * FROM customers ORDER BY customer_name");
$bike_models = mysqli_query($conn, "SELECT m.*, c.name as company_name FROM bike_models m JOIN bike_companies c ON m.company_id = c.id ORDER BY c.name, m.model_name");

// Fetch jobs with details
$jobs = mysqli_query($conn, "SELECT j.*, 
                             c.customer_name, c.phone,
                             m.model_name, bm.name as company_name,
                             u.username
                             FROM pending_jobs j
                             LEFT JOIN customers c ON j.customer_id = c.id
                             LEFT JOIN bike_models m ON j.bike_model_id = m.id
                             LEFT JOIN bike_companies bm ON m.company_id = bm.id
                             LEFT JOIN users u ON j.created_by = u.id
                             ORDER BY 
                             CASE j.status
                                 WHEN 'pending' THEN 1
                                 WHEN 'in_progress' THEN 2
                                 WHEN 'completed' THEN 3
                             END, j.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Jobs - Bike Management System</title>
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

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Add New Job</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="customer_id" class="form-label">Customer (Optional)</label>
                                <select class="form-control" id="customer_id" name="customer_id">
                                    <option value="">Select Customer</option>
                                    <?php while($customer = mysqli_fetch_assoc($customers)): ?>
                                    <option value="<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['customer_name'] . ' - ' . $customer['phone']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="bike_model_id" class="form-label">Bike Model</label>
                                <select class="form-control" id="bike_model_id" name="bike_model_id" required>
                                    <option value="">Select Bike Model</option>
                                    <?php while($model = mysqli_fetch_assoc($bike_models)): ?>
                                    <option value="<?php echo $model['id']; ?>">
                                        <?php echo htmlspecialchars($model['company_name'] . ' ' . $model['model_name'] . ' (' . $model['year'] . ')'); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="job_description" class="form-label">Job Description</label>
                                <textarea class="form-control" id="job_description" name="job_description" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="estimated_cost" class="form-label">Estimated Cost (₹)</label>
                                <input type="number" step="0.01" class="form-control" id="estimated_cost" name="estimated_cost" required>
                            </div>
                            <button type="submit" name="add_job" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add Job
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Jobs List</h5>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs mb-3" id="jobTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button">Pending</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="inprogress-tab" data-bs-toggle="tab" data-bs-target="#inprogress" type="button">In Progress</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button">Completed</button>
                            </li>
                        </ul>
                        
                        <div class="tab-content">
                            <?php 
                            $pending_jobs = [];
                            $inprogress_jobs = [];
                            $completed_jobs = [];
                            
                            while($job = mysqli_fetch_assoc($jobs)) {
                                if ($job['status'] == 'pending') {
                                    $pending_jobs[] = $job;
                                } elseif ($job['status'] == 'in_progress') {
                                    $inprogress_jobs[] = $job;
                                } else {
                                    $completed_jobs[] = $job;
                                }
                            }
                            ?>
                            
                            <!-- Pending Jobs -->
                            <div class="tab-pane fade show active" id="pending">
                                <?php foreach($pending_jobs as $job): ?>
                                <div class="card mb-2 border-warning">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="card-title">
                                                    <?php echo htmlspecialchars($job['company_name'] . ' ' . $job['model_name']); ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                </h6>
                                                <p class="mb-1"><strong>Customer:</strong> <?php echo htmlspecialchars($job['customer_name'] ?? 'Not specified'); ?> (<?php echo htmlspecialchars($job['phone'] ?? 'N/A'); ?>)</p>
                                                <p class="mb-1"><strong>Job:</strong> <?php echo htmlspecialchars($job['job_description']); ?></p>
                                                <p class="mb-1"><strong>Estimate:</strong> ₹<?php echo number_format($job['estimated_cost'], 2); ?></p>
                                                <small class="text-muted">Created by: <?php echo htmlspecialchars($job['username']); ?> on <?php echo date('d-m-Y', strtotime($job['created_at'])); ?></small>
                                            </div>
                                            <div>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                                    <select name="status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                                        <option value="pending" <?php echo $job['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="in_progress">In Progress</option>
                                                        <option value="completed">Completed</option>
                                                    </select>
                                                    <input type="hidden" name="update_status" value="1">
                                                </form>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="id" value="<?php echo $job['id']; ?>">
                                                    <button type="submit" name="delete" class="btn btn-sm btn-danger" onclick="return confirm('Delete this job?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php if(empty($pending_jobs)): ?>
                                <p class="text-muted">No pending jobs</p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- In Progress Jobs -->
                            <div class="tab-pane fade" id="inprogress">
                                <?php foreach($inprogress_jobs as $job): ?>
                                <div class="card mb-2 border-primary">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="card-title">
                                                    <?php echo htmlspecialchars($job['company_name'] . ' ' . $job['model_name']); ?>
                                                    <span class="badge bg-primary">In Progress</span>
                                                </h6>
                                                <p class="mb-1"><strong>Customer:</strong> <?php echo htmlspecialchars($job['customer_name'] ?? 'Not specified'); ?> (<?php echo htmlspecialchars($job['phone'] ?? 'N/A'); ?>)</p>
                                                <p class="mb-1"><strong>Job:</strong> <?php echo htmlspecialchars($job['job_description']); ?></p>
                                                <p class="mb-1"><strong>Estimate:</strong> ₹<?php echo number_format($job['estimated_cost'], 2); ?></p>
                                                <small class="text-muted">Created by: <?php echo htmlspecialchars($job['username']); ?></small>
                                            </div>
                                            <div>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                                    <select name="status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                                        <option value="pending">Pending</option>
                                                        <option value="in_progress" <?php echo $job['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                                        <option value="completed">Completed</option>
                                                    </select>
                                                    <input type="hidden" name="update_status" value="1">
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php if(empty($inprogress_jobs)): ?>
                                <p class="text-muted">No jobs in progress</p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Completed Jobs -->
                            <div class="tab-pane fade" id="completed">
                                <?php foreach($completed_jobs as $job): ?>
                                <div class="card mb-2 border-success">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="card-title">
                                                    <?php echo htmlspecialchars($job['company_name'] . ' ' . $job['model_name']); ?>
                                                    <span class="badge bg-success">Completed</span>
                                                </h6>
                                                <p class="mb-1"><strong>Customer:</strong> <?php echo htmlspecialchars($job['customer_name'] ?? 'Not specified'); ?></p>
                                                <p class="mb-1"><strong>Job:</strong> <?php echo htmlspecialchars($job['job_description']); ?></p>
                                                <p class="mb-1"><strong>Amount:</strong> ₹<?php echo number_format($job['estimated_cost'], 2); ?></p>
                                                <small class="text-muted">Completed on: <?php echo date('d-m-Y', strtotime($job['created_at'])); ?></small>
                                            </div>
                                            <div>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                                    <select name="status" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                                        <option value="pending">Pending</option>
                                                        <option value="in_progress">In Progress</option>
                                                        <option value="completed" <?php echo $job['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                    </select>
                                                    <input type="hidden" name="update_status" value="1">
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php if(empty($completed_jobs)): ?>
                                <p class="text-muted">No completed jobs</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>