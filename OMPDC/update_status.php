<?php
include('../connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id'], $_POST['status'])) {
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];

    // Update status in the database
    $stmt = $conn->prepare("UPDATE asset_requests SET status = ? WHERE request_id = ?");
    $stmt->bind_param("si", $status, $request_id);

    if ($stmt->execute()) {
        header("Location: requests.php"); // Redirect back
        exit;
    } else {
        echo "Error updating status: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>