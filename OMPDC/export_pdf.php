<?php
require '../vendor/autoload.php';
use Dompdf\Dompdf;

$html = '<h2>Assets Report</h2><table border="1"><tr><th>ID</th><th>Asset Name</th><th>Status</th><th>Date</th></tr>';
// Fetch data like CSV script
$html .= '</table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("assets_report.pdf", ["Attachment" => 1]);
exit();
?>
