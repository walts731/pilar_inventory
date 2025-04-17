<?php
session_start();
require '../connect.php';

// Ensure only user role can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'office_user') {
    header('Location: index.php');
    exit();
}

$userId = $_SESSION['user_id']; // assumes 'user_id' is stored in session

// Fetch the user's office_id
$officeResult = $conn->query("SELECT office_id FROM users WHERE id = $userId");
$officeId = null;

if ($officeResult && $officeResult->num_rows > 0) {
    $officeRow = $officeResult->fetch_assoc();
    $officeId = $officeRow['office_id'];
} else {
    die("Unable to fetch office information.");
}

// Total Assets (office-specific)
$totalAssets = $conn->query("SELECT COUNT(*) as total FROM assets WHERE office_id = $officeId")->fetch_assoc()['total'];

// Red-tagged items (damaged/unserviceable)
$redTagged = $conn->query("SELECT COUNT(*) as count FROM assets WHERE status IN ('damaged', 'unserviceable') AND office_id = $officeId")->fetch_assoc()['count'];

// Low quantity items (less than 5)
$lowQtyResult = $conn->query("SELECT asset_name, quantity FROM assets WHERE quantity < 5 AND office_id = $officeId");

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Asset Analytics</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../css/user.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/analytics.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">
    <h2 class="mb-4">Asset Analytics Overview (Your Office)</h2>

    <!-- First Section: Total Assets and Red-Tagged Items -->
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
                    <h5 class="card-title">Red-Tagged (Damaged/Unserviceable)</h5>
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

    <!-- Second Section: Assets by Category -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="card-title mb-3">Assets by Category</h4>
            <canvas id="categoryChart" height="90"></canvas>
        </div>
    </div>

    <!-- Third Section: Assets Acquired Per Month and Low Quantity Items -->
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
                    <h4 class="card-title mb-3">Low Quantity Items (Less than 5)</h4>
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
</div>

<!-- Chart.js Scripts -->
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
            scales: { y: { beginAtZero: true, precision: 0 } }
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
                y: { beginAtZero: true, precision: 0 },
                x: { title: { display: true, text: 'Month' } }
            }
        }
    });
</script>

<?php include '../includes/script.php'; ?>
</body>
</html>
