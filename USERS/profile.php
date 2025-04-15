<?php
session_start();
require '../connect.php'; // Database connection file

// Ensure only user role can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'office_user') {
    header('Location: index.php');
    exit();
}

// Get user's info
$adminId = $_SESSION['user_id'];
$userQuery = $conn->query("SELECT fullname, email, password, profile_picture, office_id FROM users WHERE id = $adminId");
$userRow = $userQuery->fetch_assoc();
$fullName = $userRow['fullname'];
$email = $userRow['email'];
$hashedPassword = $userRow['password'];  // Store hashed password securely
$currentProfilePic = $userRow['profile_picture'];  // Get current profile picture

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process form submission to update profile

    // Sanitize and validate inputs
    $newFullName = htmlspecialchars(trim($_POST['fullname']));
    $newEmail = htmlspecialchars(trim($_POST['email']));
    $newPassword = htmlspecialchars(trim($_POST['password']));
    $newPasswordConfirm = htmlspecialchars(trim($_POST['confirm_password']));

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $targetDir = "../uploads/";
        $fileName = basename($_FILES["profile_picture"]["name"]);
        $targetFile = $targetDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if file is an image
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFile)) {
                // Update the profile picture in the database
                $updatePicQuery = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $updatePicQuery->bind_param('si', $fileName, $adminId);
                $updatePicQuery->execute();
                $currentProfilePic = $fileName; // Update the current profile picture
            }
        }
    }

    // Update email and fullname if they are not empty
    if ($newFullName !== '') {
        $updateQuery = $conn->prepare("UPDATE users SET fullname = ? WHERE id = ?");
        $updateQuery->bind_param('si', $newFullName, $adminId);
        $updateQuery->execute();
    }

    if ($newEmail !== '') {
        $updateQuery = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
        $updateQuery->bind_param('si', $newEmail, $adminId);
        $updateQuery->execute();
    }

    // Handle password change if provided
    if ($newPassword !== '' && $newPassword === $newPasswordConfirm) {
        $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);  // Hash the new password
        $updateQuery = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updateQuery->bind_param('si', $newHashedPassword, $adminId);
        $updateQuery->execute();
    } else {
        $error = "Passwords do not match or are empty!";
    }

    // Check for errors
    if (isset($error)) {
        echo "<div class='alert alert-danger'>$error</div>";
    } else {
        echo "<div class='alert alert-success'>Profile updated successfully!</div>";
    }
}

// Re-fetch user data to show after update
$userQuery = $conn->query("SELECT fullname, email, profile_picture FROM users WHERE id = $adminId");
$userRow = $userQuery->fetch_assoc();
$fullName = $userRow['fullname'];
$email = $userRow['email'];
$currentProfilePic = $userRow['profile_picture']; // Updated profile picture
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../css/user.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-4">
        <h2>Edit Profile</h2>
        <form action="profile.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="fullname">Full Name</label>
                <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo $fullName; ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required>
            </div>

            <div class="form-group">
                <label for="profile_picture">Profile Picture</label>
                <input type="file" class="form-control" id="profile_picture" name="profile_picture">
                <br>
                <?php
                // Display current profile picture
                if ($currentProfilePic != 'default_profile.png') {
                    echo "<img src='../uploads/$currentProfilePic' alt='Profile Picture' class='img-fluid' style='max-width: 150px;'>";
                } else {
                    echo "<img src='../uploads/default_profile.png' alt='Default Profile Picture' class='img-fluid' style='max-width: 150px;'>";
                }
                ?>
            </div>

            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password (optional)">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password (optional)">
            </div>

            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>

</body>

</html>
