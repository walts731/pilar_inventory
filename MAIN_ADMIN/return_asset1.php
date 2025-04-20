<?php
session_start();
require '../connect.php';

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Get the request_id from the URL
if (isset($_GET['request_id']) && is_numeric($_GET['request_id'])) {
    $requestId = $_GET['request_id'];

    // Get the request details and the associated asset_id
    $stmt = $conn->prepare("SELECT br.asset_id, br.status, a.status AS asset_status FROM borrow_requests br JOIN assets a ON br.asset_id = a.id WHERE br.request_id = ?");
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($assetId, $requestStatus, $assetStatus);
    $stmt->fetch();
    $stmt->close();

    // Check if the request exists and if the current status is approved or borrowed
    if ($assetId && in_array($requestStatus, ['approved', 'borrowed']) && $assetStatus === 'borrowed') {
        // Step 1: Update the asset status to 'available'
        $assetUpdate = $conn->prepare("UPDATE assets SET status = 'available' WHERE id = ?");
        $assetUpdate->bind_param("i", $assetId);
        $assetUpdated = $assetUpdate->execute();
        $assetUpdate->close();

        // Step 2: Update the borrow request status to 'returned'
        $requestUpdate = $conn->prepare("UPDATE borrow_requests SET status = 'returned' WHERE request_id = ?");
        $requestUpdate->bind_param("i", $requestId);
        $requestUpdated = $requestUpdate->execute();
        $requestUpdate->close();

        // Check if both updates were successful
        if ($assetUpdated && $requestUpdated) {
            $_SESSION['message'] = "Asset returned successfully!";
        } else {
            $_SESSION['message'] = "Error returning asset.";
        }
    } else {
        $_SESSION['message'] = "Invalid request or asset status.";
    }
} else {
    $_SESSION['message'] = "Invalid request.";
}

// Redirect back to the previous page (borrow requests page)
header('Location: borrow_asset.php');
exit();
?>
