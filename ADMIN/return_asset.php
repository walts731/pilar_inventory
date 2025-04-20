<?php
session_start();
require '../connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'office_admin') {
    header('Location: index.php');
    exit();
}

if (!isset($_GET['request_id'])) {
    die("Invalid Request");
}

$requestId = (int)$_GET['request_id'];
$defaultCondition = "Good";  // Optional: Can be dynamic via a form
$defaultRemarks = "Returned";  // Optional: Can be dynamic via a form

// Step 1: Get borrow request details
$borrowQuery = "SELECT * FROM borrow_requests WHERE request_id = $requestId";
$borrowResult = $conn->query($borrowQuery);

if ($borrowResult->num_rows === 0) {
    die("Borrow request not found.");
}

$borrow = $borrowResult->fetch_assoc();
$assetId = $borrow['asset_id'];
$userId = $borrow['user_id'];
$officeId = $borrow['office_id'];

// Step 2: Insert into returned_assets with NOW() for return_date
$returnQuery = "
    INSERT INTO returned_assets 
    (borrow_request_id, asset_id, user_id, return_date, condition_on_return, remarks, office_id)
    VALUES ($requestId, $assetId, $userId, NOW(), '$defaultCondition', '$defaultRemarks', $officeId)
";
if ($conn->query($returnQuery) === TRUE) {
    // Step 3: Update asset status to 'available'
    $updateAssetQuery = "UPDATE assets SET status = 'available' WHERE id = $assetId";
    $conn->query($updateAssetQuery);

    // Step 4: Delete from borrow_requests
    $deleteBorrowQuery = "DELETE FROM borrow_requests WHERE request_id = $requestId";
    $conn->query($deleteBorrowQuery);

    // Redirect or success message
    $_SESSION['message'] = "Asset returned successfully.";
    header("Location: borrow_asset.php");
    exit();
} else {
    echo "Error returning asset: " . $conn->error;
}
?>
