<?php
session_start();

// Include database connection
include('../connect.php');

// Fetch all assets with their corresponding category names, category IDs, and values
$query = "SELECT a.id, a.asset_name, c.id AS category_id, c.category_name, a.description, a.quantity, a.unit, a.value, a.status, a.acquisition_date, o.office_name 
          FROM assets a 
          JOIN categories c ON a.category = c.id
          JOIN offices o ON a.office_id = o.id
          ORDER BY a.asset_name";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Overall Asset Inventory</title>
  <?php include '../includes/links.php'; ?>

  <!-- DataTables CSS -->
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
</head>

<body>
  <!-- Wrapper div for Sidebar and Content -->
  <div class="d-flex">
    <!-- Include Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="container-fluid">
      <!-- Include Topbar -->
      <?php include '../includes/topbar.php'; ?>

      <!-- Main Content Section -->
      <div class="container mt-5">
        <!-- Back Button -->
        <div class="d-flex justify-content-start mb-4">
          <a href="assets.php" class="btn btn-secondary">Back to Assets Management</a>
        </div>

        <!-- Action Buttons (Add New Asset and Manage Categories) -->
        <div class="d-flex justify-content-end mb-4">
          <!-- Add New Asset Button -->
          <a href="#" class="btn btn-primary me-3" data-bs-toggle="modal" data-bs-target="#addAssetModal">
            <i class="fas fa-plus"></i> Add New Asset
          </a>
          <!-- Manage Categories Button -->
          <a href="#" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fas fa-cogs me-1"></i> Manage Categories
          </a>
        </div>

        <!-- Overall Inventory Table -->
        <div class="card mb-4">
          <div class="card-header">
            All Assets
          </div>
          <div class="card-body">
            <table class="table table-striped" id="inventoryTable">
              <thead>
                <tr>
                  <th>Asset Name</th>
                  <th>Category</th>
                  <th>Description</th>
                  <th>Quantity</th>
                  <th>Unit</th>
                  <th>Value</th>
                  <th>Status</th>
                  <th>Office</th>
                  <th>QR Code</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($asset = mysqli_fetch_assoc($result)) { ?>
                  <tr>
                    <td><?php echo htmlspecialchars($asset['asset_name']); ?></td>
                    <td><?php echo htmlspecialchars($asset['category_name']) ?></td>
                    <td><?php echo htmlspecialchars($asset['description']); ?></td>
                    <td><?php echo htmlspecialchars($asset['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($asset['unit']); ?></td>
                    <td>₱<?php echo number_format($asset['value'], 2); ?></td>
                    <td><?php echo htmlspecialchars($asset['status']); ?></td>
                    <td><?php echo htmlspecialchars($asset['office_name']); ?></td>
                    <td>
                      <?php if (!empty($asset['qr_code'])) { ?>
                        <img src="<?php echo $asset['qr_code']; ?>" alt="QR Code" width="50">
                      <?php } else { ?>
                        <span class="text-muted">No QR Code</span>
                      <?php } ?>
                    </td>
                    <td>
  <a href="#" class="text-warning me-2 edit-asset"
     title="Edit"
     data-asset-id="<?php echo $asset['id']; ?>"
     data-bs-toggle="modal"
     data-bs-target="#editAssetModal">
     <i class="fas fa-edit"></i>
  </a>
  <!-- Red Tag Button -->
  <a href="#" class="text-danger me-2 red-tag-asset"
     title="Red Tag"
     data-asset-id="<?php echo $asset['id']; ?>"
     data-bs-toggle="modal"
     data-bs-target="#redTagModal">
     <i class="fas fa-times-circle"></i> Red Tag
  </a>
</td>

                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Red Tag Confirmation Modal -->
<div class="modal fade" id="redTagModal" tabindex="-1" aria-labelledby="redTagModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="redTagModalLabel">Confirm Red Tag</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to mark this asset as Unserviceable?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form id="redTagForm" method="POST">
          <input type="hidden" id="asset_id" name="asset_id">
          <button type="submit" class="btn btn-danger">Yes, Red Tag</button>
        </form>
      </div>
    </div>
  </div>
</div>

  <?php include '../modal/edit_asset_modal.php'; ?>
  <?php include '../modal/add_asset_modal.php'; ?>
  <?php include '../modal/manage_categories_modal.php'; ?>

  <?php include '../includes/script.php'; ?>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- DataTables JS -->
  <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>

  <!-- Initialize DataTables -->
  <script>
    $(document).ready(function() {
      $('#inventoryTable').DataTable(); // Apply DataTables on the overall inventory table
    });
  </script>
</body>

</html>