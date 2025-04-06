<?php
function logActivity($conn, $user_id, $office_id, $module, $action) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $conn->prepare("INSERT INTO system_logs (user_id, office_id, module, action, ip_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $user_id, $office_id, $module, $action, $ip);
    $stmt->execute();
}

?>