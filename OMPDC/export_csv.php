<?php
include('../connect.php');

$status = $_GET['status'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="assets_report.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Asset Name', 'Category', 'Description', 'Quantity', 'Status', 'Office', 'Acquisition Date']);

$query = "SELECT assets.id, assets.asset_name, categories.category_name, assets.description, 
                assets.quantity, assets.status, offices.office_name, assets.acquisition_date 
          FROM assets 
          JOIN categories ON assets.category = categories.id 
          JOIN offices ON assets.office_id = offices.id WHERE 1";

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

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>
