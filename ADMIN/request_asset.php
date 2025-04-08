<?php
session_start();
require '../connect.php'; // Database connection file

// Ensure only Super Admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Get admin's office_id from users table
$adminId = $_SESSION['user_id'];
$officeQuery = $conn->query("SELECT office_id FROM users WHERE id = $adminId");
$officeRow = $officeQuery->fetch_assoc();
$officeId = $officeRow['office_id'];

// Fetch requested assets for the admin's office
$requestQuery = $conn->query("SELECT ar.request_id, ar.asset_id, a.asset_name, ar.status, ar.request_date, ar.quantity, ar.unit, ar.description, o.office_name
                             FROM asset_requests ar
                             JOIN assets a ON ar.asset_id = a.id
                             JOIN offices o ON ar.office_id = o.id
                             WHERE ar.office_id = '$officeId'");

// Fetch available assets for the request form
$assetsQuery = $conn->query("SELECT id, asset_name FROM assets");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Requests</title>
    <?php include '../includes/links.php'; ?>
</head>

<body>
    <div class="d-flex">
        <?php include 'include/sidebar.php'; ?>
        <div class="container-fluid p-4">
            <?php include 'include/topbar.php'; ?>

            <div class="row">
                <!-- LEFT: Request an Asset Form -->
                <div class="col-md-6 mt-5">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Request an Asset</h5>
                        </div>
                        <div class="card-body">

                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                                <?php unset($_SESSION['success']); ?>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="row">
                                    <!-- First Column (Left) -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="asset_id" class="form-label">Asset</label>
                                            <select name="asset_id" id="asset_id" class="form-select" required>
                                                <option disabled selected value="">-- Select Asset --</option>
                                                <?php while ($row = $assetsQuery->fetch_assoc()): ?>
                                                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['asset_name']) ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="quantity" class="form-label">Quantity</label>
                                            <input type="number" name="quantity" class="form-control" required min="1">
                                        </div>
                                    </div>

                                    <!-- Second Column (Right) -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="unit" class="form-label">Unit</label>
                                            <input type="text" name="unit" class="form-control" required placeholder="e.g., pcs, boxes">
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
                            <!-- Table to display requested assets -->
                            <table class="table table-sm table-bordered table-hover">
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
                                    <?php while ($req = $requestQuery->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($req['asset_name']) ?></td>
                                            <td><?= htmlspecialchars($req['quantity']) ?> <?= htmlspecialchars($req['unit']) ?></td>
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
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div> <!-- /.row -->

        </div> <!-- /.container-fluid -->
    </div> <!-- /.d-flex -->

    <?php include '../includes/script.php'; ?>
</body>

</html>