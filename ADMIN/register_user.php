<?php
session_start();
require '../connect.php'; // Include your database connection
require '../include/log_activity.php'; // Include the logging function


// Ensure only Admins can access the page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'office_admin') {
    header('Location: index.php');
    exit();
}

$adminId = $_SESSION['user_id'];
$officeQuery = $conn->query("SELECT office_id FROM users WHERE id = $adminId");
$officeRow = $officeQuery->fetch_assoc();
$officeId = $officeRow['office_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // Password Hashing
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Set default status to 'active'
    $status = 'active';

    // Generate reset token (optional)
    $reset_token = bin2hex(random_bytes(16));  // Random token generation
    $reset_token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expiry in 1 hour

    // Get current date and time for created_at
    $created_at = date('Y-m-d H:i:s');

    // Insert the new user into the database
    $sql = "INSERT INTO users (username, fullname, email, password, role, status, created_at, reset_token, reset_token_expiry, office_id) 
            VALUES ('$username', '$fullname', '$email', '$hashed_password', '$role', '$status', '$created_at', '$reset_token', '$reset_token_expiry', '$officeId')";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = 'New user registered successfully.';
        header('Location: admin_dashboard.php');
        exit();
    } else {
        $_SESSION['error'] = 'Error: ' . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register User</title>
    <?php include '../includes/links.php'; ?>
</head>
<body>
    <div class="d-flex">
        <?php include 'include/sidebar.php'; ?>
        <div class="container-fluid p-4">
            <?php include 'include/topbar.php'; ?>

            <!-- Success/Error Message Display -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6 mt-5">
                    <div class="card">
                        <div class="card-header">
                            <h5>Register New User</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="register_user.php">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="fullname" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="fullname" name="fullname" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="role" class="form-label">Role</label>
                                        <select class="form-select" id="role" name="role" required>
                                            <option value="user">User</option>
                                            <option value="admin">Admin</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <button type="submit" class="btn btn-primary">Register</button>
                                    </div>
                                </div>

                                <!-- Hidden field for office_id -->
                                <input type="hidden" name="office_id" value="<?php echo $officeId; ?>">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/script.php'; ?>
</body>
</html>
