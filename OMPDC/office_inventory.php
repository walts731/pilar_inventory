<?php
session_start();

// Check if office_id is set in the URL
if (!isset($_GET['office_id'])) {
  // If not set, redirect to the main page or show an error message
  header('Location: assets_management.php');
  exit();
}

$officeId = $_GET['office_id'];

// Assuming you are fetching data from the database
include('../connect.php');

// Query to fetch office details
$officeQuery = "SELECT office_name FROM offices WHERE id = $officeId";
$officeResult = mysqli_query($conn, $officeQuery);
$officeData = mysqli_fetch_assoc($officeResult);

// Query to fetch assets for the specific office (updated query)
$officeInventoryQuery = "SELECT id, asset_name, category, description, quantity, unit, status, acquisition_date, red_tagged, last_updated FROM assets WHERE office_id = $officeId";
$officeInventoryResult = mysqli_query($conn, $officeInventoryQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Office Inventory - <?php echo $officeData['office_name']; ?></title>
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

        <!-- Office Inventory Header -->
        <h2>Inventory for <?php echo $officeData['office_name']; ?></h2>

        <!-- Action Buttons (Add New Asset and Manage Categories) -->
        <!-- Action Buttons (Add New Asset and Manage Categories) -->
        <div class="d-flex justify-content-end mb-4">
          <!-- Add New Asset Button (Modal Trigger) -->
          <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addAssetModal">
            Add New Asset
          </button>

          <!-- Manage Categories Button (Modal Trigger) -->
          <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#manageCategoriesModal">
            Manage Categories
          </button>

        </div>


        <!-- Office Inventory Table -->
        <div class="card mb-4">
          <div class="card-header">
            Assets for Office: <?php echo $officeData['office_name']; ?>
          </div>
          <div class="card-body">
            <table class="table table-striped" id="officeInventoryTable">
              <thead>
                <tr>
                  <th>Asset Name</th>
                  <th>Category</th>
                  <th>Description</th>
                  <th>Quantity</th>
                  <th>Unit</th>
                  <th>Status</th>
                  <th>Acquisition Date</th>
                  <th>Red Tagged</th>
                  <th>Last Updated</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($asset = mysqli_fetch_assoc($officeInventoryResult)) { ?>
                  <tr>
                    <td><?php echo $asset['asset_name']; ?></td>
                    <td><?php echo $asset['category']; ?></td>
                    <td><?php echo $asset['description']; ?></td>
                    <td><?php echo $asset['quantity']; ?></td>
                    <td><?php echo $asset['unit']; ?></td>
                    <td><?php echo $asset['status']; ?></td>
                    <td><?php echo date('M d, Y', strtotime($asset['acquisition_date'])); ?></td>
                    <td><?php echo $asset['red_tagged'] ? 'Yes' : 'No'; ?></td>
                    <td><?php echo date('M d, Y', strtotime($asset['last_updated'])); ?></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Add New Asset Modal -->
    <div class="modal fade" id="addAssetModal" tabindex="-1" aria-labelledby="addAssetModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg"> <!-- Made it wider for better spacing -->
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addAssetModalLabel">Add New Asset</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <form action="add_asset.php" method="POST">
              <div class="row">
                <!-- Asset Name -->
                <div class="col-md-6 mb-3">
                  <label for="asset_name" class="form-label">Asset Name</label>
                  <input type="text" class="form-control" id="asset_name" name="asset_name" required>
                </div>

                <!-- Category Dropdown -->
                <div class="col-md-6 mb-3">
                  <label for="category" class="form-label">Category</label>
                  <select class="form-control" id="category" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php
                    $catQuery = "SELECT id, category_name FROM categories";
                    $catResult = mysqli_query($conn, $catQuery);
                    while ($category = mysqli_fetch_assoc($catResult)) {
                      echo "<option value='{$category['id']}'>{$category['category_name']}</option>";
                    }
                    ?>
                  </select>
                </div>
              </div>

              <div class="row">
                <!-- Description -->
                <div class="col-12 mb-3">
                  <label for="description" class="form-label">Description</label>
                  <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                </div>
              </div>

              <div class="row">
                <!-- Quantity -->
                <div class="col-md-6 mb-3">
                  <label for="quantity" class="form-label">Quantity</label>
                  <input type="number" class="form-control" id="quantity" name="quantity" required>
                </div>

                <!-- Unit -->
                <div class="col-md-6 mb-3">
                  <label for="unit" class="form-label">Unit</label>
                  <input type="text" class="form-control" id="unit" name="unit" required>
                </div>
              </div>

              <div class="row">
                <!-- Value -->
                <div class="col-md-6 mb-3">
                  <label for="value" class="form-label">Value (₱)</label>
                  <input type="number" step="0.01" class="form-control" id="value" name="value" required>
                </div>

                <!-- Status Dropdown -->
                <div class="col-md-6 mb-3">
                  <label for="status" class="form-label">Status</label>
                  <select class="form-control" id="status" name="status" required>
                    <option value="Available">Available</option>
                    <option value="In Use">In Use</option>
                    <option value="Under Maintenance">Under Maintenance</option>
                    <option value="Disposed">Disposed</option>
                  </select>
                </div>
              </div>

              <div class="row">
                <!-- Office Dropdown -->
                <div class="col-md-6 mb-3">
                  <label for="office_id" class="form-label">Office</label>
                  <select class="form-control" id="office_id" name="office_id" required>
                    <option value="">Select Office</option>
                    <?php
                    $officeQuery = "SELECT id, office_name FROM offices";
                    $officeResult = mysqli_query($conn, $officeQuery);
                    while ($office = mysqli_fetch_assoc($officeResult)) {
                      echo "<option value='{$office['id']}'>{$office['office_name']}</option>";
                    }
                    ?>
                  </select>
                </div>

                <!-- Acquisition Date -->
                <div class="col-md-6 mb-3">
                  <label for="acquisition_date" class="form-label">Acquisition Date</label>
                  <input type="date" class="form-control" id="acquisition_date" name="acquisition_date" required>
                </div>
              </div>

              <!-- QR Code Preview (Hidden Initially) -->
              <div class="row" id="qrCodeContainer" style="display: none;">
                <div class="col-12 text-center mb-3">
                  <label class="form-label">QR Code Preview</label>
                  <div id="qrCodePreview"></div>
                </div>
              </div>

              <!-- Modal Footer -->
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save Asset</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Manage Categories Modal for the Button -->
    <div class="modal fade" id="manageCategoriesModal" tabindex="-1" aria-labelledby="manageCategoriesModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="manageCategoriesModalLabel">Manage Categories</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <!-- Tabs for List and Add -->
            <ul class="nav nav-tabs mb-3" id="manageCategoryTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="manage-list-tab" data-bs-toggle="tab" data-bs-target="#manage-list-tab-pane"
                  type="button" role="tab" aria-controls="manage-list-tab-pane" aria-selected="true">
                  <i class="fas fa-list me-1"></i> Categories List
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="manage-add-tab" data-bs-toggle="tab" data-bs-target="#manage-add-tab-pane"
                  type="button" role="tab" aria-controls="manage-add-tab-pane" aria-selected="false">
                  <i class="fas fa-plus me-1"></i> Add New Category
                </button>
              </li>
            </ul>

            <div class="tab-content" id="manageCategoryTabsContent">
              <!-- 🔹 Categories List Tab -->
              <div class="tab-pane fade show active" id="manage-list-tab-pane" role="tabpanel" aria-labelledby="manage-list-tab">
                <div class="table-responsive">
                  <table class="table table-striped table-hover">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Assets Count</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody id="manageCategoryList">
                      <!-- Categories will be loaded here via AJAX -->
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- 🔹 Add New Category Tab -->
              <div class="tab-pane fade" id="manage-add-tab-pane" role="tabpanel" aria-labelledby="manage-add-tab">
                <form id="manageAddCategoryForm">
                  <div class="mb-3">
                    <label for="manage_category_name" class="form-label">Category Name</label>
                    <input type="text" class="form-control" id="manage_category_name" name="manage_category_name" required>
                  </div>
                  <div class="text-end">
                    <button type="submit" class="btn btn-primary">Add Category</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

  </div>

  <?php include '../includes/script.php'; ?>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- DataTables JS -->
  <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>

  <!-- Initialize DataTables -->
  <script>
    $(document).ready(function() {
      $('#officeInventoryTable').DataTable(); // Apply DataTables on the office inventory table
    });
  </script>
</body>

</html>