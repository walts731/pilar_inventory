<?php
include('../connect.php');
require '../vendor/autoload.php'; // Load Composer dependencies

// Import appropriate classes based on your installed version
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $asset_name = mysqli_real_escape_string($conn, $_POST['asset_name']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $unit = mysqli_real_escape_string($conn, $_POST['unit']);
    $value = mysqli_real_escape_string($conn, $_POST['value']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $office_id = mysqli_real_escape_string($conn, $_POST['office_id']);
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
        $asset_id = mysqli_insert_id($conn); // Get last inserted ID

        // Generate QR code if category is NOT "Office Supply"
        if (strtolower($category_name) !== "office supply") {
            $qrData = "Asset ID: $asset_id\nName: $asset_name\nCategory: $category_name\nStatus: $status";

            // Ensure QR codes directory exists
            $qrDirectory = "../qr_codes/";
            if (!is_dir($qrDirectory)) {
                mkdir($qrDirectory, 0777, true); // Create directory if not exists
            }

            // COMPLETELY REVISED QR CODE GENERATION - simpler approach
            $qrCode = new QrCode($qrData);
            $qrCode->setSize(300);
            
            // Try to set error correction level if the method exists
            if (method_exists($qrCode, 'setErrorCorrectionLevel')) {
                $qrCode->setErrorCorrectionLevel('medium');
            }
            
            // The file path for the QR code
            $qrFilePath = $qrDirectory . "qr_$asset_id.png";
            
            // Write QR code to file - using approach that works with older versions
            if (method_exists($qrCode, 'writeFile')) {
                // For older versions
                $qrCode->writeFile($qrFilePath);
            } else {
                // For newer versions using the Writer
                $writer = new PngWriter();
                $result = $writer->write($qrCode);
                $result->saveToFile($qrFilePath);
            }

            // Update database with QR code path
            $update_query = "UPDATE assets SET qr_code = '$qrFilePath' WHERE id = '$asset_id'";
            mysqli_query($conn, $update_query);
        }

        echo "<script>alert('Asset added successfully!'); window.location.href='assets.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>