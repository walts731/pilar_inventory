<?php
session_start();
require '../connect.php'; // Database connection file

// Ensure only Office Admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'office_admin') {
    header('Location: index.php');
    exit();
}

// Get admin's office_id from users table
$adminId = $_SESSION['user_id'];
$officeQuery = $conn->query("SELECT office_id FROM users WHERE id = $adminId");
$officeRow = $officeQuery->fetch_assoc();
$officeId = $officeRow['office_id'];

// Handle asset request form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assetName = $conn->real_escape_string($_POST['asset_name']);
    $quantity = (int) $_POST['quantity'];
    $unit = $conn->real_escape_string($_POST['unit']);
    $description = $conn->real_escape_string($_POST['description']);
    $status = 'pending';
    $requestDate = date('Y-m-d H:i:s');

    $insertQuery = "INSERT INTO asset_requests (asset_name, user_id, status, request_date, quantity, unit, description, office_id)
                    VALUES ('$assetName', '$adminId', '$status', '$requestDate', '$quantity', '$unit', '$description', '$officeId')";

    if ($conn->query($insertQuery)) {
        $_SESSION['success'] = "Asset request submitted successfully.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['error'] = "Error submitting request: " . $conn->error;
    }
}

// Fetch requested assets for the admin's office
$requestQuery = $conn->query("SELECT request_id, asset_name, user_id, status, request_date, quantity, unit, description, office_id 
                              FROM asset_requests 
                              WHERE office_id = '$officeId'
                              ORDER BY request_date DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Asset Requests</title>
    <?php include '../includes/links.php'; ?>
</head>

<body>
    <div class="d-flex">
        <?php include 'include/sidebar.php'; ?>
        <div class="container-fluid p-4">
            <?php include 'include/topbar.php'; ?>

            <div class="row">
                <!-- LEFT: Request Form -->
                <div class="col-md-6 mt-5">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Request an Asset</h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success"><?= $_SESSION['success'];
                                                                    unset($_SESSION['success']); ?></div>
                            <?php endif; ?>
                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger"><?= $_SESSION['error'];
                                                                unset($_SESSION['error']); ?></div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="row">
                                    <!-- First Column -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="asset_name" class="form-label">Asset Name</label>
                                            <input type="text" name="asset_name" id="asset_name" class="form-control" placeholder="Enter Asset Name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="quantity" class="form-label">Quantity</label>
                                            <input type="number" name="quantity" class="form-control" required min="1">
                                        </div>
                                    </div>

                                    <!-- Second Column -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="unit" class="form-label">Unit</label>
                                            <select name="unit" id="unit" class="form-select" required>
                                                <option value="" disabled selected>Select unit</option>
                                                <option value="pcs">pcs</option>
                                                <option value="unit">unit</option>
                                                <option value="boxes">boxes</option>
                                                <option value="liters">liters</option>
                                                <option value="kilograms">kilograms</option>
                                                <option value="reams">reams</option>
                                                <option value="meters">meters</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">Purpose / Description</label>
                                            <textarea name="description" class="form-control" rows="3" required></textarea>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">Submit Request</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Requested Assets Table -->
                <div class="col-md-6 mt-5">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Requested Assets</h5>
                        </div>
                        <div class="card-body">
                            <table id="assetRequestTable" class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Asset</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                        <th>Request Date</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($requestQuery->num_rows > 0): ?>
                                        <?php while ($req = $requestQuery->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($req['asset_name']) ?></td>
                                                <td><?= htmlspecialchars($req['quantity']) . ' ' . htmlspecialchars($req['unit']) ?></td>
                                                <td>
                                                    <?php
                                                    $status = $req['status'];
                                                    $badge = match ($status) {
                                                        'approved' => 'success',
                                                        'pending' => 'warning',
                                                        default => 'secondary',
                                                    };
                                                    echo "<span class='badge bg-$badge'>" . ucfirst($status) . "</span>";
                                                    ?>
                                                </td>
                                                <td><?= date('M d, Y', strtotime($req['request_date'])) ?></td>
                                                <td><?= htmlspecialchars($req['description']) ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No requests yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> <!-- /.row -->
        </div> <!-- /.container-fluid -->
    </div> <!-- /.d-flex -->

    <?php include '../includes/script.php'; ?>
    <!-- jQuery (required by DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#assetRequestTable').DataTable();
        });
    </script>

</body>

</html>