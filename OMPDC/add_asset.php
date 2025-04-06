<?php
session_start();  // Start the session at the very top

include('../connect.php');
include('../includes/functions.php'); // Include the function that logs activities
require '../vendor/autoload.php'; // Load Composer dependencies

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure user_id and office_id are available in the session
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['office_id'])) {
        echo "Error: User not logged in or session data is missing.";
        exit;
    }

    $user_id = $_SESSION['user_id']; // Get user ID from session
    $office_id = $_SESSION['office_id']; // Get office ID from session

    // Get the form data
    $asset_name = mysqli_real_escape_string($conn, $_POST['asset_name']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $unit = mysqli_real_escape_string($conn, $_POST['unit']);
    $value = mysqli_real_escape_string($conn, $_POST['value']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $acquisition_date = mysqli_real_escape_string($conn, $_POST['acquisition_date']);

    // Get category name to check if it's "Office Supply"
    $category_query = "SELECT category_name FROM categories WHERE id = '$category_id'";
    $category_result = mysqli_query($conn, $category_query);
    $category = mysqli_fetch_assoc($category_result);
    $category_name = $category['category_name'];

    // Insert asset into the database
    $insert_query = "INSERT INTO assets (asset_name, category, description, quantity, unit, value, status, office_id, acquisition_date) 
                     VALUES ('$asset_name', '$category_id', '$description', '$quantity', '$unit', '$value', '$status', '$office_id', '$acquisition_date')";

    if (mysqli_query($conn, $insert_query)) {
        $asset_id = mysqli_insert_id($conn); // Get the last inserted asset ID

        // Log the activity
        $module = "Asset Management";
        $action = "Added new asset: $asset_name (ID: $asset_id), Category: $category_name";
        logActivity($conn, $user_id, $office_id, $module, $action); // Log this activity

        // Generate QR code if category is NOT "Office Supply"
        if (strtolower($category_name) !== "office supply") {
            try {
                // Ensure QR codes directory exists
                $qrDirectory = "../qr_codes/";
                if (!is_dir($qrDirectory)) {
                    mkdir($qrDirectory, 0777, true); // Create directory if not exists
                }

                // File path for the QR code
                $qrFilePath = $qrDirectory . "qr_$asset_id.png";
                
                // Skip QR code generation for now - just mark it in the database
                // We'll display a placeholder message in the frontend
                $update_query = "UPDATE assets SET qr_code = 'pending' WHERE id = '$asset_id'";
                mysqli_query($conn, $update_query);
                
                // Log that we need to update the QR code generation code
                error_log("QR code generation skipped for asset $asset_id - needs code update");
            } catch (Exception $e) {
                // Log the error but continue with the asset addition
                error_log("QR Code processing failed: " . $e->getMessage());
            }
        }

        // Redirect or show success message
        echo "<script>alert('Asset added successfully!'); window.location.href='overall_inventory.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
