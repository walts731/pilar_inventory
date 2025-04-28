<?php
session_start();
include '../connect.php';

// Asset Summary
$summary = [
  'total_assets' => 0,
  'total_quantity' => 0,
  'red_tagged' => 0,
];

$summaryQuery = "SELECT 
  COUNT(*) AS total_assets, 
  SUM(quantity) AS total_quantity, 
  SUM(CASE WHEN red_tagged = 1 THEN 1 ELSE 0 END) AS red_tagged
  FROM assets";
$summaryResult = $conn->query($summaryQuery);
if ($summaryResult && $row = $summaryResult->fetch_assoc()) {
  $summary = $row;
}

// Assets Acquired Per Month
$monthlyData = [];
$monthQuery = "SELECT 
  DATE_FORMAT(acquisition_date, '%Y-%m') AS month,
  COUNT(*) AS total
  FROM assets
  GROUP BY month
  ORDER BY month ASC";
$monthResult = $conn->query($monthQuery);
while ($row = $monthResult->fetch_assoc()) {
  $monthlyData[] = $row;
}

// Asset Status Distribution
$statusData = [];
$statusQuery = "SELECT status, COUNT(*) AS count FROM assets GROUP BY status";
$statusResult = $conn->query($statusQuery);
while ($row = $statusResult->fetch_assoc()) {
  $statusData[$row['status']] = $row['count'];
}

// Fill in 0 for any missing statuses
$allStatuses = ['available', 'in use', 'disposed', 'unserviceable', 'maintenance'];
foreach ($allStatuses as $status) {
  if (!isset($statusData[$status])) {
    $statusData[$status] = 0;
  }
}

// Assets by Category
$categoryData = [];
$categoryQuery = "SELECT c.category_name, COUNT(a.id) AS total 
                  FROM assets a 
                  JOIN categories c ON a.category = c.id 
                  GROUP BY c.category_name 
                  ORDER BY total DESC";
$categoryResult = $conn->query($categoryQuery);
while ($row = $categoryResult->fetch_assoc()) {
  $categoryData[$row['category_name']] = $row['total'];
}

// Asset Value Per Month
$valuePerMonthData = [];
$valueQuery = "SELECT 
  DATE_FORMAT(acquisition_date, '%Y-%m') AS month,
  SUM(value * quantity) AS total_value
  FROM assets
  GROUP BY month
  ORDER BY month ASC";
$valueResult = $conn->query($valueQuery);
while ($row = $valueResult->fetch_assoc()) {
  $valuePerMonthData[] = $row;
}

// Count Low Stock Items (e.g., quantity <= 3)
$lowStockQuery = "SELECT COUNT(*) AS low_stock FROM assets WHERE quantity <= 3";
$lowStockResult = $conn->query($lowStockQuery);
$lowStock = 0;
if ($lowStockResult && $row = $lowStockResult->fetch_assoc()) {
  $lowStock = $row['low_stock'];
}

// Calculate Total Inventory Value (price × quantity)
$valueQuery = "SELECT SUM(value * quantity) AS total_value FROM assets";
$valueResult = $conn->query($valueQuery);
$totalValue = 0;
if ($valueResult && $row = $valueResult->fetch_assoc()) {
  $totalValue = $row['total_value'];
}

// Get assets with low stock for restock suggestion
$restockQuery = "SELECT asset_name, quantity, category FROM assets WHERE quantity < 5 ORDER BY quantity ASC";
$restockResult = $conn->query($restockQuery);

