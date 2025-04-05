<?php
session_start();

// Database connection
include('../connect.php');

// Fetch all offices for filter dropdown
$officeQuery = $conn->query("SELECT id, office_name FROM offices ORDER BY office_name");

// Handle filtering
$officeId = $_GET['office'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

// Base query for archives
$sql = "SELECT a.*, o.office_name FROM archives a 
        LEFT JOIN offices o ON a.office_id = o.id 
        WHERE 1=1";

$params = [];

if (!empty($officeId)) {
    $sql .= " AND a.office_id = ?";
    $params[] = $officeId;
}

if (!empty($startDate) && !empty($endDate)) {
    $sql .= " AND DATE(a.uploaded_at) BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
}

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind the parameters if they exist
if (!empty($params)) {
    $types = str_repeat('s', count($params)); // assuming all are strings for simplicity
    $stmt->bind_param($types, ...$params);
}

// Execute and get the result
$stmt->execute();
$result = $stmt->get_result();
$files = [];

// Fetch all the rows from the result
while ($row = $result->fetch_assoc()) {
    $files[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archive Files</title>
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

            <div class="container mt-5">
                <h3 class="mb-4">📁 Archive Files</h3>

                <!-- Filter Form -->
                <form method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <select name="office" class="form-control">
                                <option value="">All Offices</option>
                                <?php
                                while ($row = $officeQuery->fetch_assoc()) {
                                    $selected = ($officeId == $row['id']) ? 'selected' : '';
                                    echo "<option value='{$row['id']}' $selected>{$row['office_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>

                <?php
                if (empty($files)) {
                    echo "<p class='text-muted'>No archived files found.</p>";
                } else {
                    echo "<div class='table-responsive'>";
                    echo "<table id='archiveTable' class='table table-bordered table-striped'>";
                    echo "<thead class='table-dark'>
                            <tr>
                                <th>Filename</th>
                                <th>Office</th>
                                <th>File Type</th>
                                <th>Size (KB)</th>
                                <th>Last Modified</th>
                                <th>Action</th>
                            </tr>
                          </thead>
                          <tbody>";

                    foreach ($files as $file) {
                        $filePath = $file['file_path'];
                        $fileExt = pathinfo($file['file_name'], PATHINFO_EXTENSION);
                        $fileSize = filesize($filePath) / 1024; // Convert to KB
                        $lastModified = date("F d, Y H:i:s", strtotime($file['uploaded_at']));

                        echo "<tr>
                                <td>{$file['file_name']}</td>
                                <td>{$file['office_name']}</td>
                                <td>{$fileExt}</td>
                                <td>" . number_format($fileSize, 2) . "</td>
                                <td>{$lastModified}</td>
                                <td>
                                    <a href='{$filePath}' class='btn btn-sm btn-success' download>Download</a>
                                    <a href='delete_file.php?file=" . urlencode($file['file_name']) . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure to delete this file?\")'>Delete</a>
                                </td>
                              </tr>";
                    }

                    echo "</tbody></table></div>";
                }
                ?>

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
            $('#archiveTable').DataTable(); // Apply DataTables on the archive table
        });
    </script>
</body>

</html>
