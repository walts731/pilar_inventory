<?php
include('../connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);

    $query = "DELETE FROM categories WHERE id = '$category_id'";
    if (mysqli_query($conn, $query)) {
        echo "Category deleted successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
