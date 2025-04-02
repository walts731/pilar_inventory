<?php
include('../connect.php');

$query = "SELECT id, office_name FROM offices ORDER BY office_name ASC";
$result = mysqli_query($conn, $query);

while ($office = mysqli_fetch_assoc($result)) {
    echo '<option value="' . htmlspecialchars($office['office_name']) . '">' . htmlspecialchars($office['office_name']) . '</option>';
}
?>
