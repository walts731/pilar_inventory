<?php
session_start();
include('../connect.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$adminId = $_SESSION['user_id'];
$officeId = 0;

// Fetch the admin's office_id securely
$officeResult = $conn->query("SELECT office_id FROM users WHERE id = $adminId");
if ($officeResult && $officeResult->num_rows > 0) {
    $officeRow = $officeResult->fetch_assoc();
    $officeId = $officeRow['office_id'];
}

// Fetch the logged-in user's office ID
$user_id = $_SESSION['user_id'];
$user_query = "SELECT office_id FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$office_id = $user['office_id'];


// Default filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Fetch categories for the filter dropdown
$categories_query = "SELECT id, category_name FROM categories";
$categories_result = $conn->query($categories_query);

// Fetch filtered assets query
$query = "SELECT assets.id, assets.asset_name, categories.category_name, assets.description, 
                assets.quantity, assets.status, offices.office_name, assets.acquisition_date 
          FROM assets 
          JOIN categories ON assets.category = categories.id 
          JOIN offices ON assets.office_id = offices.id 
          WHERE assets.office_id = ?"; // Filter by office

// Prepare parameters
$params = [$office_id];
$types = "i"; // The first parameter is an integer (office_id)

if (!empty($status_filter)) {
    $query .= " AND assets.status = ?";
    $params[] = $status_filter;
    $types .= "s"; // Adding a string for the status filter
}

if (!empty($category_filter)) {
    $query .= " AND assets.category = ?";
    $params[] = $category_filter;
    $types .= "s"; // Adding a string for the category filter
}

if (!empty($start_date) && !empty($end_date)) {
    $query .= " AND assets.acquisition_date BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= "ss"; // Adding two strings for start and end dates
}

// Prepare and execute the statement
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/user.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container mt-4">
        <h2 class="mb-4">Asset Reports</h2>

        <!-- Filter Form -->
        <form id="filterForm" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-2">
                    <label>Category:</label>
                    <select name="category" class="form-control">
                        <option value="">All</option>
                        <?php while ($category = $categories_result->fetch_assoc()): ?>
                            <option value="<?= $category['id'] ?>" <?= $category_filter == $category['id'] ? 'selected' : '' ?>>
                                <?= $category['category_name'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Status:</label>
                    <select name="status" class="form-control">
                        <option value="">All</option>
                        <option value="Available" <?= $status_filter == 'Available' ? 'selected' : '' ?>>Available</option>
                        <option value="Unserviceable" <?= $status_filter == 'Unserviceable' ? 'selected' : '' ?>>Unserviceable</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Start Date:</label>
                    <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
                </div>
                <div class="col-md-2">
                    <label>End Date:</label>
                    <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
                </div>
                <div class="col-md-4 mt-4">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="reports.php" class="btn btn-secondary">Reset</a>
                    <a href="export_pdf.php?<?= http_build_query($_GET) ?>" target="_blank" class="btn btn-danger">Export PDF</a>
                </div>
            </div>
        </form>

        <!-- Report Table Card -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="assetsTable" class="table table-striped table-hover"> <!-- Removed table-bordered -->
                        <thead>
                            <tr>
                                <th>Asset Name</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Status</th>
                                <th hidden>Office</th> <!-- Hidden column for Office -->
                                <th>Acquisition Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['asset_name'] ?></td>
                                    <td><?= $row['category_name'] ?></td>
                                    <td><?= $row['description'] ?></td>
                                    <td><?= $row['quantity'] ?></td>
                                    <td>
                                        <span class="badge 
                                        <?php
                                        if ($row['status'] == 'damaged') {
                                            echo 'bg-danger';
                                        } elseif ($row['status'] == 'in use') {
                                            echo 'bg-primary';
                                        } elseif ($row['status'] == 'unserviceable') {
                                            echo 'bg-secondary';
                                        } elseif ($row['status'] == 'available') {
                                            echo 'bg-success';
                                        }
                                        ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td hidden><?= $row['office_name'] ?></td>
                                    <td><?= date("M j, Y", strtotime($row['acquisition_date'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>

    <script>
        // Update the export_type input based on the selected template
        document.querySelectorAll('input[name="template"]').forEach(function(input) {
            input.addEventListener('change', function() {
                // Set the export type based on the selected template
                document.getElementById('exportTypeInput').value = this.value;
            });
        });

        // Trigger the export process when the Proceed button is clicked
        document.querySelector('button[type="submit"]').addEventListener('click', function() {
            // You can add any additional checks here if needed
            document.getElementById('exportForm').submit(); // Submit the form to export_csv.php
        });

        document.addEventListener("DOMContentLoaded", function() {
            const filterForm = document.getElementById("filterForm");

            // Auto-submit on dropdown and date change
            filterForm.querySelectorAll("select, input[type='date']").forEach(function(element) {
                element.addEventListener("change", function() {
                    filterForm.submit();
                });
            });
        });
    </script>


    <?php include '../includes/script.php'; ?>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#assetsTable').DataTable();
        });

        let exportForm = document.getElementById('exportForm');
        let exportTypeInput = document.getElementById('exportTypeInput');
        const templatesModal = document.getElementById('templatesModal');

        templatesModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const exportType = button.getAttribute('data-export-type');
            exportTypeInput.value = exportType;

            // Set form action based on export type
            exportForm.action = exportType === 'csv' ? 'export_csv.php' : 'export_pdf.php';
        });
    </script>
</body>

</html>