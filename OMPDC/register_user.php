<?php
session_start();
include('../connect.php');

// Ensure the form data is posted and office_id is passed
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['office_id'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $role = $_POST['role'];
    $office_id = $_POST['office_id'];

    // Insert the user into the database
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, office_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $username, $email, $password, $role, $office_id);
    if ($stmt->execute()) {
        header("Location: users.php?office_id=" . $office_id); // Redirect back to the users page for the selected office
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
