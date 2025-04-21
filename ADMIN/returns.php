<?php
session_start();
require '../connect.php'; // Database connection file
require '../include/log_activity.php'; // Include the logging function

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

// Fetch returned assets with joined details
$returnedAssetsQuery = $conn->query("
    SELECT ra.id, ra.borrow_request_id, ra.asset_id, ra.user_id, ra.return_date, ra.condition_on_return, ra.remarks, 
           ra.office_id, a.asset_name, u.username, o.office_name
    FROM returned_assets ra
    JOIN assets a ON ra.asset_id = a.id
    JOIN users u ON ra.user_id = u.id
    JOIN offices o ON ra.office_id = o.id
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Returned Assets</title>
    <?php include '../includes/links.php'; ?>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
</head>

<body>
    <div class="d-flex">
        <?php include 'include/sidebar.php'; ?>
        <div class="container-fluid p-4">
            <?php include 'include/topbar.php'; ?>

            <h3 class="mt-4 mb-3">Returned Assets</h3>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">List of Returned Assets</h5>
                </div>
                <div class="card-body">
                    <table class="table " id="returnedAssetsTable">
                        <thead>
                            <tr>
                                <th>Asset Name</th>
                                <th>User</th>
                                <th>Office</th>
                                <th>Return Date</th>
                                <th>Condition</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $returnedAssetsQuery->fetch_assoc()) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['asset_name']) ?></td>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= htmlspecialchars($row['office_name']) ?></td>
                                    <td><?= (new DateTime($row['return_date']))->format('M d, Y') ?></td>
                                    <td><?= htmlspecialchars($row['condition_on_return']) ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?= htmlspecialchars($row['remarks']) ?></span>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <?php include '../includes/script.php'; ?>

    <!-- jQuery & DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#returnedAssetsTable').DataTable();
        });
    </script>
</body>

</html>