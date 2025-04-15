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
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Fetch offices for the filter dropdown
$offices_query = "SELECT id, office_name FROM offices";
$offices_result = $conn->query($offices_query);

// Fetch categories for the filter dropdown
$categories_query = "SELECT id, category_name FROM categories";
$categories_result = $conn->query($categories_query);

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
if (!empty($category_filter)) {
    $query .= " AND assets.category = ?";
    $params[] = $category_filter;
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
        <?php include 'include/sidebar.php'; ?>
        <div class="container-fluid">
        <?php include 'include/topbar.php'; ?>

            <div class="container mt-4">
                <h2 class="mb-4">Asset Reports</h2>

                <!-- Filter Form -->
                <form method="GET" class="mb-4">
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

                <!-- Export Trigger Buttons -->
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#templatesModal" data-export-type="csv">Export CSV</button>
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#templatesModal" data-export-type="pdf">Export PDF</button>


                <!-- Report Table -->
                <table id="assetsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
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
                                <td><?= $row['asset_name'] ?></td>
                                <td><?= $row['category_name'] ?></td>
                                <td><?= $row['description'] ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <td><?= $row['status'] ?></td>
                                <td><?= $row['office_name'] ?></td>
                                <td><?= date("M j, Y", strtotime($row['acquisition_date'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="templatesModal" tabindex="-1" aria-labelledby="templatesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="templatesModalLabel">Choose Template for Export</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="exportForm" method="GET">
                        <input type="hidden" name="export_type" id="exportTypeInput">
                        <input type="hidden" name="status" value="<?= $status_filter ?>">
                        <input type="hidden" name="start_date" value="<?= $start_date ?>">
                        <input type="hidden" name="end_date" value="<?= $end_date ?>">
                        <input type="hidden" name="office" value="<?= $office_filter ?>">
                        <input type="hidden" name="category" value="<?= $category_filter ?>">

                        <div class="list-group">
                            <label class="list-group-item">
                                <input class="form-check-input me-1" type="radio" name="template" value="template1" checked>
                                <strong>Template 1:</strong> Header: "Inventory Custodian Slip", Footer: "Signatories", Logo: logo1.png
                                <button type="button" class="btn btn-info btn-sm float-end" data-template="template1" data-bs-toggle="modal" data-bs-target="#viewTemplateModal">View</button>
                            </label>
                            <label class="list-group-item">
                                <input class="form-check-input me-1" type="radio" name="template" value="template2">
                                <strong>Template 2:</strong> Header: "Requisition and Issue Slip", Footer: "Signatories", Logo: logo2.png
                                <button type="button" class="btn btn-info btn-sm float-end" data-template="template2" data-bs-toggle="modal" data-bs-target="#viewTemplateModal">View</button>
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" form="exportForm" class="btn btn-primary">Proceed</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>



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