<?php
session_start();
include('../connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Default filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$office_filter = isset($_GET['office']) ? $_GET['office'] : '';

// Fetch offices for the filter dropdown
$offices_query = "SELECT id, office_name FROM offices";
$offices_result = $conn->query($offices_query);

// Fetch filtered assets
$query = "SELECT assets.id, assets.asset_name, categories.category_name, assets.description, 
                assets.quantity, assets.status, offices.office_name, assets.acquisition_date 
          FROM assets 
          JOIN categories ON assets.category = categories.id 
          JOIN offices ON assets.office_id = offices.id 
          WHERE 1"; 

$params = [];
if (!empty($status_filter)) {
    $query .= " AND assets.status = ?";
    $params[] = $status_filter;
}
if (!empty($start_date) && !empty($end_date)) {
    $query .= " AND assets.acquisition_date BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
}
if (!empty($office_filter)) {
    $query .= " AND assets.office_id = ?";
    $params[] = $office_filter;
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Reports</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
</head>

<body>
    <div class="d-flex">
        <?php include '../includes/sidebar.php'; ?>
        <div class="container-fluid">
            <?php include '../includes/topbar.php'; ?>

            <div class="container mt-4">
                <h2 class="mb-4">Asset Reports</h2>

                <!-- Filter Form -->
                <form method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Status:</label>
                            <select name="status" class="form-control">
                                <option value="">All</option>
                                <option value="Available" <?= $status_filter == 'Available' ? 'selected' : '' ?>>Available</option>
                                <option value="Unserviceable" <?= $status_filter == 'Unserviceable' ? 'selected' : '' ?>>Unserviceable</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Office:</label>
                            <select name="office" class="form-control">
                                <option value="">All</option>
                                <?php while ($office = $offices_result->fetch_assoc()): ?>
                                    <option value="<?= $office['id'] ?>" <?= $office_filter == $office['id'] ? 'selected' : '' ?>><?= $office['office_name'] ?></option>
                                <?php endwhile; ?>
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
                        <div class="col-md-2 mt-4">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="reports.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>

                <!-- Export Buttons -->
                <div class="mb-3">
                    <a href="export_csv.php?status=<?= $status_filter ?>&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&office=<?= $office_filter ?>" class="btn btn-success">Export CSV</a>
                    <a href="export_pdf.php?status=<?= $status_filter ?>&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&office=<?= $office_filter ?>" class="btn btn-danger">Export PDF</a>
                </div>

                <!-- Report Table -->
                <table id="assetsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Asset Name</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Office</th>
                            <th>Acquisition Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= $row['asset_name'] ?></td>
                                <td><?= $row['category_name'] ?></td>
                                <td><?= $row['description'] ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <td><?= $row['status'] ?></td>
                                <td><?= $row['office_name'] ?></td>
                                <td><?= $row['acquisition_date'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <?php include '../includes/script.php'; ?>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#assetsTable').DataTable();
        });
    </script>
</body>

</html>
