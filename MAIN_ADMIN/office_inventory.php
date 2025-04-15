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
    <?php include 'include/sidebar.php'; ?>

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
        <div class="d-flex justify-content-end mb-4">
          <a href="add_asset.php?office_id=<?php echo $officeId; ?>" class="btn btn-primary me-2">Add New Asset</a>
          <a href="manage_categories.php" class="btn btn-secondary">Manage Categories</a>
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
                    <td><?php echo $asset['acquisition_date']; ?></td>
                    <td><?php echo $asset['red_tagged'] ? 'Yes' : 'No'; ?></td>
                    <td><?php echo $asset['last_updated']; ?></td>
                    
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
      $('#officeInventoryTable').DataTable(); // Apply DataTables on the office inventory table
    });
  </script>
</body>
</html>
