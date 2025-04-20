<?php
session_start();
require '../connect.php'; // Database connection file

// Ensure only Super Admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'office_admin') {
    header('Location: index.php');
    exit();
}

// Get admin's office_id from users table
$adminId = $_SESSION['user_id'];
$officeQuery = $conn->query("SELECT office_id FROM users WHERE id = $adminId");
$officeRow = $officeQuery->fetch_assoc();
$officeId = $officeRow['office_id'];

// Total Users in the same office
$totalUsersQuery = $conn->query("SELECT COUNT(*) as total_users FROM users WHERE office_id = $officeId");
$totalUsers = $totalUsersQuery->fetch_assoc()['total_users'];

// Total Assets in the same office
$totalAssetsQuery = $conn->query("SELECT COUNT(*) as total_assets FROM assets WHERE office_id = $officeId");
$totalAssets = $totalAssetsQuery->fetch_assoc()['total_assets'];

// Low Quantity Assets (quantity less than 10) in the same office
$lowQuantityQuery = $conn->query("SELECT COUNT(*) as low_quantity FROM assets WHERE office_id = $officeId AND quantity < 10");
$lowQuantityAssets = $lowQuantityQuery->fetch_assoc()['low_quantity'];

// Red Tagged Assets (status = 'unserviceable') in the same office
$redTaggedQuery = $conn->query("SELECT COUNT(*) as red_tagged FROM assets WHERE office_id = $officeId AND status = 'unserviceable'");
$redTaggedAssets = $redTaggedQuery->fetch_assoc()['red_tagged'];

