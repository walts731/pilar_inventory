<?php
session_start();
require '../connect.php'; // Database connection file
require 'include/log_activity.php'; // Include the logging function

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

// Fetch archives related to the admin's office_id
$archivesQuery = $conn->query("SELECT user_id, filter_status, filter_category, filter_start_date, filter_end_date, file_name, action_type, created_at FROM archives WHERE filter_office = $officeId");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archives</title>
    <?php include '../includes/links.php'; ?>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>

<body>
    <div class="d-flex">
        <?php include 'include/sidebar.php'; ?>
        <div class="container-fluid p-4">
            <?php include 'include/topbar.php'; ?>

            <h3>Archives for Your Office</h3>

            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Status</label>
                    <input type="text" id="statusFilter" class="form-control" placeholder="Filter by status">
                </div>
                <div class="col-md-3">
                    <label>Category</label>
                    <input type="text" id="categoryFilter" class="form-control" placeholder="Filter by category">
                </div>
                <div class="col-md-3">
                    <label>Start Date</label>
                    <input type="date" id="startDateFilter" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>End Date</label>
                    <input type="date" id="endDateFilter" class="form-control">
                </div>
            </div>

            <!-- Table -->
            <table class="table table-bordered" id="archiveTable">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Status</th>
                        <th>Category</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>File Name</th>
                        <th>Action</th>
                        <th>Date Exported</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($archivesQuery->num_rows > 0) {
                        while ($archive = $archivesQuery->fetch_assoc()) {
                            $userQuery = $conn->query("SELECT fullname FROM users WHERE id = {$archive['user_id']}");
                            $userRow = $userQuery->fetch_assoc();
                            $userFullName = $userRow['fullname'];
                            $filePath = '../uploads/' . $archive['file_name'];

                            echo "<tr>
                                <td>{$userFullName}</td>
                                <td>{$archive['filter_status']}</td>
                                <td>{$archive['filter_category']}</td>
                                <td>" . (!empty($archive['filter_start_date']) ? date('M j, Y', strtotime($archive['filter_start_date'])) : '-') . "</td>
                                <td>" . (!empty($archive['filter_end_date']) ? date('M j, Y', strtotime($archive['filter_end_date'])) : '-') . "</td>
                                <td>{$archive['file_name']}</td>
                                <td><a href='{$filePath}' download class='btn btn-primary'>Download</a></td>
                                <td>" . date('M j, Y', strtotime($archive['created_at'])) . "</td>
                              </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8'>No archives found for your office.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

        </div>
    </div>

    <!-- jQuery + DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <?php include '../includes/script.php'; ?>

    <script>
        $(document).ready(function() {
            var table = $('#archiveTable').DataTable();

            // Filter by Status
            $('#statusFilter').on('keyup change', function() {
                table.column(1).search(this.value).draw();
            });

            // Filter by Category
            $('#categoryFilter').on('keyup change', function() {
                table.column(2).search(this.value).draw();
            });

            // Filter by Date Exported Range
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let start = $('#startDateFilter').val();
                let end = $('#endDateFilter').val();
                let exportedDate = new Date(data[7]);

                if (!start && !end) return true;

                if (start && exportedDate < new Date(start)) return false;
                if (end && exportedDate > new Date(end)) return false;

                return true;
            });

            $('#startDateFilter, #endDateFilter').on('change', function() {
                table.draw();
            });
        });
    </script>
</body>

</html>