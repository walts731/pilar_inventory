<?php
session_start();

  // Assuming you are fetching data from the database
  include('../connect.php');

  // Query to fetch inventory data by office
  $officesQuery = "SELECT id, office_name FROM offices";
  $resultOffices = mysqli_query($conn, $officesQuery);

  // Query to fetch overall inventory
  $overallInventoryQuery = "SELECT COUNT(*) AS total_assets FROM assets";
  $resultOverall = mysqli_query($conn, $overallInventoryQuery);
  $overallData = mysqli_fetch_assoc($resultOverall);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Assets Management</title>
  <?php include '../includes/links.php'; ?>
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
        <!-- Overall Inventory Card -->
        <div class="card mb-4">
          <div class="card-header">
            Overall Inventory
            <h5 class="card-title">Total Assets: <?php echo $overallData['total_assets']; ?></h5>
          </div>
          <div class="card-body">
            <a href="overall_inventory.php" class="btn btn-primary">Manage Overall Inventory</a>
          </div>
        </div>

        <!-- Office Inventory Cards -->
        <div class="row">
          <?php while ($office = mysqli_fetch_assoc($resultOffices)) { ?>
            <!-- Office Card -->
            <div class="col-md-4 mb-4">
              <div class="card">
                <div class="card-header">
                  <?php echo $office['office_name']; ?>
                </div>
                <div class="card-body">
                  <?php
                    // Query to fetch assets for the specific office
                    $officeId = $office['id'];
                    $officeInventoryQuery = "SELECT COUNT(*) AS total_assets FROM assets WHERE office_id = $officeId";
                    $resultOfficeInventory = mysqli_query($conn, $officeInventoryQuery);
                    $officeData = mysqli_fetch_assoc($resultOfficeInventory);
                  ?>
                  <h5 class="card-title">Total Assets: <?php echo $officeData['total_assets']; ?></h5>
                  <a href="office_inventory.php?office_id=<?php echo $office['id']; ?>" class="btn btn-primary">Manage Office Inventory</a>
                </div>
              </div>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>

  </div>

  <?php include '../includes/script.php'; ?>
  </body>
</html>
