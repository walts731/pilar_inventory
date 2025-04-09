<?php
session_start();
require '../connect.php'; // Database connection file

// Ensure only user role can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header('Location: index.php');
    exit();
}

// Get user's info
$adminId = $_SESSION['user_id'];
$userQuery = $conn->query("SELECT fullname, office_id FROM users WHERE id = $adminId");
$userRow = $userQuery->fetch_assoc();
$officeId = $userRow['office_id'];
$fullName = $userRow['fullname'];

// Get recent inventory and reports
$recentInventoryQuery = $conn->query("SELECT * FROM assets WHERE office_id = $officeId ORDER BY last_updated DESC LIMIT 5");
$recentReportsQuery = $conn->query("SELECT * FROM archives WHERE filter_office = $officeId ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <?php include '../includes/links.php'; ?>
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .dashboard-left,
        .dashboard-right {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .card {
            background: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin-bottom: 20px;
        }

        .card h3 {
            margin-bottom: 15px;
        }

        .card ul {
            padding-left: 20px;
            list-style: none;
        }

        .card a.btn {
            padding: 10px 20px;
            border-radius: 8px;
            background: #007bff;
            color: #fff;
            text-decoration: none;
            display: block;
            margin-top: 15px;
            text-align: center;
        }

        .card a.btn:hover {
            background: #0056b3;
        }

        .button-container {
            display: flex;
            gap: 20px;
            /* Adjust spacing between the buttons */
            justify-content: flex-start;
            /* Align buttons to the left */
        }

        .button-wrapper {
            flex: 1;
            /* Optional: Makes the buttons take equal space */
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-left">
            <h2>Welcome, <?php echo htmlspecialchars($fullName); ?>!</h2>

            <!-- Add New Asset -->
            <div class="card">
                <h3>Add New Asset</h3>
                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addAssetModal">
                    <i class="fas fa-plus-circle"></i> Add Asset
                </button>
            </div>

            <!-- Generate Reports -->
            <div class="card">
                <h3>Generate Reports</h3>
                <p>Download and view reports of your office's asset records.</p>

                <div class="button-container">
                    <!-- Button for Export CSV -->
                    <div class="button-wrapper">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#templatesModal" data-export-type="csv">Export CSV</button>
                    </div>

                    <!-- Button for Export PDF -->
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
            <?php
            // Modify your query to join the assets table with the categories table
            $query = "SELECT assets.asset_name, categories.category_name, assets.quantity, assets.last_updated
                      FROM assets
                      JOIN categories ON assets.category = categories.id
                      WHERE assets.office_id = $officeId
                      ORDER BY assets.last_updated DESC LIMIT 5"; // Ensure LIMIT is added for recent assets
            $recentInventoryQuery = $conn->query($query);

            while ($inventory = $recentInventoryQuery->fetch_assoc()) {
                // Format the last_updated date to 'Feb 24, 2004'
                $formattedDate = date("M d, Y", strtotime($inventory['last_updated']));
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($inventory['asset_name']); ?></td>
                    <td>
    <span class="badge bg-primary"><?php echo htmlspecialchars($inventory['category_name']); ?></span>
</td>
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
                    <?php while ($report = $recentReportsQuery->fetch_assoc()) { ?>
                        <li>
                            <?php
                            // Format the created_at date to 'Feb 24, 2004'
                            $formattedDate = date("M d, Y", strtotime($report['created_at']));
                            ?>
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