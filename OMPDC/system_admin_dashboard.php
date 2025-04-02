<?php
session_start();
require '../connect.php'; // Database connection file

// Ensure only Super Admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header('Location: login.php');
    exit();
}

// Fetch summary data
$total_assets_query = $conn->query("SELECT COUNT(*) AS total FROM assets");
$total_assets = $total_assets_query->fetch_assoc()['total'] ?? 0;

$pending_requests_query = $conn->query("SELECT COUNT(*) AS total FROM asset_requests WHERE status='pending'");
$pending_requests = $pending_requests_query->fetch_assoc()['total'] ?? 0;

$red_tagged_assets_query = $conn->query("SELECT COUNT(*) AS total FROM assets WHERE status='red-tagged'");
$red_tagged_assets = $red_tagged_assets_query->fetch_assoc()['total'] ?? 0;

$low_stock_assets_query = $conn->query("SELECT COUNT(*) AS total FROM assets WHERE stock <= 5");
$low_stock_assets = $low_stock_assets_query->fetch_assoc()['total'] ?? 0;

// Fetch latest activities
$activities_result = $conn->query("SELECT * FROM activity_log ORDER BY timestamp DESC LIMIT 5");
$activities = $activities_result->fetch_all(MYSQLI_ASSOC);

// Fetch users
$users_result = $conn->query("SELECT * FROM users");
$users = $users_result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="d-flex">
    <!-- Include Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="container-fluid p-4">
        <!-- Include Sidebar -->
    <?php include '../includes/topbar.php'; ?>
        <div class="row mt-3">
            <!-- Summary Cards -->
            <div class="col-md-3">
                <div class="card text-bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Assets</h5>
                        <p class="card-text"><?php echo $total_assets; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Pending Requests</h5>
                        <p class="card-text"><?php echo $pending_requests; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-bg-danger mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Red-Tagged Assets</h5>
                        <p class="card-text"><?php echo $red_tagged_assets; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-bg-secondary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Low Stock Assets</h5>
                        <p class="card-text"><?php echo $low_stock_assets; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <h3>Recent Activities</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Activity</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($activities as $activity): ?>
                <tr>
                    <td><?php echo htmlspecialchars($activity['activity']); ?></td>
                    <td><?php echo htmlspecialchars($activity['timestamp']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- User Management -->
        <h3>Manage Users</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td>
                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-warning">Edit</a>
                        <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-danger">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</div>

</body>
</html>


<?php include '../includes/script.php'; ?>
