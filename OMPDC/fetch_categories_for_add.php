<?php
include('../connect.php');

$query = "SELECT id, category_name FROM categories ORDER BY category_name ASC";
$result = mysqli_query($conn, $query);

while ($category = mysqli_fetch_assoc($result)) {
    echo '<option value="' . htmlspecialchars($category['category_name']) . '">' . htmlspecialchars($category['category_name']) . '</option>';
}
?>
