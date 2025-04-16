<?php
require '../vendor/autoload.php'; // Dompdf autoload
require '../connect.php'; // Database connection

use Dompdf\Dompdf;
use Dompdf\Options;

// Initialize Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Fetch asset data with the join
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
    ORDER BY 
        assets.last_updated DESC
";
$result = $conn->query($query);

// Build HTML
ob_start();
?>
<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0; /* Ensure there's no margin for the body */
        }

        .header-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 0; /* Remove the top margin */
            margin-bottom: 20px;
        }

        .logo {
            height: 80px;
            margin-right: 20px; /* Optional: Adds some space between the logo and the header text */
        }

        .header-text {
            text-align: center;
            flex-grow: 1; /* Ensures header text takes available space */
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
        <!-- Logo on the left -->
        <div style="flex: 0 0 auto;">
            <?php
            $imagePath = '../img/PILAR LOGO TRANSPARENT.png';
            $imageData = base64_encode(file_get_contents($imagePath));
            $imageSrc = 'data:image/png;base64,' . $imageData;
            ?>
            <img src="<?php echo $imageSrc; ?>" class="logo" alt="Logo" style="height: 80px;">
        </div>

        <!-- Centered Header Text -->
        <div class="header-text">
            <h4>Republic of the Country</h4>
            <h4>Municipality of Region</h4>
            <h4>Province of Province</h4>
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
