<?php
function logActivity($conn, $userId, $officeId, $module, $action) {
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $datetime = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO system_logs (user_id, office_id, module, action, ip_address, datetime) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $userId, $officeId, $module, $action, $ipAddress, $datetime);
    $stmt->execute();
    $stmt->close();
}
?>
