<?php
session_start();
require '../connect.php';
require '../vendor/autoload.php'; // Include Composer's autoloader

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

// Only allow access for admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'office_admin') {
    header('Location: index.php');
    exit();
}

// Get admin's office_id from the session
$adminId = $_SESSION['user_id'];
$officeQuery = $conn->query("SELECT office_id FROM users WHERE id = $adminId");
$officeRow = $officeQuery->fetch_assoc();
$officeId = $officeRow['office_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_asset'])) {
    // Get form data
    $asset_name = htmlspecialchars($_POST['asset_name']);
    $category_id = (int) $_POST['category']; // Get the category ID, not the name
    $description = htmlspecialchars($_POST['description']);
    $quantity = (int) $_POST['quantity'];
    $unit = htmlspecialchars($_POST['unit']);
    $status = htmlspecialchars($_POST['status']);
    $acquisition_date = $_POST['acquisition_date'];
    $value = (float) $_POST['value'];
    $red_tagged = (int) $_POST['red_tagged'];

    // Check if the category_id exists in the categories table
    $check_category_query = "SELECT COUNT(*) FROM categories WHERE id = ?";
    if ($check_stmt = $conn->prepare($check_category_query)) {
        $check_stmt->bind_param("i", $category_id);
        $check_stmt->execute();
        $check_stmt->bind_result($category_exists);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($category_exists == 0) {
            echo "<script>alert('The selected category does not exist. Please select a valid category.'); window.location.href='assets_list.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Database error. Please try again.'); window.location.href='assets.php';</script>";
        exit;
    }

    // Generate QR Code using chillerlan/php-qrcode
    $qrContent = 'Asset Name: ' . $asset_name . ' | Category: ' . $category_id;
    echo "QR Content: $qrContent <br>";  // Debugging line

    $options = new QROptions([
        'version'    => 5, // Increase the version to 5 (handles more data)
        'eccLevel'   => QRCode::ECC_L, // Low error correction level to maximize capacity
        'outputType' => QRCode::OUTPUT_IMAGE_PNG,
        'imageBase64' => false // We will save the file to the server, not base64
    ]);

    $qrcode = new QRCode($options);
    $qrImage = $qrcode->render($qrContent);

    // Check if QR image is created
    if (!$qrImage) {
        echo "<script>alert('Failed to generate QR code.'); window.location.href='assets.php';</script>";
        exit;
    }

    // Generate a unique filename
    $qrFileName = uniqid() . bin2hex(random_bytes(5)) . '.png'; // Generates a filename like 680482a29bd97.png
    $qrFilePath = '../qr_code/' . $qrFileName;  // File path to store the QR code image

    // Ensure the folder exists
    if (!file_exists('../qr_code')) {
        mkdir('../qr_code', 0777, true); // Ensure the folder exists with correct permissions
    }

    // Save the image to the file path
    if (file_put_contents($qrFilePath, $qrImage) === false) {
        echo "<script>alert('Failed to save QR code image.'); window.location.href='assets.php';</script>";
        exit;
    }

    // Debugging: Check that the filename is being passed correctly
    echo "QR Code Filename: " . $qrFileName . "<br>"; // Ensure filename looks as expected

    // Insert asset into the database
    $query = "INSERT INTO assets (asset_name, category, description, quantity, unit, status, acquisition_date, value, red_tagged, office_id, qr_code)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($query)) {
        // Bind parameters to match the prepared statement
        $stmt->bind_param("sssiissdiii", $asset_name, $category_id, $description, $quantity, $unit, $status, $acquisition_date, $value, $red_tagged, $officeId, $qrFileName);

        if ($stmt->execute()) {
            echo "<script>alert('Asset added successfully!'); window.location.href='assets.php';</script>";
        } else {
            echo "<script>alert('Error adding asset: " . $stmt->error . "'); window.location.href='assets.php';</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Database error. Please try again.'); window.location.href='assets.php';</script>";
    }

    // Close the database connection
    $conn->close();
}
?>
