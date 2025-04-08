<?php
session_start();
require '../connect.php'; // Database connection file

// Ensure only Super Admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
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
                    <div class="card text-white bg-primary shadow">
                        <div class="card-body">
                            <h5 class="card-title">Total Users</h5>
                            <h3><?php echo $totalUsers; ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Total Assets -->
                <div class="col-md-3 mb-4">
                    <div class="card text-white bg-success shadow">
                        <div class="card-body">
                            <h5 class="card-title">Total Assets</h5>
                            <h3><?php echo $totalAssets; ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Low Quantity Assets -->
                <div class="col-md-3 mb-4">
                    <div class="card text-white bg-warning shadow">
                        <div class="card-body">
                            <h5 class="card-title">Low Quantity Assets</h5>
                            <h3><?php echo $lowQuantityAssets; ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Red Tagged Assets -->
                <div class="col-md-3 mb-4">
                    <div class="card text-white bg-danger shadow">
                        <div class="card-body">
                            <h5 class="card-title">Red Tagged Assets</h5>
                            <h3><?php echo $redTaggedAssets; ?></h3>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Assets by Category (Dynamic Bar Pills) -->
            <div class="row">
                <div class="col-md-4 offset-md-8 mb-4">
                    <div class="card shadow">
                        <div class="card-body">
                            <h5 class="card-title">Assets by Category</h5>

                            <?php foreach ($categories as $category): ?>
                                <?php
                                // Count the assets by category
                                $categoryAssetsQuery = $conn->query("SELECT COUNT(*) as category_count FROM assets WHERE office_id = $officeId AND category = {$category['id']}");
                                $categoryAssets = $categoryAssetsQuery->fetch_assoc()['category_count'];

                                // Calculate the percentage of assets in this category
                                $categoryPercentage = $totalAssets > 0 ? ($categoryAssets / $totalAssets) * 100 : 0;
                                ?>

                                <!-- Bar Pill for Each Category -->
                                <div class="progress mb-3">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $categoryPercentage; ?>%" aria-valuenow="<?php echo $categoryPercentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?php echo htmlspecialchars($category['category_name']); ?> (<?php echo $categoryAssets; ?>)
                                    </div>
                                </div>

                            <?php endforeach; ?>
                            <hr>
                            <h5 class="card-title">Assets by Status</h5>

                            <?php
                            // Get unique statuses and their counts for assets in the same office
                            $statusQuery = $conn->query("SELECT status, COUNT(*) as status_count FROM assets WHERE office_id = $officeId GROUP BY status");
                            while ($statusRow = $statusQuery->fetch_assoc()):
                                $status = $statusRow['status'];
                                $count = $statusRow['status_count'];
                                $percentage = $totalAssets > 0 ? ($count / $totalAssets) * 100 : 0;

                                // Optional: color code per status (you can expand this as needed)
                                $statusColor = 'bg-secondary';
                                if ($status == 'working') $statusColor = 'bg-success';
                                elseif ($status == 'damaged') $statusColor = 'bg-warning';
                                elseif ($status == 'unserviceable') $statusColor = 'bg-danger';
                            ?>
                                <!-- Bar Pill for Each Status -->
                                <div class="progress mb-3">
                                    <div class="progress-bar <?php echo $statusColor; ?>" role="progressbar" style="width: <?php echo $percentage; ?>%" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?php echo ucfirst($status); ?> (<?php echo $count; ?>)
                                    </div>
                                </div>
                            <?php endwhile; ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/script.php'; ?>
</body>

</html>