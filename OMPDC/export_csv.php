<?php
session_start();
include('../connect.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get filters from URL
$status = $_GET['status'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$office_id = $_GET['office'] ?? '';
$category_id = $_GET['category'] ?? '';

// Prepare query
$query = "SELECT assets.asset_name, categories.category_name, assets.description, assets.quantity,
                 assets.status, offices.office_name, assets.acquisition_date
          FROM assets
          JOIN categories ON assets.category = categories.id
          JOIN offices ON assets.office_id = offices.id
          WHERE 1";

$params = [];
$types = '';

if (!empty($status)) {
    $query .= " AND assets.status = ?";
    $params[] = $status;
    $types .= 's';
}
if (!empty($category_id)) {
    $query .= " AND assets.category = ?";
    $params[] = $category_id;
    $types .= 's';
}
if (!empty($start_date) && !empty($end_date)) {
    $query .= " AND assets.acquisition_date BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= 'ss';
}
if (!empty($office_id)) {
    $query .= " AND assets.office_id = ?";
    $params[] = $office_id;
    $types .= 's';
}

// Execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Set CSV headers
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="asset_report.csv"');

// Generate CSV file
$file_name = "asset_report_" . date('Ymd_His') . ".csv";  // Custom filename with timestamp
$output = fopen('php://output', 'w');
fputcsv($output, ['Asset Name', 'Category', 'Description', 'Quantity', 'Status', 'Office', 'Acquisition Date']);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['asset_name'],
        $row['category_name'],
        $row['description'],
        $row['quantity'],
        $row['status'],
        $row['office_name'],
        date("M j, Y", strtotime($row['acquisition_date']))
    ]);
}
fclose($output);

// Record archive action with file_name
$insert_archive = $conn->prepare("INSERT INTO archives (user_id, action_type, filter_status, filter_office, filter_category, filter_start_date, filter_end_date, file_name, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

$insert_archive->bind_param(
    'isssssss',
    $_SESSION['user_id'],
    $action_type,
    $status,
    $office_id,
    $category_id,
    $start_date,
    $end_date,
    $file_name
);

$action_type = 'Export CSV';
$insert_archive->execute();
exit();
?>
