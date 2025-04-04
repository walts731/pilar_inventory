<?php
session_start();
include('../connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $office_id = $_POST['office_id'];
    $username = $_POST['username'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Insert user with office_id only (no office_name)
    $stmt = $conn->prepare("INSERT INTO users (office_id, username, fullname, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
    $stmt->bind_param("isssss", $office_id, $username, $fullname, $email, $password, $role);

    if ($stmt->execute()) {
        header("Location: users.php?office_id=" . $office_id);
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    echo "Invalid request method.";
}
?>
