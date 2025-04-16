<?php
session_start();
require '../connect.php';

// Only Super Admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'office_user') {
    header('Location: index.php');
    exit();
}

// Get admin's office_id
$adminId = $_SESSION['user_id'];
$officeQuery = $conn->query("SELECT office_id FROM users WHERE id = $adminId");
$officeRow = $officeQuery->fetch_assoc();
$officeId = $officeRow['office_id'];

$assetQuery = $conn->query("SELECT assets.*, categories.category_name FROM assets
                            JOIN categories ON assets.category = categories.id
                            WHERE assets.office_id = $officeId");

$categoryQuery = $conn->query("SELECT id, category_name FROM categories ORDER BY category_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Inventory</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../css/user.css">

    <!-- DataTables CSS -->
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
            <h2>Assets for Your Office</h2>
        </div>

        <div class="card shadow rounded w-100">
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table id="assetsTable" class="table table-bordered table-striped w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Asset Name</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Status</th>
                                <th>Acquisition Date</th>
                                <th>Value</th>
                                <th>QR Code</th>
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $assetQuery->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['asset_name']) ?></td>
                                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                                    <td><?= htmlspecialchars($row['description']) ?></td>
                                    <td><?= $row['quantity'] ?></td>
                                    <td><?= htmlspecialchars($row['unit']) ?></td>
                                    <td>
                                        <?php
                                        $status = htmlspecialchars($row['status']);
                                        switch ($status) {
                                            case 'available':
                                                echo '<span class="badge bg-success">Available</span>';
                                                break;
                                            case 'in use':
                                                echo '<span class="badge bg-warning">In Use</span>';
                                                break;
                                            case 'damaged':
                                                echo '<span class="badge bg-danger">Damaged</span>';
                                                break;
                                            case 'unserviceable':
                                                echo '<span class="badge bg-secondary">Unserviceable</span>';
                                                break;
                                            default:
                                                echo '<span class="badge bg-secondary">Unknown</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?= date("M d, Y", strtotime($row['acquisition_date'])) ?></td>
                                    <td><?= $row['value'] ?></td>
                                    <td>
                                        <?php if ($row['qr_code']): ?>
                                            <img src="../qr_codes/<?= $row['qr_code'] ?>" alt="QR Code" width="50">
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date("M d, Y", strtotime($row['last_updated'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <?php include '../ADMIN/include/add_asset_modal.php'; ?>
    <?php include '../ADMIN/include/edit_asset_modal.php'; ?>
    <?php include '../modal/manage_categories_modal.php'; ?>

    <!-- Scripts -->
    <?php include '../includes/script.php'; ?>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#assetsTable').DataTable({
                responsive: true
            });

            $('.edit-btn').click(function() {
                const assetId = $(this).data('id');
                openEditModal(assetId);
            });
        });

        function openEditModal(assetId) {
            $.ajax({
                url: 'get_asset.php',
                type: 'GET',
                data: { asset_id: assetId },
                success: function(response) {
                    const asset = JSON.parse(response);
                    $('#editAssetId').val(asset.id);
                    $('#editAssetName').val(asset.asset_name);
                    $('#editCategory').val(asset.category);
                    $('#editDescription').val(asset.description);
                    $('#editQuantity').val(asset.quantity);
                    $('#editUnit').val(asset.unit);
                    $('#editStatus').val(asset.status);
                    $('#editAcquisitionDate').val(asset.acquisition_date);
                    $('#editValue').val(asset.value);
                    $('#editLastUpdated').val(asset.last_updated);
                    $('#editAssetModal').modal('show');
                }
            });
        }
    </script>
</body>
</html>
