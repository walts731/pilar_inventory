<?php
include('../connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $asset_name = mysqli_real_escape_string($conn, $_POST['asset_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $quantity = intval($_POST['quantity']);
    $unit = mysqli_real_escape_string($conn, $_POST['unit']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $acquisition_date = mysqli_real_escape_string($conn, $_POST['acquisition_date']);
    $office = mysqli_real_escape_string($conn, $_POST['office']);
    $value = mysqli_real_escape_string($conn, $_POST['value']);

    $query = "INSERT INTO assets (asset_name, category, description, quantity, unit, status, acquisition_date, office_id, value) 
              VALUES ('$asset_name', '$category', '$description', '$quantity', '$unit', '$status', '$acquisition_date', '$value', 
                      (SELECT id FROM offices WHERE office_name = '$office' LIMIT 1))";

    if (mysqli_query($conn, $query)) {
        echo "Asset added successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
