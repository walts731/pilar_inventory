<?php
session_start();
require '../connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'office_admin') {
    header('Location: index.php');
    exit();
}

$adminId = $_SESSION['user_id'];
$userQuery = $conn->query("SELECT fullname, email, username, password, profile_picture, office_id FROM users WHERE id = $adminId");
$userRow = $userQuery->fetch_assoc();
$fullName = $userRow['fullname'];
$email = $userRow['email'];
$username = $userRow['username'];
$currentProfilePic = $userRow['profile_picture'];

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $newFullName = htmlspecialchars(trim($_POST['fullname']));
        $newEmail = htmlspecialchars(trim($_POST['email']));
        $newUsername = htmlspecialchars(trim($_POST['username']));

        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $targetDir = "../uploads/";
            $fileName = basename($_FILES["profile_picture"]["name"]);
            $targetFile = $targetDir . $fileName;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);

            if ($check !== false) {
                if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFile)) {
                    $updatePicQuery = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                    $updatePicQuery->bind_param('si', $fileName, $adminId);
                    $updatePicQuery->execute();
                    $currentProfilePic = $fileName;
                }
            }
        }

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

        if ($newUsername !== '') {
            // Check if username is unique
            $checkUsernameQuery = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $checkUsernameQuery->bind_param('s', $newUsername);
            $checkUsernameQuery->execute();
            $checkUsernameQuery->bind_result($usernameCount);
            $checkUsernameQuery->fetch();

            if ($usernameCount == 0) {
                // Update username if unique
                $updateQuery = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
                $updateQuery->bind_param('si', $newUsername, $adminId);
                $updateQuery->execute();
                $username = $newUsername;
            } else {
                $error = "Username is already taken!";
            }
        }

        $success = "Profile updated successfully!";
    }

    if (isset($_POST['update_password'])) {
        $currentPassword = htmlspecialchars(trim($_POST['current_password']));
        $newPassword = htmlspecialchars(trim($_POST['password']));
        $newPasswordConfirm = htmlspecialchars(trim($_POST['confirm_password']));

        // Fetch the current password from the database
        $passwordQuery = $conn->query("SELECT password FROM users WHERE id = $adminId");
        $passwordRow = $passwordQuery->fetch_assoc();
        $storedPasswordHash = $passwordRow['password'];

        // Validate the current password
        if (password_verify($currentPassword, $storedPasswordHash)) {
            // Check for password strength
            $passwordPattern = "/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/"; // At least 1 uppercase, 1 number, 1 special character, and 8+ characters

            if (preg_match($passwordPattern, $newPassword)) {
                if ($newPassword !== '' && $newPassword === $newPasswordConfirm) {
                    $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $updateQuery = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $updateQuery->bind_param('si', $newHashedPassword, $adminId);
                    $updateQuery->execute();
                    $success = "Password updated successfully!";
                } else {
                    $error = "New passwords do not match!";
                }
            } else {
                $error = "Password must be at least 8 characters long and contain at least one uppercase letter, one number, and one special character.";
            }
        } else {
            $error = "The current password you entered is incorrect!";
        }
    }

    // Refresh user data
    $userQuery = $conn->query("SELECT fullname, email, username, profile_picture FROM users WHERE id = $adminId");
    $userRow = $userQuery->fetch_assoc();
    $fullName = $userRow['fullname'];
    $email = $userRow['email'];
    $username = $userRow['username'];
    $currentProfilePic = $userRow['profile_picture'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <?php include '../includes/links.php'; ?>
    <link rel="stylesheet" href="../css/user.css">
    <style>
        .profile-pic-preview {
            max-width: 150px;
            border-radius: 0.5rem;
            border: 1px solid #ddd;
            padding: 4px;
        }
        .content-area {
            margin-top: 60px;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <?php include 'include/sidebar.php'; ?>
        <div class="container-fluid p-4">
            <?php include 'include/topbar.php'; ?>

            <div class="container my-5">
                <div class="row justify-content-center">
                    <!-- Left Column: Profile Card -->
                    <div class="col-lg-5 col-md-6 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white text-center">
                                <h4 class="mb-0">Edit Profile</h4>
                            </div>
                            <div class="card-body">
                                <?php if ($success): ?>
                                    <div class="alert alert-success"><?php echo $success; ?></div>
                                <?php endif; ?>
                                <?php if ($error): ?>
                                    <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php endif; ?>

                                <form action="profile.php" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="update_profile" value="1">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" value="<?php echo $username; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="fullname" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo $fullName; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required>
                                    </div>
                                    <div class="mb-3 row">
                                        <div class="col-md-6">
                                            <label for="profile_picture" class="form-label">Profile Picture</label>
                                            <input type="file" class="form-control" id="profile_picture" name="profile_picture">
                                        </div>
                                        <div class="col-md-6 text-center">
                                            <img src="../uploads/<?php echo $currentProfilePic ?: 'default_profile.png'; ?>" alt="Profile Picture" class="profile-pic-preview">
                                        </div>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Update Profile</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Password Card -->
                    <div class="col-lg-5 col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-warning text-white text-center">
                                <h4 class="mb-0">Update Password</h4>
                            </div>
                            <div class="card-body">
                                <form action="profile.php" method="POST">
                                    <input type="hidden" name="update_password" value="1">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Enter current password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-warning">Update Password</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/script.php'; ?>
</body>

</html>
