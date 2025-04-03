<?php
session_start();
include('../connect.php');

// Check if user is logged in and has permission
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate required fields
if (!isset($_POST['asset_id']) || !is_numeric($_POST['asset_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid asset ID']);
    exit;
}

// Prepare data
$asset_id = (int)$_POST['asset_id'];
$asset_name = trim($_POST['asset_name']);
$category = (int)$_POST['category'];
$description = trim($_POST['description']);
$quantity = (int)$_POST['quantity'];
$unit = trim($_POST['unit']);
$value = floatval($_POST['value']);
$status = trim($_POST['status']);
$office_id = (int)$_POST['office_id'];
$acquisition_date = !empty($_POST['acquisition_date']) ? $_POST['acquisition_date'] : null;

// Validate required fields
if (empty($asset_name) || empty($quantity) || empty($status) || empty($office_id)) {
    echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
    exit;
}

try {
    // Begin transaction
    $conn->begin_transaction();

    // Update asset data
    $query = "UPDATE assets SET 
              asset_name = ?, 
              category = ?, 
              description = ?, 
              quantity = ?, 
              unit = ?, 
              value = ?, 
              status = ?, 
              office_id = ?, 
              acquisition_date = ? 
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sisisdssii", 
        $asset_name,
        $category,
        $description,
        $quantity,
        $unit,
        $value,
        $status,
        $office_id,
        $acquisition_date,
        $asset_id
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update asset: " . $stmt->error);
    }

    // Log the update
    $log_query = "INSERT INTO asset_logs 
                 (asset_id, action, action_by, action_date, details) 
                 VALUES (?, 'UPDATE', ?, NOW(), ?)";
    $log_details = "Asset updated: " . json_encode([
        'name' => $asset_name,
        'category' => $category,
        'status' => $status
    ]);
    
    $log_stmt = $conn->prepare($log_query);
    $log_stmt->bind_param("iis", $asset_id, $_SESSION['user_id'], $log_details);
    $log_stmt->execute();

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Asset updated successfully',
        'asset_id' => $asset_id
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
