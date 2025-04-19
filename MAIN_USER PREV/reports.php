<?php
session_start();
include('../connect.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Default filters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Fetch categories for filter dropdown
$categories_query = "SELECT id, category_name FROM categories";
$categories_result = $conn->query($categories_query);

// Build base query to fetch ALL assets (not limited to an office)
$query = "SELECT assets.id, assets.asset_name, categories.category_name, assets.description, 
                assets.quantity, assets.status, offices.office_name, assets.acquisition_date 
          FROM assets 
          JOIN categories ON assets.category = categories.id 
          JOIN offices ON assets.office_id = offices.id 
          WHERE 1"; // Always true, simplifies appending filters

// Prepare dynamic conditions
$params = [];
$types = "";

if (!empty($status_filter)) {
    $query .= " AND assets.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($category_filter)) {
    $query .= " AND assets.category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

if (!empty($start_date) && !empty($end_date)) {
    $query .= " AND assets.acquisition_date BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= "ss";
}

// Prepare and execute statement
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
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#templatesModal" data-export-type="pdf">Export PDF</button>
                </div>
            </div>
        </form>

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
                        <td><?= htmlspecialchars($row['asset_name']) ?></td>
                        <td><?= htmlspecialchars($row['category_name']) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td>
                            <span class="badge 
                                    <?php
                                    $status = strtolower($row['status']);
                                    echo match ($status) {
                                        'damaged' => 'bg-danger',
                                        'in use' => 'bg-primary',
                                        'unserviceable' => 'bg-secondary',
                                        'available' => 'bg-success',
                                        default => 'bg-light text-dark'
                                    };
                                    ?>">
                                <?= $row['status'] ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['office_name']) ?></td>
                        <td><?= date("M j, Y", strtotime($row['acquisition_date'])) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </div>

    <!-- Export Modal -->
    <div class="modal fade" id="templatesModal" tabindex="-1" aria-labelledby="templatesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="templatesModalLabel">Choose Template for Export</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="exportForm" action="export_csv.php" method="GET">
                        <input type="hidden" name="export_type" id="exportTypeInput">
                        <input type="hidden" name="status" value="<?= $status_filter ?>">
                        <input type="hidden" name="start_date" value="<?= $start_date ?>">
                        <input type="hidden" name="end_date" value="<?= $end_date ?>">
                        <input type="hidden" name="category" value="<?= $category_filter ?>">

                        <div class="list-group">
                            <label class="list-group-item">
                                <input class="form-check-input me-1" type="radio" name="template" value="template1" checked>
                                <strong>Template 1:</strong> Inventory Custodian Slip - logo1.png
                                <button type="button" class="btn btn-info btn-sm float-end" data-template="template1" data-bs-toggle="modal" data-bs-target="#viewTemplateModal">View</button>
                            </label>
                            <label class="list-group-item">
                                <input class="form-check-input me-1" type="radio" name="template" value="template2">
                                <strong>Template 2:</strong> Requisition and Issue Slip - logo2.png
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

    <script>
        // Export modal logic
        document.querySelectorAll('input[name="template"]').forEach(input => {
            input.addEventListener('change', () => {
                document.getElementById('exportTypeInput').value = input.value;
            });
        });

        const templatesModal = document.getElementById('templatesModal');
        templatesModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const exportType = button.getAttribute('data-export-type');
            document.getElementById('exportTypeInput').value = exportType;
            const exportForm = document.getElementById('exportForm');
            exportForm.action = exportType === 'csv' ? 'export_csv.php' : 'export_pdf.php';
        });
    </script>

    <?php include '../includes/script.php'; ?>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#assetsTable').DataTable();
        });
    </script>
</body>

</html>
