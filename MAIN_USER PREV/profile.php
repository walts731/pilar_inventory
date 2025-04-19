<?php
session_start();
require '../connect.php';

// Ensure only user role can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header('Location: index.php');
    exit();
}

$adminId = $_SESSION['user_id'];
$userQuery = $conn->query("SELECT fullname, email, password, profile_picture FROM users WHERE id = $adminId");
$userRow = $userQuery->fetch_assoc();
$fullName = $userRow['fullname'];
$email = $userRow['email'];
$hashedPassword = $userRow['password'];
$currentProfilePic = $userRow['profile_picture'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newFullName = htmlspecialchars(trim($_POST['fullname']));
    $newEmail = htmlspecialchars(trim($_POST['email']));
    $newPassword = htmlspecialchars(trim($_POST['password']));
    $newPasswordConfirm = htmlspecialchars(trim($_POST['confirm_password']));

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $targetDir = "../uploads/";
        $fileName = time() . '_' . basename($_FILES["profile_picture"]["name"]);
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

    if ($newPassword !== '' && $newPassword === $newPasswordConfirm) {
        $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateQuery = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updateQuery->bind_param('si', $newHashedPassword, $adminId);
        $updateQuery->execute();
    } elseif ($newPassword !== $newPasswordConfirm) {
        $error = "Passwords do not match!";
    }

    if (isset($error)) {
        $alert = "<div class='alert alert-danger'>$error</div>";
    } else {
        $alert = "<div class='alert alert-success'>Profile updated successfully!</div>";
    }

    // Refresh user info
    $userQuery = $conn->query("SELECT fullname, email, profile_picture FROM users WHERE id = $adminId");
    $userRow = $userQuery->fetch_assoc();
    $fullName = $userRow['fullname'];
    $email = $userRow['email'];
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
        .profile-card {
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #007bff;
            margin-bottom: 15px;
        }

        .form-section {
            border-left: 1px solid #eee;
            padding-left: 30px;
        }

        @media (max-width: 768px) {
            .form-section {
                border-left: none;
                padding-left: 0;
                margin-top: 30px;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-5">
        <?= isset($alert) ? $alert : '' ?>

        <div class="row">
            <div class="col-md-4 text-center profile-card">
                <h4>Your Profile</h4>
                <img src="../img/?= $currentProfilePic ?: 'josh.jpg' ?>" alt="Profile Picture" class="profile-picture">

                <p class="mt-3"><strong>Name:</strong> <?= htmlspecialchars($fullName) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
            </div>

            <div class="col-md-8 profile-card form-section">
                <h4>Edit Information</h4>
                <form action="profile.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="fullname" class="form-label">Full Name</label>
                        <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($fullName) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="profile_picture" class="form-label">Change Profile Picture</label>
                        <input type="file" name="profile_picture" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password">
                    </div>

                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