$restockSuggestions = [];
if ($restockResult && $restockResult->num_rows > 0) {
  while ($row = $restockResult->fetch_assoc()) {
    $restockSuggestions[] = $row;
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Analytics</title>
  <?php include '../includes/links.php'; ?>

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" />
</head>

<body>

  <div class="d-flex">
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="container-fluid">
      <?php include '../includes/topbar.php'; ?>

      <div class="container mt-5">
        <h3 class="mb-4">Assets Summary</h3>
        <div class="row">
          <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
              <div class="card-body">
                <h5 class="card-title">Total Assets</h5>
                <p class="card-text display-6"><?= $summary['total_assets'] ?></p>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card text-white bg-danger mb-3">
              <div class="card-body">
                <h5 class="card-title">Red Tagged Assets</h5>
                <p class="card-text display-6"><?= $summary['red_tagged'] ?></p>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
              <div class="card-body">
                <h5 class="card-title">Low Stock Items</h5>
                <p class="card-text display-6"><?= $lowStock ?></p>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
              <div class="card-body">
                <h5 class="card-title">Total Inventory Value</h5>
                <p class="card-text display-6">₱<?= number_format($totalValue, 2) ?></p>
              </div>
            </div>
          </div>
        </div>

        <div class="row mt-5">
          <!-- Pie Chart -->
          <div class="col-md-3 d-flex flex-column align-items-center">
            <h5 class="text-center">Asset Status Distribution</h5>
            <div style="width: 200px; height: 200px;">
              <canvas id="statusPieChart"></canvas>
            </div>
          </div>

          <!-- Monthly Acquisitions Bar Chart -->
          <div class="col-md-3">
            <h5 class="text-center">Assets Acquired Per Month</h5>
            <div style="height: 300px;">
              <canvas id="monthlyAssetsChart"></canvas>
            </div>
          </div>

          <!-- Assets by Category Horizontal Bar Chart -->
          <div class="col-md-3">
            <h5 class="text-center">Assets by Category</h5>
            <div style="height: 300px;">
              <canvas id="categoryBarChart"></canvas>
            </div>
          </div>

          <div class="col-md-3">
            <h5 class="text-center">Total Asset Value Acquired Per Month</h5>
            <div style="height: 300px;">
              <canvas id="valuePerMonthChart"></canvas>
            </div>
          </div>
        </div>

        <?php
// Filter the restock suggestions based on the "Office Supplies" category
$officeSuppliesSuggestions = array_filter($restockSuggestions, function($item) {
    return strtolower($item['category']) === 'Office Supplies';
});
?>

<?php if (!empty($officeSuppliesSuggestions)) : ?>
    <div class="mt-1">
        <h4>🛒 Office Supplies Restock Suggestions</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Asset Name</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Suggestion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($officeSuppliesSuggestions as $item) : ?>
                        <tr>
                            <td><?= htmlspecialchars($item['asset_name']) ?></td>
                            <td><?= htmlspecialchars($item['category']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= $item['quantity'] == 0 ? 'Urgent Restock' : 'Consider Restocking' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else : ?>
    <div class="mt-1">
        <h4>🛒 Office Supplies Restock Suggestions</h4>
        <p class="text-muted">All office supplies are sufficient at the moment.</p>
    </div>
<?php endif; ?>


      </div>
    </div>
  </div>

  <?php include '../includes/script.php'; ?>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Pass PHP data to JS -->
  <script>
    const monthlyLabels = <?= json_encode(array_column($monthlyData, 'month')) ?>;
    const monthlyCounts = <?= json_encode(array_column($monthlyData, 'total')) ?>;
    const statusLabels = <?= json_encode(array_keys($statusData)) ?>;
    const statusCounts = <?= json_encode(array_values($statusData)) ?>;
    const categoryLabels = <?= json_encode(array_keys($categoryData)) ?>;
    const categoryCounts = <?= json_encode(array_values($categoryData)) ?>;
    const valueLabels = <?= json_encode(array_column($valuePerMonthData, 'month')) ?>;
    const valueTotals = <?= json_encode(array_map('floatval', array_column($valuePerMonthData, 'total_value'))) ?>;
  </script>

  <!-- Chart Init -->
  <script>
    const ctxValue = document.getElementById('valuePerMonthChart').getContext('2d');
    const valuePerMonthChart = new Chart(ctxValue, {
      type: 'line',
      data: {
        labels: valueLabels,
        datasets: [{
          label: 'Total Value (₱)',
          data: valueTotals,
          borderColor: 'rgba(75, 192, 192, 1)',
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          fill: true,
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Total Value (₱)'
            }
          },
          x: {
            title: {
              display: true,
              text: 'Month'
            }
          }
        }
      }
    });

    const ctxCategory = document.getElementById('categoryBarChart').getContext('2d');
    const categoryBarChart = new Chart(ctxCategory, {
      type: 'bar',
      data: {
        labels: categoryLabels,
        datasets: [{
          label: 'Total Assets',
          data: categoryCounts,
          backgroundColor: 'rgba(255, 159, 64, 0.6)',
          borderColor: 'rgba(255, 159, 64, 1)',
          borderWidth: 1
        }]
      },
      options: {
        indexAxis: 'y', // Horizontal bar
        responsive: true,
        scales: {
          x: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Number of Assets'
            }
          },
          y: {
            title: {
              display: true,
              text: 'Category'
            }
          }
        }
      }
    });

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

    const ctx = document.getElementById('monthlyAssetsChart').getContext('2d');
    const monthlyAssetsChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: monthlyLabels,
        datasets: [{
          label: 'Assets Acquired',
          data: monthlyCounts,
          backgroundColor: 'rgba(54, 162, 235, 0.6)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Number of Assets'
            }
          },
          x: {
            title: {
              display: true,
              text: 'Month'
            }
          }
        }
      }
    });
  </script>

</body>

</html>