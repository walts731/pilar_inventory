<?php
// Assuming you already have a connection to the database in $conn
if (isset($_GET['asset_id'])) {
    $asset_id = $_GET['asset_id'];
    $query = "SELECT * FROM assets WHERE id = $asset_id";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $asset = $result->fetch_assoc();
        echo json_encode($asset);
    } else {
        echo json_encode([]);
    }
}
?>
