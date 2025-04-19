<?php
session_start();
require '../connect.php'; // Database connection file

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    exit("Invalid request.");
}

$reportId = intval($_POST['id']);
$stmt = $conn->prepare("SELECT file_name FROM archives WHERE id = ?");
$stmt->bind_param("i", $reportId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    exit("Report not found.");
}

$row = $result->fetch_assoc();
$fileName = $row['file_name'];
$filePath = "../uploads/" . $fileName;

if (!file_exists($filePath)) {
    http_response_code(404);
    exit("File not found at: " . $filePath);
}

$extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

if ($extension === 'csv') {
    // Render CSV as HTML table
    echo '<table class="table table-bordered">';
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            echo "<tr>";
            foreach ($data as $cell) {
                echo "<td>" . htmlspecialchars($cell) . "</td>";
            }
            echo "</tr>";
        }
        fclose($handle);
    }
    echo '</table>';
} else {
    // For non-CSV, return path (handled by iframe)
    echo $filePath;
}
exit;
?>
