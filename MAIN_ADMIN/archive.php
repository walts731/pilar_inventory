<?php
session_start();
include('../connect.php');

// Fetch offices and categories for filter dropdowns
$officeQuery = $conn->query("SELECT id, office_name FROM offices ORDER BY office_name");
$categoryQuery = $conn->query("SELECT id, category_name FROM categories ORDER BY category_name");

// Handle filters
$officeId = $_GET['office'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$status = $_GET['status'] ?? '';
$categoryId = $_GET['category'] ?? '';

// Base query
$sql = "SELECT a.*, o.office_name, u.username 
        FROM archives a 
        LEFT JOIN offices o ON a.filter_office = o.id
        LEFT JOIN users u ON a.user_id = u.id 
        WHERE 1=1";
$params = [];

if (!empty($officeId)) {
    $sql .= " AND a.filter_office = ?";
    $params[] = $officeId;
}
if (!empty($categoryId)) {
    $sql .= " AND a.filter_category = ?";
    $params[] = $categoryId;
}
if (!empty($status)) {
    $sql .= " AND a.filter_status = ?";
    $params[] = $status;
}
if (!empty($startDate) && !empty($endDate)) {
    $sql .= " AND DATE(a.created_at) BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
}

// Prepare and execute
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$records = [];

while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Archived Reports</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
</head>

<body>
<div class="d-flex">
    <?php include 'include/sidebar.php'; ?>
    <div class="container-fluid">
    <?php include 'include/topbar.php'; ?>

        <div class="container mt-5">
            <h3 class="mb-4">📁 Archive Logs</h3>

            <!-- Filter Form -->
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-2">
                        <select name="office" class="form-control">
                            <option value="">All Offices</option>
                            <?php while ($row = $officeQuery->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>" <?= ($officeId == $row['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['office_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="category" class="form-control">
                            <option value="">All Categories</option>
                            <?php while ($row = $categoryQuery->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>" <?= ($categoryId == $row['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['category_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" <?= $status == 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $status == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="damaged" <?= $status == 'damaged' ? 'selected' : '' ?>>Damaged</option>
                            <option value="disposed" <?= $status == 'disposed' ? 'selected' : '' ?>>Disposed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="form-control">
                    </div>
                    <div class="col-md-2 mt-2 mt-md-0">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>

            <?php if (empty($records)): ?>
                <p class="text-muted">No archived exports found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table id="archiveTable" class="table table-bordered table-striped">
                        <thead class="table-dark">
                        <tr>
                            <th>User</th>
                            <th>Office</th>
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
                        <?php foreach ($records as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['office_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['filter_status'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['filter_category'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['filter_start_date'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['filter_end_date'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['file_name']) ?></td>
                                <td>
                                    <a href="/exports/<?= urlencode($row['file_name']) ?>" 
                                       class="btn btn-sm btn-success" download>Download</a>
                                    
                                </td>
                                <td><?= date("F d, Y H:i:s", strtotime($row['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/script.php'; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('#archiveTable').DataTable();
    });
</script>
</body>
</html>
