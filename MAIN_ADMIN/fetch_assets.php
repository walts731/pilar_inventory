<?php
require '../connect.php';

$q = $_GET['q'] ?? '';

if ($q !== '') {
    $stmt = $conn->prepare("SELECT a.*, o.office_name FROM assets a JOIN offices o ON a.office_id = o.id WHERE a.asset_name LIKE CONCAT('%', ?, '%') LIMIT 10");
    $stmt->bind_param('s', $q);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        echo '<div class="suggestion-item" 
                    data-id="' . $row['id'] . '" 
                    data-qty="' . $row['quantity'] . '" 
                    data-office="' . htmlspecialchars($row['office_name']) . '">' .
                    htmlspecialchars($row['asset_name']) . ' (' . $row['quantity'] . ') - ' . htmlspecialchars($row['office_name']) .
             '</div>';
    }
}
?>
