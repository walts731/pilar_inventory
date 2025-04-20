<?php
include('../connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);

    $query = "INSERT INTO categories (category_name) VALUES ('$category_name')";
    if (mysqli_query($conn, $query)) {
        echo "Category added successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
