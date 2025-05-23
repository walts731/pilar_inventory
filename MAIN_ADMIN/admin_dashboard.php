<?php
session_start();
require '../connect.php'; // Database connection file

// Ensure only Office Admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Total Users
$totalUsersQuery = $conn->query("SELECT COUNT(*) as total_users FROM users");
$totalUsers = $totalUsersQuery->fetch_assoc()['total_users'];

// Total Assets
$totalAssetsQuery = $conn->query("SELECT COUNT(*) as total_assets FROM assets");
$totalAssets = $totalAssetsQuery->fetch_assoc()['total_assets'];

// Low Quantity Assets
$lowQuantityQuery = $conn->query("SELECT COUNT(*) as low_quantity FROM assets WHERE quantity < 10");
$lowQuantityAssets = $lowQuantityQuery->fetch_assoc()['low_quantity'];

// Red Tagged Assets
$redTaggedQuery = $conn->query("SELECT COUNT(*) as red_tagged FROM assets WHERE status = 'unserviceable'");
$redTaggedAssets = $redTaggedQuery->fetch_assoc()['red_tagged'];

// Fetch Categories
$categoriesQuery = $conn->query("SELECT id, category_name, type FROM categories");
$categories = $categoriesQuery->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <?php include '../includes/links.php'; ?>
</head>

<body>
    <div class="d-flex">
        <?php include 'include/sidebar.php'; ?>
        <div class="container-fluid p-4">
            <?php include 'include/topbar.php'; ?>

            <div class="row mt-3">
                <!-- Total Users -->
                <div class="col-md-3 mb-4">
                    <div class="card text-white border-primary shadow">
                        <div class="card-body text-primary">
                            <h5 class="card-title">Total Users</h5>
                            <h3><?php echo $totalUsers; ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Total Assets -->
                <div class="col-md-3 mb-4">
                    <div class="card text-white border-success shadow">
                        <div class="card-body text-success">
                            <h5 class="card-title">Total Assets</h5>
                            <h3><?php echo $totalAssets; ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Low Quantity Assets -->
                <div class="col-md-3 mb-4">
                    <div class="card text-white border-warning shadow">
                        <div class="card-body text-warning">
                            <h5 class="card-title">Low Quantity Assets</h5>
                            <h3><?php echo $lowQuantityAssets; ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Red Tagged Assets -->
                <div class="col-md-3 mb-4">
                    <div class="card text-white border-danger shadow">
                        <div class="card-body text-danger">
                            <h5 class="card-title">Red Tagged Assets</h5>
                            <h3><?php echo $redTaggedAssets; ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- LEFT COLUMN -->
                <div class="col-md-8 mb-4">
                    <!-- Recent Inventory Items -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">Recent Inventory Items</h5>
                                <a href="overall_inventory.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <ul class="list-group">
                                <?php
                                $recentAssetsQuery = $conn->query("SELECT asset_name, quantity, status, acquisition_date FROM assets ORDER BY acquisition_date DESC LIMIT 5");
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
                                <h5 class="card-title mb-0">Recent Borrow Requests</h5>
                                <a href="requests.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <ul class="list-group">
                                <?php
                                $recentRequestsQuery = $conn->query("
                                    SELECT br.request_date, br.status, a.asset_name, u.username 
                                    FROM borrow_requests br
                                    JOIN assets a ON br.asset_id = a.id
                                    JOIN users u ON br.user_id = u.id
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
                                <h5 class="card-title mb-0">Recent Returned Assets</h5>
                                <a href="returns.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <ul class="list-group">
                                <?php
                                $recentReturnsQuery = $conn->query("
                                    SELECT ra.return_date, a.asset_name, u.username 
                                    FROM returned_assets ra
                                    JOIN assets a ON ra.asset_id = a.id
                                    JOIN users u ON ra.user_id = u.id
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
                <div class="col-md-4 mb-4">
                    <!-- Assets by Category and Status -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Assets by Category</h5>
                            <?php foreach ($categories as $category): ?>
                                <?php
                                $categoryAssetsQuery = $conn->query("SELECT COUNT(*) as category_count FROM assets WHERE category = {$category['id']}");
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
                            <h5 class="card-title">Assets by Status</h5>
                            <?php
                            $statusQuery = $conn->query("SELECT status, COUNT(*) as status_count FROM assets GROUP BY status");
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
                                <h5 class="card-title mb-0">Recent Users</h5>
                                <a href="users.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <ul class="list-group">
                                <?php
                                $recentUsersQuery = $conn->query("SELECT username, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");
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
</body>
</html>
