<?php
session_start();
require '../connect.php';
require 'include/log_activity.php'; // Include the logging function

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'office_admin') {
    header('Location: index.php');
    exit();
}

$adminId = $_SESSION['user_id'];
$officeQuery = $conn->query("SELECT office_id FROM users WHERE id = $adminId");
$officeRow = $officeQuery->fetch_assoc();
$officeId = $officeRow['office_id'];

// Handle borrow request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow'])) {
    $assetId = $_POST['asset_id'];
    $requestDate = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO borrow_requests (asset_id, user_id, office_id, request_date, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iiis", $assetId, $adminId, $officeId, $requestDate);

    if ($stmt->execute()) {
        $message = "Borrow request submitted!";
    } else {
        $error = "Error: " . $conn->error;
    }
}

// Handle office filter
$selectedOffice = isset($_GET['filter_office']) ? (int)$_GET['filter_office'] : 0;

// Get list of offices (excluding your own)
$offices = $conn->query("SELECT id, office_name FROM offices WHERE id != $officeId");

// Build query for assets
$assetQuery = "
    SELECT a.*, o.office_name 
    FROM assets a 
    JOIN offices o ON a.office_id = o.id 
    WHERE a.office_id != $officeId AND a.quantity > 0 AND a.status = 'available'
";

if ($selectedOffice > 0) {
    $assetQuery .= " AND a.office_id = $selectedOffice";
}
$assets = $conn->query($assetQuery);

// Get your own borrow requests
$requests = $conn->query("
    SELECT br.*, a.asset_name, o.office_name 
    FROM borrow_requests br 
    JOIN assets a ON br.asset_id = a.id 
    JOIN offices o ON a.office_id = o.id 
    WHERE br.user_id = $adminId
    ORDER BY br.request_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Borrow Asset</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>

<body>
    <div class="d-flex">
        <?php include 'include/sidebar.php'; ?>
        <div class="container-fluid p-4">
            <?php include 'include/topbar.php'; ?>

            <?php if (isset($message)) echo "<div class='alert alert-success'>$message</div>"; ?>
            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

            <div class="row">
                <!-- LEFT: Assets -->
                <div class="col-md-7">
                    <div class="card mb-4 mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Available Assets to Borrow</h5>
                            <form method="GET" class="d-flex align-items-center">
                                <label class="me-2 mb-0">Filter by Office:</label>
                                <select name="filter_office" class="form-select" onchange="this.form.submit()">
                                    <option value="0">All Offices</option>
                                    <?php while ($office = $offices->fetch_assoc()): ?>
                                        <option value="<?= $office['id'] ?>" <?= $selectedOffice == $office['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($office['office_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </form>
                        </div>
                        <div class="card-body">
                            <table id="assetTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Asset</th>
                                        <th>Category</th>
                                        <th>Qty</th>
                                        <th>Unit</th>
                                        <th>Office</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $assets->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['asset_name']) ?></td>
                                            <td><?= htmlspecialchars($row['category']) ?></td>
                                            <td><?= $row['quantity'] ?></td>
                                            <td><?= htmlspecialchars($row['unit']) ?></td>
                                            <td><?= htmlspecialchars($row['office_name']) ?></td>
                                            <td>
                                                <form method="POST">
                                                    <input type="hidden" name="asset_id" value="<?= $row['id'] ?>">
                                                    <button type="submit" name="borrow" class="btn btn-sm btn-outline-primary">Borrow</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Requests -->
                <div class="col-md-5">
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">My Borrow Requests</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Asset</th>
                                        <th>From</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($req = $requests->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($req['asset_name']) ?></td>
                                            <td><?= htmlspecialchars($req['office_name']) ?></td>
                                            <td>
                                                <?php
                                                $status = $req['status'];
                                                $badge = match ($status) {
                                                    'approved' => 'success',
                                                    'pending' => 'warning',
                                                    'borrowed' => 'primary',
                                                    default => 'secondary',
                                                };
                                                echo "<span class='badge bg-$badge'>" . ucfirst($status) . "</span>";
                                                ?>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($req['request_date'])) ?></td>
                                            <td>
                                                <?php if ($status === 'approved'): ?>
                                                    <a href="return_asset.php?request_id=<?= $req['request_id'] ?>"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Are you sure you want to return this asset?');">
                                                        Return
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
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

    <?php include '../includes/script.php'; ?>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#assetTable').DataTable();
        });
    </script>
</body>

</html>