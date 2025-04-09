<?php
session_start();
require '../connect.php'; // Database connection file

// Ensure only Super Admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Get admin's office_id from users table
$adminId = $_SESSION['user_id'];
$officeQuery = $conn->query("SELECT office_id FROM users WHERE id = $adminId");
$officeRow = $officeQuery->fetch_assoc();
$officeId = $officeRow['office_id'];

// Handle form submission for new user registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // Password Hashing
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert new user into the database
    $sql = "INSERT INTO users (username, fullname, email, password, role, office_id) 
            VALUES ('$username', '$fullname', '$email', '$hashed_password', '$role', '$officeId')";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = 'New user registered successfully.';
        header('Location: admin_dashboard.php');
        exit();
    } else {
        $_SESSION['error'] = 'Error: ' . mysqli_error($conn);
    }
}

if (isset($_GET['deactivate'])) {
    $userId = $_GET['deactivate'];
    $conn->query("UPDATE users SET status = 'inactive' WHERE id = $userId");
    header('Location: users.php');
    exit();
}

if (isset($_GET['activate'])) {
    $userId = $_GET['activate'];
    $conn->query("UPDATE users SET status = 'active' WHERE id = $userId");
    header('Location: users.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
                    <?php echo $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Main content row -->
            <div class="row">
                <!-- First div: User Registration Form (Left Side) -->
                <div class="col-md-6 mt-5">
                    <div class="card">
                        <div class="card-header">
                            <h5>Register New User</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="admin_dashboard.php">
                                <div class="row">
                                    <!-- First div with Username and Full Name -->
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
                                    <!-- Second div with Email, Password, and Role -->
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
                                    <!-- Role -->
                                    <div class="col-md-6 mb-3">
                                        <label for="role" class="form-label">Role</label>
                                        <select class="form-select" id="role" name="role" required>
                                            <option value="user">user</option>
                                            <option value="admin">admin</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <button type="submit" class="btn btn-primary">Register</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Second div: Registered Users List (Right Side) -->
                <div class="col-md-6 mt-5">
                    <div class="card">
                        <div class="card-header">
                            <h5>Registered Users in Your Office</h5>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Action</th> <!-- New Action Column -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Fetch users from the same office_id
                                    $query = "SELECT id, username, fullname, email, role, status FROM users WHERE office_id = $officeId";
                                    $result = $conn->query($query);
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                        $role = htmlspecialchars($row['role']);
                                        $badgeClass = $role === 'admin' ? 'badge bg-danger' : 'badge bg-primary';
                                        echo "<td><span class='$badgeClass'>$role</span></td>";
                                        $status = htmlspecialchars($row['status']);
                                        $badgeClass = $status === 'active' ? 'badge bg-success' : 'badge bg-secondary';
                                        echo "<td><span class='$badgeClass'>$status</span></td>";
                                        echo "<td>";

                                        if ($row['status'] == 'active') {
                                            echo "<a href='?deactivate=" . $row['id'] . "' class='btn btn-warning btn-sm'>Deactivate</a>";
                                        } else {
                                            echo "<a href='?activate=" . $row['id'] . "' class='btn btn-success btn-sm'>Activate</a>";
                                        }

                                        echo "</td>";

                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/script.php'; ?>
</body>

</html>