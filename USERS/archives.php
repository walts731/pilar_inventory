<?php
session_start();
require '../connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'office_user') {
    header('Location: index.php');
    exit();
}

$adminId = $_SESSION['user_id'];
$userQuery = $conn->query("SELECT fullname, office_id FROM users WHERE id = $adminId");
$userRow = $userQuery->fetch_assoc();
$officeId = $userRow['office_id'];
$fullName = $userRow['fullname'];

$archivesQuery = $conn->query("SELECT user_id, filter_status, filter_category, filter_start_date, filter_end_date, file_name, action_type, created_at FROM archives WHERE filter_office = $officeId");

// Get distinct status and category options
$statusOptions = [];
$categoryOptions = [];

$statusQuery = $conn->query("SELECT DISTINCT filter_status FROM archives WHERE filter_office = $officeId AND filter_status IS NOT NULL");
while ($row = $statusQuery->fetch_assoc()) {
    $statusOptions[] = $row['filter_status'];
}

$categoryQuery = $conn->query("SELECT DISTINCT filter_category FROM archives WHERE filter_office = $officeId AND filter_category IS NOT NULL");
while ($row = $categoryQuery->fetch_assoc()) {
    $categoryOptions[] = $row['filter_category'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archives</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../css/user.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <style>
        @media (max-width: 768px) {
            .card {
                margin: 0 auto;
                width: 100%;
                border-radius: 0.5rem;
            }

            .card-body {
                padding: 0.5rem;
            }

            table.dataTable {
                font-size: 0.75rem;
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            img {
                max-width: 100%;
                height: auto;
            }
        }

        @media (min-width: 992px) {
            .card {
                max-height: 80vh;
                overflow-y: auto;
            }
        }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Archives for Your Office</h2>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-lg-3 col-md-6 col-sm-12 mb-2">
            <label for="statusFilter">Status</label>
            <select id="statusFilter" class="form-control">
                <option value="">All</option>
                <?php foreach ($statusOptions as $status): ?>
                    <option value="<?= htmlspecialchars($status) ?>"><?= htmlspecialchars($status) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-12 mb-2">
            <label for="categoryFilter">Category</label>
            <select id="categoryFilter" class="form-control">
                <option value="">All</option>
                <?php foreach ($categoryOptions as $category): ?>
                    <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-12 mb-2">
            <label for="startDateFilter">Start Date</label>
            <input type="date" id="startDateFilter" class="form-control">
        </div>

        <div class="col-lg-3 col-md-6 col-sm-12 mb-2">
            <label for="endDateFilter">End Date</label>
            <input type="date" id="endDateFilter" class="form-control">
        </div>
    </div>

    <div class="card shadow rounded w-100">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="archiveTable" class="table table-bordered table-striped w-100">
                    <thead class="table-light">
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
                                <td><a href='{$filePath}' download class='btn btn-primary btn-sm'>Download</a></td>
                                <td>" . date('M j, Y', strtotime($archive['created_at'])) . "</td>
                              </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center'>No archives found for your office.</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<?php include '../includes/script.php'; ?>

<script>
    $(document).ready(function () {
        var table = $('#archiveTable').DataTable({
            responsive: true
        });

        $('#statusFilter').on('change', function () {
            table.column(1).search(this.value).draw();
        });

        $('#categoryFilter').on('change', function () {
            table.column(2).search(this.value).draw();
        });

        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            let start = $('#startDateFilter').val();
            let end = $('#endDateFilter').val();
            let exportedDate = new Date(data[7]);

            if (!start && !end) return true;
            if (start && exportedDate < new Date(start)) return false;
            if (end && exportedDate > new Date(end)) return false;

            return true;
        });

        $('#startDateFilter, #endDateFilter').on('change', function () {
            table.draw();
        });
    });
</script>
</body>
</html>
