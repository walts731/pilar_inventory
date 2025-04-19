<?php
session_start();
require '../connect.php'; // Database connection file

// Ensure only user role can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header('Location: index.php');
    exit();
}

// Get user's info (only the full name is needed now)
$adminId = $_SESSION['user_id'];
$userQuery = $conn->query("SELECT fullname FROM users WHERE id = $adminId");
$userRow = $userQuery->fetch_assoc();
$fullName = $userRow['fullname'];

// Get recent inventory and reports (no office filtering)
$recentInventoryQuery = $conn->query("SELECT assets.asset_name, categories.category_name, assets.quantity, assets.last_updated
    FROM assets
    JOIN categories ON assets.category = categories.id
    ORDER BY assets.last_updated DESC LIMIT 5");

$recentReportsQuery = $conn->query("SELECT * FROM archives ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../css/user.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-left">
            <h2>Welcome, <?php echo htmlspecialchars($fullName); ?>!</h2>

            <!-- Generate Reports -->
            <div class="card">
                <h3>Generate Reports</h3>
                <p>Download and view reports of all office asset records.</p>
                <div class="button-container">
                    <div class="button-wrapper">
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#templatesModal" data-export-type="pdf">Export PDF</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-right">
            <!-- Recent Inventory -->
            <div class="card">
                <h3>Recent Added Inventory</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Asset Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Added On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($inventory = $recentInventoryQuery->fetch_assoc()) {
                            $formattedDate = date("M d, Y", strtotime($inventory['last_updated']));
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($inventory['asset_name']); ?></td>
                                <td><span class="badge bg-primary"><?php echo htmlspecialchars($inventory['category_name']); ?></span></td>
                                <td><?php echo htmlspecialchars($inventory['quantity']); ?></td>
                                <td><?php echo $formattedDate; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Generated Reports -->
            <div class="card">
                <h3>Recent Generated Reports</h3>
                <ul>
                    <?php while ($report = $recentReportsQuery->fetch_assoc()) {
                        $formattedDate = date("M d, Y", strtotime($report['created_at']));
                    ?>
                        <li>
                            <?php echo htmlspecialchars($report['file_name']); ?> (<?php echo $formattedDate; ?>)
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Add Asset Modal -->
    <?php include '../ADMIN/include/add_asset_modal.php'; ?>
</body>

</html>
