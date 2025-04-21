<?php
session_start();
require '../connect.php'; // DB connection
require '../include/log_activity.php'; // Include the logging function

// Ensure the user is logged in and is office admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'office_admin') {
    header('Location: ../index.php');
    exit();
}

// Check if request_id is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $requestId = $_GET['id'];

    // Step 1: Get asset_id from the borrow request
    $getAsset = $conn->prepare("SELECT asset_id FROM borrow_requests WHERE request_id = ?");
    $getAsset->bind_param("i", $requestId);
    $getAsset->execute();
    $getAsset->bind_result($assetId);
    $getAsset->fetch();
    $getAsset->close();

    if ($assetId) {
        // Step 2: Update request status
        $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'approved' WHERE request_id = ?");
        $stmt->bind_param("i", $requestId);

        // Step 3: Update asset status
        $assetUpdate = $conn->prepare("UPDATE assets SET status = 'borrowed' WHERE id = ?");
        $assetUpdate->bind_param("i", $assetId);

        if ($stmt->execute() && $assetUpdate->execute()) {
            $_SESSION['message'] = "Request approved and asset marked as borrowed.";
        } else {
            $_SESSION['message'] = "Error approving request or updating asset.";
        }

        $stmt->close();
        $assetUpdate->close();
    } else {
        $_SESSION['message'] = "Asset not found for this request.";
    }

} else {
    $_SESSION['message'] = "Invalid request.";
}

// Redirect back to the request page
header('Location: request.php');
exit();
?>
