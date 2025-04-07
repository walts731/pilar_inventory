<?php
session_start();
include('../connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $office_id = $_POST['office_id'];
    $username = $_POST['username'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Check if password and confirm password match
    if ($password !== $confirm_password) {
        echo "Error: Passwords do not match.";
        exit;
    }

    // Check password length (minimum 8 characters)
    if (strlen($password) < 8) {
        echo "Error: Password must be at least 8 characters long.";
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into the database
    $stmt = $conn->prepare("INSERT INTO users (office_id, username, fullname, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
    $stmt->bind_param("isssss", $office_id, $username, $fullname, $email, $hashed_password, $role);

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
