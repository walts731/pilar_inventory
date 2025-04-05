<?php
require '../vendor/autoload.php';
use Dompdf\Dompdf;

include('../connect.php');

// Filters from GET
$status = $_GET['status'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Fetch data
$query = "SELECT assets.id, assets.asset_name, assets.status, assets.acquisition_date 
          FROM assets 
          WHERE 1";
$params = [];

if (!empty($status)) {
    $query .= " AND assets.status = ?";
    $params[] = $status;
}
if (!empty($start_date) && !empty($end_date)) {
    $query .= " AND assets.acquisition_date BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Start HTML
$html = '<h2 style="text-align:center;">Assets Report</h2>';
$html .= '<table border="1" cellspacing="0" cellpadding="6" width="100%">
            <thead>
              <tr style="background:#f2f2f2;">
                <th>ID</th>
                <th>Asset Name</th>
                <th>Status</th>
                <th>Acquisition Date</th>
              </tr>
            </thead>
            <tbody>';

// Populate rows
while ($row = $result->fetch_assoc()) {
    $html .= "<tr>
                <td>{$row['id']}</td>
                <td>{$row['asset_name']}</td>
                <td>{$row['status']}</td>
                <td>{$row['acquisition_date']}</td>
              </tr>";
}

$html .= '</tbody></table>';

// Create PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Save to file
$timestamp = date('Ymd_His');
$filename = "assets_report_{$timestamp}.pdf";
file_put_contents("../archives/{$filename}", $dompdf->output());

// Stream to user
$dompdf->stream($filename, ["Attachment" => 1]);
exit();
?>
