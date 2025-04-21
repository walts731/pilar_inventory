<?php
session_start(); // ✅ Required to access $_SESSION variables

include('../connect.php');
require 'include/log_activity.php'; // Include the logging function

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);

    $query = "INSERT INTO categories (category_name) VALUES ('$category_name')";
    if (mysqli_query($conn, $query)) {
        echo "Category added successfully!";

        // Ensure these session variables exist
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $officeId = isset($_SESSION['office_id']) ? $_SESSION['office_id'] : null;
        $module = 'Categories';
        $action = "Added new category: $category_name";

        // Only log if user and office ID are available
        if ($userId !== null && $officeId !== null) {
            logActivity($conn, $userId, $officeId, $module, $action);
        } else {
            error_log("Logging skipped: user_id or office_id is not set.");
        }
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
