<?php
session_start();
require '../connect.php'; // Database connection file

// Ensure only user role can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'office_user') {
    header('Location: index.php');
    exit();
}

// Get user's info
$adminId = $_SESSION['user_id'];
$userQuery = $conn->query("SELECT fullname, office_id FROM users WHERE id = $adminId");
$userRow = $userQuery->fetch_assoc();
$officeId = $userRow['office_id'];
$fullName = $userRow['fullname'];

// Get recent inventory and reports
$recentInventoryQuery = $conn->query("SELECT * FROM assets WHERE office_id = $officeId ORDER BY last_updated DESC LIMIT 5");
$recentReportsQuery = $conn->query("SELECT * FROM archives WHERE filter_office = $officeId ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../css/user.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Left Sidebar Section -->
            <div class="col-md-3 col-lg-3 col-xl-2 col-sm-3 dashboard-left p-4">
                <h2 class="fw-bold mb-4">Welcome, <?php echo htmlspecialchars($fullName); ?>!</h2>

                <!-- Generate Reports -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">Generate Reports</h3>
                        <p class="card-text">Download your office's asset records.</p>

                        <button class="btn btn-danger rounded-pill">
                            <a href="generate_pdf.php" target="_blank" class="text-white text-decoration-none">
                                Export PDF
                            </a>
                        </button>


                    </div>
                </div>
            </div>

            <!-- Right Content Section -->
            <div class="col-md-9 col-lg-9 col-xl-10 col-sm-9dashboard-right p-4">
                <!-- Recent Inventory Section -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">Recent Added Inventory</h3>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Asset Name</th>
                                        <th>Category</th>
                                        <th>Quantity</th>
                                        <th>Added On</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT assets.asset_name, categories.category_name, assets.quantity, assets.last_updated
    FROM assets
    JOIN categories ON assets.category = categories.id
    WHERE assets.office_id = $officeId
      AND categories.category_name = 'Office Supplies'
    ORDER BY assets.last_updated DESC LIMIT 5";

                                    $recentInventoryQuery = $conn->query($query);

                                    while ($inventory = $recentInventoryQuery->fetch_assoc()) {
                                        $formattedDate = date("M d, Y", strtotime($inventory['last_updated']));
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($inventory['asset_name']); ?></td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($inventory['category_name']); ?></span>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($inventory['quantity']); ?>
                                                <?php if ($inventory['quantity'] <= 5): ?>
                                                    <span class="badge bg-danger ms-2">Low Stock</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $formattedDate; ?></td>
                                        </tr>
                                    <?php } ?>

                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>


                <!-- Recent Generated Reports Section -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Recent Generated Reports</h3>
                        <!-- Recent Generated Reports Section -->
                        <ul class="list-group list-group-flush">
                            <?php while ($report = $recentReportsQuery->fetch_assoc()) {
                                $formattedDate = date("M d, Y", strtotime($report['created_at']));
                                $fileExtension = pathinfo($report['file_name'], PATHINFO_EXTENSION);
                                $badgeColor = 'secondary';

                                switch (strtolower($fileExtension)) {
                                    case 'pdf':
                                        $badgeColor = 'danger';
                                        break;
                                    case 'xlsx':
                                    case 'xls':
                                        $badgeColor = 'success';
                                        break;
                                    case 'docx':
                                    case 'doc':
                                        $badgeColor = 'primary';
                                        break;
                                }
                            ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-file-earmark-text-fill me-2 text-muted"></i>
                                        <strong><?php echo htmlspecialchars($report['file_name']); ?></strong>
                                        <span class="badge bg-<?php echo $badgeColor; ?> ms-2 text-uppercase"><?php echo $fileExtension; ?></span>
                                        <small class="text-muted d-block mt-1"><?php echo $formattedDate; ?></small>
                                    </div>
                                    <!-- Change this to use POST request -->
                                    <button class="btn btn-outline-primary btn-sm view-report-btn"
                                        data-id="<?php echo $report['id']; ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#reportModal">
                                        View
                                    </button>
                                </li>
                            <?php } ?>
                        </ul>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Report Preview Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportModalLabel">Report Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="reportPreviewContainer">
                    <!-- Add iframe here -->
                    <iframe id="reportIframe" src="" width="100%" height="600px" frameborder="0"></iframe>
                </div>

            </div>
        </div>
    </div>


    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const viewButtons = document.querySelectorAll(".view-report-btn");
        const iframe = document.getElementById("reportIframe");

        viewButtons.forEach(button => {
            button.addEventListener("click", function() {
                const reportId = this.getAttribute("data-id");

                fetch('fetch_report.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            id: reportId
                        })
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data.includes("File not found")) {
                            alert(data);
                        } else {
                            iframe.src = data;
                        }
                    })
                    .catch(error => {
                        console.error("Error fetching the report: ", error);
                    });
            });
        });

        const reportModal = document.getElementById("reportModal");
        reportModal.addEventListener("hidden.bs.modal", function() {
            iframe.src = "";
        });
    });
</script>