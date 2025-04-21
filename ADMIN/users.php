<?php
session_start();
require '../connect.php';
require '../include/log_activity.php'; // Include the logging function

// Ensure only office_admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'office_admin') {
    header('Location: index.php');
    exit();
}

// Get admin's office_id
$adminId = $_SESSION['user_id'];
$officeQuery = $conn->query("SELECT office_id FROM users WHERE id = $adminId");
$officeRow = $officeQuery->fetch_assoc();
$officeId = $officeRow['office_id'];

// Form handling
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    if ($_POST['password'] !== $_POST['confirm_password']) {
        $_SESSION['error'] = 'Passwords do not match.';
        header('Location: register_user.php');
        exit();
    }

    if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $_POST['password'])) {
        $_SESSION['error'] = 'Password must contain at least 8 characters, one uppercase letter, one number, and one special character.';
        header('Location: register_user.php');
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (username, fullname, email, password, role, office_id) 
            VALUES ('$username', '$fullname', '$email', '$hashed_password', '$role', '$officeId')";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = 'New user registered successfully.';
        header('Location: register_user.php');
        exit();
    } else {
        $_SESSION['error'] = 'Error: ' . mysqli_error($conn);
        header('Location: register_user.php');
        exit();
    }
}

// Status toggle
if (isset($_GET['deactivate'])) {
    $userId = $_GET['deactivate'];
    $conn->query("UPDATE users SET status = 'inactive' WHERE id = $userId");
    header('Location: register_user.php');
    exit();
}

if (isset($_GET['activate'])) {
    $userId = $_GET['activate'];
    $conn->query("UPDATE users SET status = 'active' WHERE id = $userId");
    header('Location: register_user.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register User</title>
    <?php include '../includes/links.php'; ?>
</head>
<body>
<div class="d-flex">
    <?php include 'include/sidebar.php'; ?>
    <div class="container-fluid p-4">
        <?php include 'include/topbar.php'; ?>

        <!-- Alerts -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo $_SESSION['success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="row">
            <!-- Registration Form -->
            <div class="col-md-6 mt-5">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-person-plus-fill me-2"></i>Register New User</h5>
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
                                    <input type="password" class="form-control" id="password" name="password"
                                           pattern="(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}"
                                           title="Must contain at least 8 characters, one uppercase letter, one number, and one special character"
                                           required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 d-flex align-items-end mb-3">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-check-circle me-1"></i>Register
                                    </button>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>

                            <input type="hidden" name="role" value="office_user">
                            <input type="hidden" name="office_id" value="<?php echo $officeId; ?>">
                        </form>
                    </div>
                </div>
            </div>

            <!-- Registered Users Table -->
            <div class="col-md-6 mt-5">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-people-fill me-2"></i>Registered Users in Your Office</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $query = "SELECT id, fullname, email, role, status FROM users WHERE office_id = $officeId";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";

                                $role = htmlspecialchars($row['role']);
                                switch ($role) {
                                    case 'super_admin':
                                        $badgeClass = 'badge bg-dark';
                                        break;
                                    case 'office_admin':
                                        $badgeClass = 'badge bg-warning text-dark';
                                        break;
                                    case 'office_user':
                                        $badgeClass = 'badge bg-info text-dark';
                                        break;
                                    case 'admin':
                                        $badgeClass = 'badge bg-danger';
                                        break;
                                    default:
                                        $badgeClass = 'badge bg-secondary';
                                }
                                echo "<td><span class='$badgeClass'>$role</span></td>";

                                $status = htmlspecialchars($row['status']);
                                $statusClass = $status === 'active' ? 'badge bg-success' : 'badge bg-secondary';
                                echo "<td><span class='$statusClass'>$status</span></td>";

                                echo "<td>";
                                if ($row['status'] === 'active' && $row['role'] !== 'office_admin') {
                                    echo "<a href='?deactivate=" . $row['id'] . "' class='btn btn-warning btn-sm'>Deactivate</a>";
                                } elseif ($row['status'] === 'inactive') {
                                    echo "<a href='?activate=" . $row['id'] . "' class='btn btn-success btn-sm'>Activate</a>";
                                } else {
                                    echo "<span class='text-muted'>Admin</span>";
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
