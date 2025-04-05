<?php
include('../connect.php');

// Get filter parameters from the query string
$status = $_GET['status'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$office_id = $_GET['office_id'] ?? ''; // Office ID from the filter

// Create a timestamped filename for both download and archiving
$timestamp = date('Ymd_His');
$filename = "assets_report_{$timestamp}.csv";

// Set the path to save the archive file
$archivePath = "../archives/{$filename}";
$archiveFile = fopen($archivePath, 'w');

// Set headers for browser download
header('Content-Type: text/csv');
header("Content-Disposition: attachment; filename=\"$filename\"");

// Open PHP output stream to send the file directly to the browser
$output = fopen('php://output', 'w');

// Write the CSV headers (same for both output and archive)
$headers = ['ID', 'Asset Name', 'Category', 'Description', 'Quantity', 'Status', 'Office', 'Acquisition Date'];
fputcsv($output, $headers);
fputcsv($archiveFile, $headers);

// Build the query for fetching assets
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
if (!empty($office_id)) {
    $query .= " AND assets.office_id = ?";
    $params[] = $office_id;
}

// Prepare and execute the query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Prepare SQL to insert into the archives table
$insertQuery = "INSERT INTO archives (file_name, file_path, office_id) VALUES (?, ?, ?)";
$insertStmt = $conn->prepare($insertQuery);

// Check for SQL preparation error
if ($insertStmt === false) {
    die('Error preparing insert statement: ' . $conn->error);
}

// Fetch and write data to both the output (browser), archive file, and insert into database
while ($row = $result->fetch_assoc()) {
    // Prepare the data to be written to the CSV and inserted into the database
    $data = [
        $row['id'],
        $row['asset_name'],
        $row['category_name'],
        $row['description'],
        $row['quantity'],
        $row['status'],
        $row['office_name'],
        $row['acquisition_date']
    ];

    // Write to both files
    fputcsv($output, $data);
    fputcsv($archiveFile, $data);
}

// Insert data into the archives table (file info)
$insertStmt->bind_param('ssi', $filename, $archivePath, $office_id);

// Execute the insert statement and check for errors
if (!$insertStmt->execute()) {
    die('Error inserting into archives table: ' . $insertStmt->error);
}

// Close the insert statement and files
$insertStmt->close();
fclose($output);
fclose($archiveFile);

// Exit to ensure the script ends after the file is generated and sent
exit();
?>
