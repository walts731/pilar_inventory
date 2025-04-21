<?php
session_start();
require '../connect.php';

// Only Super Admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'office_admin') {
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
    <title>Assets - Admin</title>
    <?php include '../includes/links.php'; ?>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="css/asset.css">
</head>

<body>
    <div class="d-flex flex-column flex-lg-row">
        <?php include 'include/sidebar.php'; ?>

        <div class="container-fluid p-4 mt-3">
            <?php include 'include/topbar.php'; ?>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 mt-4">
                <h2 class="mb-3 mb-md-0">Assets for Your Office</h2>
                <div>
                    <button class="btn btn-primary me-2 mb-2 mb-md-0" data-bs-toggle="modal" data-bs-target="#addAssetModal">
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
                        <table id="assetsTable" class="table table-hover align-middle">
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
                                    <th>Action</th>
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
                                                    echo '<span class="badge bg-warning text-dark">In Use</span>';
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
                                                <img src="../qr_code/<?= htmlspecialchars($row['qr_code']) ?>" alt="QR Code" width="50">
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>

                                        <td><?= date("M d, Y", strtotime($row['last_updated'])) ?></td>
                                        <td>
                                            <div class="d-flex justify-content-start gap-2">
                                                <?php if ($row['qr_code']): ?>
                                                    <a href="<?= $row['qr_code'] ?>" download class="btn btn-sm btn-outline-secondary">
                                                        <i class="fas fa-download"></i> Save QR
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">No QR</span>
                                                <?php endif; ?>
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

    <!-- Modals -->
    <?php include 'include/add_asset_modal.php'; ?>
    <?php include 'include/edit_asset_modal.php'; ?>
    <?php include 'include/manage_categories_modal.php'; ?>
    <?php include '../includes/script.php'; ?>

    <!-- DataTables Scripts -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#assetsTable').DataTable({
                responsive: true,
                pagingType: 'simple',
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search assets..."
                }
            });

            // Edit button trigger
            $('.edit-btn').click(function() {
                const assetId = $(this).data('id');
                openEditModal(assetId);
            });
        });

        function openEditModal(assetId) {
            $.ajax({
                url: 'get_asset.php',
                type: 'GET',
                data: {
                    asset_id: assetId
                },
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

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Handle delete category click
    document.getElementById("categoryList").addEventListener("click", function (e) {
        if (e.target.closest(".delete-category-btn")) {
            const button = e.target.closest(".delete-category-btn");
            const categoryId = button.getAttribute("data-id");

            if (confirm("Are you sure you want to delete this category?")) {
                fetch("delete_category.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "id=" + encodeURIComponent(categoryId)
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    // Reload categories list (you need a function for that)
                    loadCategories();
                })
                .catch(error => console.error("Error:", error));
            }
        }
    });
});
</script>
