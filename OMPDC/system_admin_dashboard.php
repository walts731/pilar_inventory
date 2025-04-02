<?php
session_start();
require '../connect.php'; // Database connection file

// Ensure only Super Admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header('Location: index.php');
    exit();
}

// Fetch summary data using prepared statements
function fetch_count($conn, $query, $param_types = '', $params = [])
{
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total'] ?? 0;
}

$total_assets = fetch_count($conn, "SELECT COUNT(*) AS total FROM assets");
$pending_requests = fetch_count($conn, "SELECT COUNT(*) AS total FROM asset_requests WHERE status='pending'");
$red_tagged_assets = fetch_count($conn, "SELECT COUNT(*) AS total FROM assets WHERE status='red-tagged'");
$low_stock_assets = fetch_count($conn, "SELECT COUNT(*) AS total FROM assets WHERE quantity <= 5");

// Fetch recent activities (actions related to inventory such as adding, borrowing, transferring)
$activities_query = "
    SELECT u.username, a.activity, a.module, a.timestamp 
    FROM activity_log a
    JOIN users u ON a.user_id = u.id
    ORDER BY a.timestamp DESC LIMIT 5";


$activities = $conn->query($activities_query)->fetch_all(MYSQLI_ASSOC);

// Fetch users
$users_query = "SELECT * FROM users";
$users = $conn->query($users_query)->fetch_all(MYSQLI_ASSOC);

// Fetch recent inventory actions (e.g., adding inventory, borrowing, transferring)
$recent_inventory_actions_query = "
    SELECT action_name, category, quantity, action_date 
    FROM inventory_actions 
    ORDER BY action_date DESC LIMIT 5";

$recent_inventory_actions = $conn->query($recent_inventory_actions_query)->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Admin Dashboard</title>
    <?php include '../includes/links.php'; ?>
</head>

<body>
    <div class="d-flex">
        <?php include '../includes/sidebar.php'; ?>
        <div class="container-fluid p-4">
            <?php include '../includes/topbar.php'; ?>
            <div class="row mt-3">
                <!-- Asset-based summary cards -->
                <?php
                $cards = [
                    ['title' => 'Total Assets', 'count' => $total_assets, 'class' => 'primary'],
                    ['title' => 'Pending Requests', 'count' => $pending_requests, 'class' => 'warning'],
                    ['title' => 'Red-Tagged Assets', 'count' => $red_tagged_assets, 'class' => 'danger'],
                    ['title' => 'Low Stock Assets', 'count' => $low_stock_assets, 'class' => 'secondary']
                ];

                foreach ($cards as $card): ?>
                    <div class="col-md-3">
                        <div class="card text-bg-<?php echo $card['class']; ?> mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $card['title']; ?></h5>
                                <p class="card-text"><?php echo $card['count']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Recent Activities</h5>
                        </div>
                        <div class="card-body p-3">
                            <table class="table table-striped mb-0" id="activitiesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Module</th>
                                        <th>Activity</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activities as $activity): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($activity['username']); ?></td>
                                            <td><?php echo isset($activity['module']) ? htmlspecialchars($activity['module']) : 'N/A'; ?></td>
                                            <td><?php echo htmlspecialchars($activity['activity']); ?></td>
                                            <td><?php echo (new DateTime($activity['timestamp']))->format('M d, Y h:i A'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Recent Inventory Actions</h5>
                        </div>
                        <div class="card-body p-3">
                            <table class="table table-striped mb-0" id="inventoryActionsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Action Name</th>
                                        <th>Category</th>
                                        <th>Quantity</th>
                                        <th>Action Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_inventory_actions as $action): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($action['action_name']); ?></td>
                                            <td><?php echo htmlspecialchars($action['category']); ?></td>
                                            <td><?php echo htmlspecialchars($action['quantity']); ?></td>
                                            <td><?php echo (new DateTime($action['action_date']))->format('M d, Y h:i A'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/script.php'; ?>


</body>

</html>