<?php
session_start();
require '../connect.php';

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
        echo "<script>alert('Database error. Please try again.'); window.location.href='assets_list.php';</script>";
        exit;
    }

    // Insert asset into the database, using the office_id from the session
    $query = "INSERT INTO assets (asset_name, category, description, quantity, unit, status, acquisition_date, value, red_tagged, office_id)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("sssiissdii", $asset_name, $category_id, $description, $quantity, $unit, $status, $acquisition_date, $value, $red_tagged, $officeId);
        if ($stmt->execute()) {
            echo "<script>alert('Asset added successfully!'); window.location.href='assets_list.php';</script>";
        } else {
            echo "<script>alert('Error adding asset. Please try again.'); window.location.href='assets_list.php';</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Database error. Please try again.'); window.location.href='assets.php';</script>";
    }

    // Close the database connection
    $conn->close();
}
?>
