<?php
session_start();
include('../connect.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$adminId = $_SESSION['user_id'];

// Fetch categories for the filter dropdown
$categories_query = "SELECT id, category_name FROM categories";
$categories_result = $conn->query($categories_query);

// Default filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Fetch filtered assets query without office-based filtering
$query = "SELECT assets.id, assets.asset_name, categories.category_name, assets.description, 
                assets.quantity, assets.status, assets.acquisition_date 
          FROM assets 
          JOIN categories ON assets.category = categories.id";

// Apply filters if provided
if (!empty($status_filter)) {
    $query .= " WHERE assets.status = ?";
}

if (!empty($category_filter)) {
    $query .= (!empty($status_filter) ? " AND" : " WHERE") . " assets.category = ?";
}

if (!empty($start_date) && !empty($end_date)) {
    $query .= (!empty($status_filter) || !empty($category_filter) ? " AND" : " WHERE") . " assets.acquisition_date BETWEEN ? AND ?";
}

// Prepare parameters based on applied filters
$params = [];
$types = '';

if (!empty($status_filter)) {
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($category_filter)) {
    $params[] = $category_filter;
    $types .= "s";
}

if (!empty($start_date) && !empty($end_date)) {
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= "ss";
}

// Prepare and execute the statement
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
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
                    <table id="assetsTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Asset Name</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Status</th>
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
                                    <td><?= date("M j, Y", strtotime($row['acquisition_date'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#assetsTable').DataTable();
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
</body>

</html>