// Fetch categories from the 'categories' table
$categoriesQuery = $conn->query("SELECT id, category_name, type FROM categories");
$categories = $categoriesQuery->fetch_all(MYSQLI_ASSOC); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include '../includes/links.php'; ?>
    <style>
        .list-group-item small {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .card .card-title {
            font-weight: 600;
        }
    </style>

</head>

<body>
    <div class="d-flex">
        <?php include 'include/sidebar.php'; ?>
        <div class="container-fluid p-4">
            <?php include 'include/topbar.php'; ?>

            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mt-3">
                <!-- Total Users -->
                <div class="col">
                    <div class="card border-primary shadow h-100">
                        <div class="card-body text-primary">
                            <h5 class="card-title">
                                <i class="fas fa-users"></i> Total Users
                            </h5>
                            <h3><?php echo $totalUsers; ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Total Assets -->
                <div class="col">
                    <div class="card border-success shadow h-100">
                        <div class="card-body text-success">
                            <h5 class="card-title">
                                <i class="fas fa-boxes"></i> Total Assets
                            </h5>
                            <h3><?php echo $totalAssets; ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Low Quantity Assets -->
                <div class="col">
                    <div class="card border-warning shadow h-100">
                        <div class="card-body text-warning">
                            <h5 class="card-title">
                                <i class="fas fa-exclamation-triangle"></i> Low Quantity Assets
                            </h5>
                            <h3><?php echo $lowQuantityAssets; ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Red Tagged Assets -->
                <div class="col">
                    <div class="card border-danger shadow h-100">
                        <div class="card-body text-danger">
                            <h5 class="card-title">
                                <i class="fas fa-ban"></i> Red Tagged Assets
                            </h5>
                            <h3><?php echo $redTaggedAssets; ?></h3>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row g-4 mt-2">
                <!-- LEFT COLUMN -->
                <div class="col-12 col-lg-8"> <!-- Recent Inventory Items -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-box-open me-2"></i> Recent Inventory Items
                                </h5>
                                <a href="assets.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i> View All
                                </a>
                            </div>


                            <ul class="list-group">
                                <?php
                                $recentAssetsQuery = $conn->query("SELECT asset_name, quantity, status, acquisition_date FROM assets WHERE office_id = $officeId ORDER BY acquisition_date DESC LIMIT 5");

                                while ($asset = $recentAssetsQuery->fetch_assoc()):
                                ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($asset['asset_name']); ?></strong><br>
                                            <small>Status: <?php echo ucfirst($asset['status']); ?> | Quantity: <?php echo $asset['quantity']; ?></small>
                                        </div>
                                        <span class="badge bg-info text-dark"><?php echo date('M d, Y', strtotime($asset['acquisition_date'])); ?></span>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- Recent Borrow Requests -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-clipboard-list me-2"></i> Recent Borrow Requests
                                </h5>
                                <a href="request.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i> View All
                                </a>
                            </div>

                            <ul class="list-group">
                                <?php
                                $recentRequestsQuery = $conn->query("
                        SELECT br.request_date, br.status, a.asset_name, u.username 
                        FROM borrow_requests br
                        JOIN assets a ON br.asset_id = a.id
                        JOIN users u ON br.user_id = u.id
                        WHERE br.office_id = $officeId
                        ORDER BY br.request_date DESC
                        LIMIT 5
                    ");

                                if ($recentRequestsQuery->num_rows > 0):
                                    while ($request = $recentRequestsQuery->fetch_assoc()):
                                ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo htmlspecialchars($request['asset_name']); ?></strong><br>
                                                <small>Requested by: <?php echo htmlspecialchars($request['username']); ?> | Status: <?php echo ucfirst($request['status']); ?></small>
                                            </div>
                                            <span class="badge bg-secondary text-light"><?php echo date('M d, Y', strtotime($request['request_date'])); ?></span>
                                        </li>
                                <?php
                                    endwhile;
                                else:
                                    echo '<li class="list-group-item">No recent borrow requests.</li>';
                                endif;
                                ?>
                            </ul>
                        </div>
                    </div>

                    <!-- Recent Returned Assets -->
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-undo-alt me-2"></i> Recent Returned Assets
                                </h5>
                                <a href="returns.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i> View All
                                </a>
                            </div>

                            <ul class="list-group">
                                <?php
                                $recentReturnsQuery = $conn->query("
                        SELECT ra.return_date, a.asset_name, u.username 
                        FROM returned_assets ra
                        JOIN assets a ON ra.asset_id = a.id
                        JOIN users u ON ra.user_id = u.id
                        WHERE ra.office_id = $officeId
                        ORDER BY ra.return_date DESC
                        LIMIT 5
                    ");

                                if ($recentReturnsQuery->num_rows > 0):
                                    while ($return = $recentReturnsQuery->fetch_assoc()):
                                ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo htmlspecialchars($return['asset_name']); ?></strong><br>
                                                <small>Returned by: <?php echo htmlspecialchars($return['username']); ?></small>
                                            </div>
                                            <span class="badge bg-success"><?php echo date('M d, Y', strtotime($return['return_date'])); ?></span>
                                        </li>
                                <?php
                                    endwhile;
                                else:
                                    echo '<li class="list-group-item">No recent returned assets.</li>';
                                endif;
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="col-12 col-lg-4">
                    <!-- Assets by Category and Status -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-chart-pie me-2"></i> Assets by Category
                            </h5>

                            <?php foreach ($categories as $category): ?>
                                <?php
                                $categoryAssetsQuery = $conn->query("SELECT COUNT(*) as category_count FROM assets WHERE office_id = $officeId AND category = {$category['id']}");
                                $categoryAssets = $categoryAssetsQuery->fetch_assoc()['category_count'];
                                $categoryPercentage = $totalAssets > 0 ? ($categoryAssets / $totalAssets) * 100 : 0;
                                ?>
                                <div class="progress mb-3">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $categoryPercentage; ?>%" aria-valuenow="<?php echo $categoryPercentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?php echo htmlspecialchars($category['category_name']); ?> (<?php echo $categoryAssets; ?>)
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <hr>
                            <h5 class="card-title">
                                <i class="fas fa-cogs me-2"></i> Assets by Status
                            </h5>
                            <?php
                            $statusQuery = $conn->query("SELECT status, COUNT(*) as status_count FROM assets WHERE office_id = $officeId GROUP BY status");
                            while ($statusRow = $statusQuery->fetch_assoc()):
                                $status = $statusRow['status'];
                                $count = $statusRow['status_count'];
                                $percentage = $totalAssets > 0 ? ($count / $totalAssets) * 100 : 0;

                                $statusColor = 'bg-secondary';
                                if ($status == 'working') $statusColor = 'bg-success';
                                elseif ($status == 'damaged') $statusColor = 'bg-warning';
                                elseif ($status == 'unserviceable') $statusColor = 'bg-danger';
                            ?>
                                <div class="progress mb-3">
                                    <div class="progress-bar <?php echo $statusColor; ?>" role="progressbar" style="width: <?php echo $percentage; ?>%" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?php echo ucfirst($status); ?> (<?php echo $count; ?>)
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- Recent Users -->
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-users me-2"></i> Recent Users
                                </h5>
                                <a href="users.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i> View All
                                </a>
                            </div>

                            <ul class="list-group">
                                <?php
                                $recentUsersQuery = $conn->query("SELECT username, role, created_at FROM users WHERE office_id = $officeId ORDER BY created_at DESC LIMIT 5");

                                if ($recentUsersQuery->num_rows > 0):
                                    while ($user = $recentUsersQuery->fetch_assoc()):
                                ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo htmlspecialchars($user['username']); ?></strong><br>
                                                <small>Role: <?php echo ucfirst($user['role']); ?></small>
                                            </div>
                                            <span class="badge bg-primary"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                                        </li>
                                <?php
                                    endwhile;
                                else:
                                    echo '<li class="list-group-item">No recent users.</li>';
                                endif;
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php include '../includes/script.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>