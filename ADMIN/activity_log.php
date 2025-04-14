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

// Fetch the office name based on office_id
$officeNameQuery = $conn->query("SELECT office_name FROM offices WHERE id = $officeId");
$officeNameRow = $officeNameQuery->fetch_assoc();
$officeName = $officeNameRow['office_name'];

// Fetch activity logs based on office_id with user fullname and office name
$logsQuery = $conn->query("
    SELECT sl.id, u.fullname, o.office_name, sl.module, sl.action, sl.ip_address, sl.datetime 
    FROM system_logs sl
    JOIN users u ON sl.user_id = u.id
    JOIN offices o ON sl.office_id = o.id
    WHERE sl.office_id = $officeId
    ORDER BY sl.datetime DESC
");

// Fetch distinct modules for the filter dropdown
$modulesQuery = $conn->query("SELECT DISTINCT module FROM system_logs WHERE office_id = $officeId");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs</title>
    <?php include '../includes/links.php'; ?>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>

<body>
    <div class="d-flex">
        <?php include 'include/sidebar.php'; ?>
        <div class="container-fluid p-4">
            <?php include 'include/topbar.php'; ?>

            <!-- Display office name dynamically in the heading -->
            <h2>Activity Logs - <?php echo $officeName; ?></h2>

            <!-- Module Filter Dropdown -->
            <div class="mb-3">
                <label for="moduleFilter">Filter by Module:</label>
                <select id="moduleFilter" class="form-control" style="width: 200px;">
                    <option value="">All</option>
                    <?php while ($moduleRow = $modulesQuery->fetch_assoc()): ?>
                        <option value="<?php echo $moduleRow['module']; ?>"><?php echo $moduleRow['module']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Card for activity logs -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Activity Logs</h5>
                </div>
                <div class="card-body">
                    <table id="activityLogs" class="table">
                        <thead>
                            <tr>
                                <th>Fullname</th>
                                <th>Module</th>
                                <th>Action</th>
                                <th>IP Address</th>
                                <th>Date/Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($log = $logsQuery->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $log['fullname']; ?></td>
                                    <td><?php echo $log['module']; ?></td>
                                    <td><?php echo $log['action']; ?></td>
                                    <td><?php echo $log['ip_address']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($log['datetime'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <?php include '../includes/script.php'; ?>

    <!-- DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <!-- Initialize DataTables -->
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#activityLogs').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true
            });

            // Filter module functionality
            $('#moduleFilter').on('change', function() {
                var filterValue = this.value;
                if (filterValue) {
                    table.column(1).search('^' + filterValue + '$', true, false).draw(); // Filter by exact module name
                } else {
                    table.column(1).search('').draw(); // Clear filter
                }
            });
        });
    </script>
</body>

</html>
