<?php
session_start();
require '../connect.php'; // DB connection

// Ensure the user is logged in and is office admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'office_admin') {
    header('Location: ../index.php');
    exit();
}

// Check if request_id is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $requestId = $_GET['id'];

    // Prepare the statement to update the request status
    $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'rejected' WHERE request_id = ?");
    $stmt->bind_param("i", $requestId);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Request rejected successfully.";
    } else {
        $_SESSION['message'] = "Error rejecting request.";
    }

    $stmt->close();
} else {
    $_SESSION['message'] = "Invalid request.";
}

// Redirect back to the request page
header('Location: request.php');
exit();
?>
