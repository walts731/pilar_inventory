
<?php
session_start();
require '../connect.php';

// Only Super Admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
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
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid p-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Assets for Your Office</h2>
                <div>
                    <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addAssetModal">
                        <i class="fas fa-plus-circle"></i> Add Asset
                    </button>
                    <a href="#" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="fas fa-cogs me-1"></i> Manage Categories
                    </a>

                </div>
            </div>

            <div class="card shadow rounded">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="assetsTable" class="table table-bordered table-striped">
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
                                    <th>Action</th> <!-- New Action Column -->
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
                                                <img src="<?= $row['qr_code'] ?>" alt="QR Code" width="50">
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date("M d, Y", strtotime($row['last_updated'])) ?></td>
                                        <td>
                                            <div class="d-flex">
                                                <?php if ($row['qr_code']): ?>
                                                    <a href="<?= $row['qr_code'] ?>" download class="btn btn-sm btn-outline-secondary me-2">
                                                        <i class="fas fa-download"></i> Save QR
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">No QR</span>
                                                <?php endif; ?>
                                                <!-- Edit Button/Icon -->
                                                <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary edit-btn" data-id="<?= $row['id'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                            </div>
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
    <!-- Add Asset Modal -->
    <?php include '../ADMIN/include/add_asset_modal.php'; ?>
    <!-- Edit Asset Modal -->
    <?php include '../ADMIN/include/edit_asset_modal.php'; ?>
    <!-- Add Category Modal -->
    <?php include '../modal/manage_categories_modal.php'; ?>


    <?php include '../includes/script.php'; ?>

    <!-- DataTables Scripts -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#assetsTable').DataTable({
                responsive: true
            });
        });

        // Trigger edit modal and populate data
        function openEditModal(assetId) {
            // Fetch asset details using AJAX or other method to pre-fill modal form
            $.ajax({
                url: 'get_asset.php',
                type: 'GET',
                data: {
                    asset_id: assetId
                },
                success: function(response) {
                    const asset = JSON.parse(response);

                    // Populate the form fields with the existing asset data
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
                }
            });

            $('#editAssetModal').modal('show');
        }

        // Attach edit function to the edit buttons in the table
        $(document).ready(function() {
            $('.edit-btn').click(function() {
                const assetId = $(this).data('id');
                openEditModal(assetId);
            });
        });

        
    </script>

</body>

</html>
</body>
</html>