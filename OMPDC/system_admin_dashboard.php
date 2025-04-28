<?php
session_start();
require '../connect.php';

// Access control
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: index.php");
    exit();
}

// ===== FETCH SUMMARY COUNTS =====
function fetch_count($conn, $query) {
    $res = $conn->query($query);
    $row = $res->fetch_assoc();
    return $row['total'] ?? 0;
}

$total_assets       = fetch_count($conn, "SELECT COUNT(*) FROM assets");
$pending_requests   = fetch_count($conn, "SELECT COUNT(*) FROM asset_requests WHERE status='pending'");
$red_tagged_assets  = fetch_count($conn, "SELECT COUNT(*) FROM assets WHERE status='red-tagged'");
$low_stock_assets   = fetch_count($conn, "SELECT COUNT(*) FROM assets WHERE quantity <= 5");

// ===== FETCH RECENT ACTIVITIES =====
$activities = $conn->query("
    SELECT u.username, a.module, a.activity, a.timestamp
    FROM activity_log a
    JOIN users u ON a.user_id = u.id
    ORDER BY a.timestamp DESC LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// ===== FETCH RECENT INVENTORY ACTIONS =====
$inventory_actions = $conn->query("
    SELECT action_name, category, quantity, action_date
    FROM inventory_actions
    ORDER BY action_date DESC LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// ===== FETCH CHART DATA =====

// Value per month
$valuePerMonthData = [];
$valueResult = $conn->query("
    SELECT DATE_FORMAT(acquisition_date, '%Y-%m') AS month,
           SUM(value * quantity) AS total_value
    FROM assets
    GROUP BY month
    ORDER BY month ASC
");
while ($row = $valueResult->fetch_assoc()) {
    $valuePerMonthData[] = $row;
}

// Asset Status Distribution
$statusData = [];
$statusQuery = "SELECT status, COUNT(*) AS count FROM assets GROUP BY status";
$statusResult = $conn->query($statusQuery);
while ($row = $statusResult->fetch_assoc()) {
  $statusData[$row['status']] = $row['count'];
}

// Assets acquired per month
$monthlyData = [];
$monthResult = $conn->query("
    SELECT DATE_FORMAT(acquisition_date, '%Y-%m') AS month,
           COUNT(*) AS total
    FROM assets
    GROUP BY month
    ORDER BY month ASC
");
while ($row = $monthResult->fetch_assoc()) {
    $monthlyData[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Super Admin Dashboard</title>
    <?php include '../includes/links.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="d-flex">
    <?php include '../includes/sidebar.php'; ?>
    <div class="container-fluid p-4">
        <?php include '../includes/topbar.php'; ?>

        <!-- SUMMARY CARDS -->
<div class="row mb-4 mt-5">
    <?php
    $cards = [
        ['title' => 'Total Assets',         'count' => $total_assets,     'class' => 'primary',   'border_class' => 'border-success'],
        ['title' => 'Pending Requests',     'count' => $pending_requests, 'class' => 'warning',   'border_class' => 'border-warning'],
        ['title' => 'Red-Tagged Assets',    'count' => $red_tagged_assets,'class' => 'danger',    'border_class' => 'border-danger'],
        ['title' => 'Low Stock Assets',     'count' => $low_stock_assets, 'class' => 'secondary','border_class' => 'border-secondary'],
    ];
    foreach ($cards as $card): ?>
        <div class="col-md-3">
            <div class="card border <?= $card['border_class']; ?> shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?= $card['title']; ?></h5>
                    <h3 class="card-text"><?= $card['count']; ?></h3>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>


        <!-- CHARTS AND ACTIVITIES -->
        <div class="row">
            <!-- Asset Status Pie Chart -->
            <div class="col-md-3 d-flex flex-column align-items-center mb-4">
                <h5 class="text-center">Asset Status Distribution</h5>
                <div style="width: 200px; height: 200px;">
                    <canvas id="statusPieChart"></canvas>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="col-md-9 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">Recent Activities</div>
                    <div class="card-body p-2">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Module</th>
                                    <th>Activity</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activities as $act): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($act['username']) ?></td>
                                        <td><?= htmlspecialchars($act['module']) ?></td>
                                        <td><?= htmlspecialchars($act['activity']) ?></td>
                                        <td><?= date('M d, Y h:i A', strtotime($act['timestamp'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ASSET CHARTS -->
        <div class="row">
            <!-- Total Asset Value Per Month Chart -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header text-center fw-bold">Total Asset Value Per Month</div>
                    <div class="card-body">
                        <canvas id="valuePerMonthChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Assets Acquired Per Month Chart -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header text-center fw-bold">Assets Acquired Per Month</div>
                    <div class="card-body">
                        <canvas id="monthlyAssetsChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- CHART SCRIPTS -->
<script>
const valueLabels = <?= json_encode(array_column($valuePerMonthData, 'month')) ?>;
const valueTotals = <?= json_encode(array_map('floatval', array_column($valuePerMonthData, 'total_value'))) ?>;
const monthlyLabels = <?= json_encode(array_column($monthlyData, 'month')) ?>;
const monthlyCounts = <?= json_encode(array_map('intval', array_column($monthlyData, 'total'))) ?>;

// 🔵 Add these two lines:
const statusLabels = <?= json_encode(array_keys($statusData)) ?>;
const statusCounts = <?= json_encode(array_values($statusData)) ?>;


const ctxPie = document.getElementById('statusPieChart').getContext('2d');
    const statusPieChart = new Chart(ctxPie, {
      type: 'pie',
      data: {
        labels: statusLabels,
        datasets: [{
          label: 'Asset Status',
          data: statusCounts,
          backgroundColor: [
            '#4CAF50', // available - green
            '#2196F3', // in use - blue
            '#9E9E9E', // disposed - grey
            '#f44336', // unserviceable - red
            '#FF9800' // maintenance - orange
          ],
          borderColor: '#fff',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'right'
          },
          title: {
            display: false
          }
        }
      }
    });

// Value per Month Line Chart
new Chart(document.getElementById('valuePerMonthChart'), {
    type: 'line',
    data: {
        labels: valueLabels,
        datasets: [{
            label: 'Total Value (₱)',
            data: valueTotals,
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        scales: {
            y: { beginAtZero: true, title: { display: true, text: '₱ Value' }},
            x: { title: { display: true, text: 'Month' }}
        }
    }
});

// Monthly Acquired Assets Bar Chart
new Chart(document.getElementById('monthlyAssetsChart'), {
    type: 'bar',
    data: {
        labels: monthlyLabels,
        datasets: [{
            label: 'Assets Acquired',
            data: monthlyCounts,
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Quantity' }},
            x: { title: { display: true, text: 'Month' }}
        }
    }
});
</script>

<?php include '../includes/script.php'; ?>
</body>
</html>
