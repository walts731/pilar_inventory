<?php
session_start();

// Include database connection
include('../connect.php');

// Fetch all assets from the database
$query = "SELECT a.id, a.asset_name, a.category, a.description, a.quantity, a.unit, a.status, a.acquisition_date, o.office_name 
          FROM assets a 
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
          <a href="add_asset.php" class="btn btn-primary me-2">Add New Asset</a>
          <a href="manage_categories.php" class="btn btn-info">Manage Categories</a>
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
                  <th>Status</th>
                  <th>Office</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($asset = mysqli_fetch_assoc($result)) { ?>
                  <tr>
                    <td><?php echo $asset['asset_name']; ?></td>
                    <td><?php echo $asset['category']; ?></td>
                    <td><?php echo $asset['description']; ?></td>
                    <td><?php echo $asset['quantity']; ?></td>
                    <td><?php echo $asset['unit']; ?></td>
                    <td><?php echo $asset['status']; ?></td>
                    <td><?php echo $asset['office_name']; ?></td>
                    <td>
                      <a href="edit_asset.php?asset_id=<?php echo $asset['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                      <a href="delete_asset.php?asset_id=<?php echo $asset['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
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
