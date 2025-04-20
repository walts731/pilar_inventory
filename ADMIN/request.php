<?php
session_start();
require '../connect.php'; // Database connection file

// Ensure only Super Admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'office_admin') {
    header('Location: index.php');
    exit();
}

// Get admin's office_id from users table
$adminId = $_SESSION['user_id'];
$officeQuery = $conn->query("SELECT office_id FROM users WHERE id = $adminId");
$officeRow = $officeQuery->fetch_assoc();
$officeId = $officeRow['office_id'];

// Fetch all offices for the filter dropdown
$officeFilterQuery = $conn->query("SELECT id, office_name FROM offices");

// Fetch all borrow requests based on selected office (if any filter is applied)
$officeFilter = isset($_GET['filter_office']) ? $_GET['filter_office'] : '';
$filterQuery = "SELECT br.request_id, br.asset_id, br.user_id, br.office_id, br.request_date, br.status, 
                        a.asset_name, u.username, o.office_name
                FROM borrow_requests br
                JOIN assets a ON br.asset_id = a.id
                JOIN users u ON br.user_id = u.id
                JOIN offices o ON br.office_id = o.id";

if ($officeFilter != '') {
    $filterQuery .= " WHERE br.office_id = $officeFilter AND br.office_id != $officeId";
} else {
    $filterQuery .= " WHERE br.office_id != $officeId";
}

$requestQuery = $conn->query($filterQuery);

// Fetch all categories for the filter dropdown (optional)
$categoryQuery = $conn->query("SELECT id, category_name FROM categories");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Borrow Requests</title>
    <?php include '../includes/links.php'; ?>
    <!-- DataTables CSS and JS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
</head>

<body>
    <div class="d-flex">
        <?php include 'include/sidebar.php'; ?>
        <div class="container-fluid p-4">
            <?php include 'include/topbar.php'; ?>

            <h3>Asset Borrow Requests</h3>

            <!-- Filter Form -->
            <form action="request.php" method="GET" class="mb-4">
                <div class="row g-2">
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <label for="filter_office" class="form-label">Filter by Office</label>
                        <select name="filter_office" id="filter_office" class="form-control" onchange="this.form.submit()">
                            <option value="">All Offices</option>
                            <?php while ($office = $officeFilterQuery->fetch_assoc()) { ?>
                                <option value="<?php echo $office['id']; ?>" <?php echo ($office['id'] == $officeFilter) ? 'selected' : ''; ?>>
                                    <?php echo $office['office_name']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </form>

            <!-- Card for DataTable -->
            <div class="card">
                <div class="card-header">
                    <h4>Borrow Requests</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="requestsTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Asset Name</th>
                                    <th>Requested By</th>
                                    <th>Requesting Office</th>
                                    <th>Request Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $requestQuery->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?php echo $row['asset_name']; ?></td>
                                        <td><?php echo $row['username']; ?></td>
                                        <td><?php echo $row['office_name']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($row['request_date'])); ?></td>
                                        <td>
                                            <?php
                                            $status = ucfirst($row['status']);
                                            $badgeClass = '';

                                            switch (strtolower($row['status'])) {
                                                case 'pending':
                                                    $badgeClass = 'bg-warning text-dark';
                                                    break;
                                                case 'approved':
                                                    $badgeClass = 'bg-success';
                                                    break;
                                                case 'rejected':
                                                    $badgeClass = 'bg-danger';
                                                    break;
                                                default:
                                                    $badgeClass = 'bg-secondary';
                                            }
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $status; ?></span>
                                        </td>
                                        <td>
                                            <a href="approve_request.php?id=<?php echo $row['request_id']; ?>" class="btn btn-outline-success btn-sm">&#10003;</a>
                                            <a href="reject_request.php?id=<?php echo $row['request_id']; ?>" class="btn btn-outline-danger btn-sm">&#10007;</a>
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

    <!-- DataTables Initialization -->
    <script>
        $(document).ready(function() {
            $('#requestsTable').DataTable();
        });
    </script>
</body>

</html>