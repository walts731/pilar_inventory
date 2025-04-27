<?php
session_start();
require '../connect.php';
require_once '../vendor/autoload.php'; // in case you need QR functionality later

// Ensure only user role can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'office_user') {
    header('Location: index.php');
    exit();
}

// Fetch user details
$userId = $_SESSION['user_id'];
$userQuery = $conn->query("SELECT fullname, office_id FROM users WHERE id = $userId");
$userRow = $userQuery->fetch_assoc();
$officeId = $userRow['office_id'];
$fullName = $userRow['fullname'];

// --- Analytics Section Data ---

// Total Assets
$totalAssets = $conn->query("SELECT COUNT(*) as total FROM assets WHERE office_id = $officeId")->fetch_assoc()['total'];

// Red-tagged items
$redTagged = $conn->query("SELECT COUNT(*) as count FROM assets WHERE status IN ('damaged', 'unserviceable') AND office_id = $officeId")->fetch_assoc()['count'];

// Assets by Status
$statusStats = $conn->query("SELECT status, COUNT(*) as count FROM assets WHERE office_id = $officeId GROUP BY status");
$statusData = [];
while ($row = $statusStats->fetch_assoc()) {
    $statusData[$row['status']] = $row['count'];
}

// Assets by Category
$categoryStats = $conn->query("SELECT categories.category_name, COUNT(*) as count 
    FROM assets 
    JOIN categories ON assets.category = categories.id 
    WHERE assets.office_id = $officeId
    GROUP BY assets.category");

$categoryLabels = [];
$categoryCounts = [];
while ($row = $categoryStats->fetch_assoc()) {
    $categoryLabels[] = $row['category_name'];
    $categoryCounts[] = $row['count'];
}

// Assets Acquired Per Month
$monthlyStats = $conn->query("SELECT DATE_FORMAT(acquisition_date, '%Y-%m') AS month, COUNT(*) as count
    FROM assets
    WHERE office_id = $officeId
    GROUP BY month
    ORDER BY month ASC");

$months = [];
$monthCounts = [];
while ($row = $monthlyStats->fetch_assoc()) {
    $months[] = $row['month'];
    $monthCounts[] = $row['count'];
}

// Low Quantity Items
$lowQtyResult = $conn->query("SELECT asset_name, quantity FROM assets WHERE quantity < 5 AND office_id = $officeId");

// --- Dashboard Section Data ---

// Recent Inventory
$recentInventoryQuery = $conn->query("SELECT assets.asset_name, categories.category_name, assets.quantity, assets.last_updated
    FROM assets
    JOIN categories ON assets.category = categories.id
    WHERE assets.office_id = $officeId
    ORDER BY assets.last_updated DESC
    LIMIT 5");

// Recent Reports
$recentReportsQuery = $conn->query("SELECT * FROM archives WHERE filter_office = $officeId ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../css/user.css">
    <link rel="stylesheet" href="../css/analytics.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-md-3 dashboard-left p-4">
                <h2 class="fw-bold mb-4">Welcome, <?= htmlspecialchars($fullName); ?>!</h2>

                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">Generate Reports</h3>
                        <p class="card-text">Download your office's asset records.</p>
                        <div class="d-flex gap-2">
                            <button class="btn btn-danger rounded-pill">
                                <a href="generate_pdf.php" target="_blank" class="text-white text-decoration-none">Export PDF</a>
                            </button>
                            <button class="btn btn-primary rounded-pill">
                                <a href="scan_qr.php" target="_blank" class="text-white text-decoration-none">Scan QR</a>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Content -->
            <div class="col-md-9 dashboard-right p-4">

                <!-- Analytics Section -->
                <div class="row text-center mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Total Assets</h5>
                                <h3><?= $totalAssets ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm bg-danger text-white">
                            <div class="card-body">
                                <h5 class="card-title">Red-Tagged</h5>
                                <h3><?= $redTagged ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <?php foreach ($statusData as $status => $count): ?>
                            <div class="card shadow-sm mb-2">
                                <div class="card-body">
                                    <h6 class="card-title"><?= ucfirst($status) ?></h6>
                                    <h4><?= $count ?></h4>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Assets by Category -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Assets by Category</h4>
                        <canvas id="categoryChart" height="90"></canvas>
                    </div>
                </div>

                <!-- Assets Acquired Per Month + Low Quantity Items -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <h4 class="card-title mb-3">Assets Acquired Per Month</h4>
                                <canvas id="monthlyChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <h4 class="card-title mb-3">Low Quantity Items</h4>
                                <?php if ($lowQtyResult->num_rows > 0): ?>
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Asset Name</th>
                                                <th>Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $lowQtyResult->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['asset_name']) ?></td>
                                                    <td class="text-danger"><?= $row['quantity'] ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <p class="text-muted">No low quantity items found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Inventory -->
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
                                    <?php while ($inventory = $recentInventoryQuery->fetch_assoc()): ?>
                                        <?php $formattedDate = date("M d, Y", strtotime($inventory['last_updated'])); ?>
                                        <tr>
                                            <td><?= htmlspecialchars($inventory['asset_name']); ?></td>
                                            <td><span class="badge bg-primary"><?= htmlspecialchars($inventory['category_name']); ?></span></td>
                                            <td>
                                                <?= htmlspecialchars($inventory['quantity']); ?>
                                                <?php if ($inventory['quantity'] <= 5): ?>
                                                    <span class="badge bg-danger ms-2">Low Stock</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $formattedDate; ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Reports -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Recent Generated Reports</h3>
                        <ul class="list-group list-group-flush">
                            <?php while ($report = $recentReportsQuery->fetch_assoc()): ?>
                                <?php
                                $formattedDate = date("M d, Y", strtotime($report['created_at']));
                                $fileExtension = pathinfo($report['file_name'], PATHINFO_EXTENSION);
                                $badgeColor = match (strtolower($fileExtension)) {
                                    'pdf' => 'danger',
                                    'xlsx', 'xls' => 'success',
                                    'docx', 'doc' => 'primary',
                                    default => 'secondary'
                                };
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-file-earmark-text-fill me-2 text-muted"></i>
                                        <strong><?= htmlspecialchars($report['file_name']); ?></strong>
                                        <span class="badge bg-<?= $badgeColor; ?> ms-2 text-uppercase"><?= $fileExtension; ?></span>
                                        <small class="text-muted d-block mt-1"><?= $formattedDate; ?></small>
                                    </div>
                                    <button class="btn btn-outline-primary btn-sm view-report-btn" data-id="<?= $report['id']; ?>" data-bs-toggle="modal" data-bs-target="#reportModal">
                                        View
                                    </button>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>

            </div> <!-- End Right Content -->
        </div>
    </div>

    <!-- Report Preview Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportModalLabel">Report Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="reportPreviewContainer">
                    <iframe id="reportIframe" src="" width="100%" height="600px" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Chart.js -->
    <script>
        const categoryChart = new Chart(document.getElementById('categoryChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($categoryLabels) ?>,
                datasets: [{
                    label: 'Assets',
                    data: <?= json_encode($categoryCounts) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                }
            }
        });

        const monthlyChart = new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($months) ?>,
                datasets: [{
                    label: 'Assets Acquired',
                    data: <?= json_encode($monthCounts) ?>,
                    fill: false,
                    borderColor: 'rgba(255, 99, 132, 0.9)',
                    backgroundColor: 'rgba(255, 99, 132, 0.3)',
                    tension: 0.3,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                }
            }
        });

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
                            iframe.src = data.includes("File not found") ? "" : data;
                        })
                        .catch(error => console.error("Error fetching the report: ", error));
                });
            });

            const reportModal = document.getElementById("reportModal");
            reportModal.addEventListener("hidden.bs.modal", function() {
                iframe.src = "";
            });
        });
    </script>

    <?php include '../includes/script.php'; ?>
</body>

</html>