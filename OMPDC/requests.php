<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../login.php");
  exit;
}

include('../connect.php');

// Fetch data from the asset_requests table, joining with assets, users, and offices tables
$sql = "
SELECT ar.request_id, 
       a.asset_name, 
       u.fullname, 
       u.role, 
       ar.status, 
       ar.request_date, 
       ar.quantity, 
       ar.unit, 
       ar.description, 
       o.office_name
FROM asset_requests ar
JOIN assets a ON ar.asset_name = a.id
JOIN users u ON ar.user_id = u.id
JOIN offices o ON ar.office_id = o.id
";
$result = $conn->query($sql);

function getBadgeClass($status)
{
  switch ($status) {
    case 'approved':
      return 'bg-success';  // Green badge for approved
    case 'pending':
      return 'bg-warning text-dark'; // Yellow badge for pending
    case 'rejected':
      return 'bg-danger'; // Red badge for rejected
    default:
      return 'bg-secondary'; // Gray for unknown status
  }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Asset Requests</title>
  <?php include '../includes/links.php'; ?>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
  <style>
    .badge-status {
      display: inline-block;
      width: auto;
      margin-right: 10px;
    }
  </style>
</head>

<body>
  <div class="d-flex">
    <!-- Include Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <div class="container-fluid">
      <!-- Include Topbar -->
      <?php include '../includes/topbar.php'; ?>

      <!-- Main Content Section -->
      <div class="container mt-5">
        <div class="card shadow-lg">
          <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Asset Requests</h3>
          </div>
          <div class="card-body">
            <table id="assetRequestsTable" class="table table-striped display">
              <thead class="table-light">
                <tr>
                  <th>Asset Name</th>
                  <th>Description</th>
                  <th>Quantity</th>
                  <th>Unit</th>
                  <th>Requesting Office</th>
                  <th>Requested By</th>
                  <th>Role</th>
                  <th>Request Date</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                if ($result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['asset_name'] . "</td>";
                    echo "<td>" . $row['description'] . "</td>";
                    echo "<td>" . $row['quantity'] . "</td>";
                    echo "<td>" . $row['unit'] . "</td>";
                    echo "<td>" . $row['office_name'] . "</td>";
                    echo "<td>" . $row['fullname'] . "</td>";
                    echo "<td><span class='badge " . ($row['role'] == 'admin' ? 'bg-primary' : 'bg-secondary') . "'>{$row['role']}</span></td>";
                    echo "<td>" . date("M j, Y", strtotime($row['request_date'])) . "</td>";
                    echo "<td><span class='badge " . getBadgeClass($row['status']) . "'>" . $row['status'] . "</span></td>";
                    echo "<td>
                            <form action='update_status.php' method='POST' class='d-inline'>
                              <input type='hidden' name='request_id' value='{$row['request_id']}'>
                              <button type='submit' name='status' value='Approved' class='btn btn-success btn-sm'>
                                <i class='fas fa-check'></i>
                              </button>
                            </form>
                            <form action='update_status.php' method='POST' class='d-inline'>
                              <input type='hidden' name='request_id' value='{$row['request_id']}'>
                              <button type='submit' name='status' value='Rejected' class='btn btn-danger btn-sm'>
                                <i class='fas fa-times'></i>
                              </button>
                            </form>
                          </td>";
                    echo "</tr>";
                  }
                } else {
                  echo "<tr><td colspan='9' class='text-center'>No asset requests found</td></tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- DataTables Script -->
      <script>
        $(document).ready(function() {
          $('#assetRequestsTable').DataTable();
        });
      </script>

</body>

</html>

<?php
$conn->close(); // Close database connection
?>