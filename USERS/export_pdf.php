<?php
require '../vendor/autoload.php'; // Dompdf autoload
require '../connect.php'; // Database connection

use Dompdf\Dompdf;
use Dompdf\Options;

// Initialize Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Prepare filters
$conditions = [];
$params = [];

if (!empty($_GET['office_id'])) {
    $conditions[] = "assets.office_id = ?";
    $params[] = $_GET['office_id'];
}
if (!empty($_GET['category'])) {
    $conditions[] = "assets.category = ?";
    $params[] = $_GET['category'];
}
if (!empty($_GET['status'])) {
    $conditions[] = "assets.status = ?";
    $params[] = $_GET['status'];
}
if (isset($_GET['red_tagged']) && $_GET['red_tagged'] !== '') {
    $conditions[] = "assets.red_tagged = ?";
    $params[] = $_GET['red_tagged'];
}

$whereClause = '';
if (!empty($conditions)) {
    $whereClause = 'WHERE ' . implode(' AND ', $conditions);
}

// Fetch filtered data
$query = "
    SELECT 
        assets.asset_name, 
        categories.category_name, 
        assets.description, 
        assets.quantity, 
        assets.unit, 
        assets.status, 
        assets.acquisition_date, 
        offices.office_name, 
        assets.red_tagged, 
        assets.last_updated, 
        assets.value
    FROM 
        assets
    JOIN 
        categories ON assets.category = categories.id
    JOIN 
        offices ON assets.office_id = offices.id
    $whereClause
    ORDER BY 
        assets.last_updated DESC
";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $types = str_repeat('s', count($params)); // assuming all are strings or compatible
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Build HTML
ob_start();
?>
<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
        }

        .header-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 0;
            margin-bottom: 20px;
        }

        .logo {
            height: 80px;
            margin-right: 20px;
        }

        .header-text {
            text-align: center;
            flex-grow: 1;
        }

        .header-text h4,
        .header-text h2,
        .header-text p {
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 11px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>

    <div class="header-wrapper">
        <div style="flex: 0 0 auto;">
            <?php
            $imagePath = '../img/PILAR LOGO TRANSPARENT.png';
            $imageData = base64_encode(file_get_contents($imagePath));
            $imageSrc = 'data:image/png;base64,' . $imageData;
            ?>
            <img src="<?php echo $imageSrc; ?>" class="logo" alt="Logo" style="height: 80px;">
        </div>

        <div class="header-text">
            <h4>Republic of the Philippines</h4>
            <h4>Municipality of Pilar</h4>
            <h4>Province of Albay</h4>
            <h2>INVENTORY REPORT</h2>
            <p>As of <?php echo date('F Y'); ?></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Asset Name</th>
                <th>Category</th>
                <th>Description</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Status</th>
                <th>Acquired</th>
                <th>Office</th>
                <th>Red Tagged</th>
                <th>Last Updated</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['asset_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td><?php echo htmlspecialchars($row['unit']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['acquisition_date'])); ?></td>
                    <td><?php echo htmlspecialchars($row['office_name']); ?></td>
                    <td><?php echo $row['red_tagged'] ? 'Yes' : 'No'; ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['last_updated'])); ?></td>
                    <td><?php echo number_format($row['value'], 2); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>

</html>

<?php
$html = ob_get_clean();

// Load HTML into Dompdf
$dompdf->loadHtml($html);

// Set paper size to A4 landscape
$dompdf->setPaper('A4', 'landscape');

// Render and stream the PDF
$dompdf->render();
$dompdf->stream('Inventory_Report_' . date('Ymd') . '.pdf', ['Attachment' => false]);
exit;
?>
