<?php
session_start();
include('../connect.php');

// Check if office_id is passed via GET request
if (isset($_GET['office_id'])) {
    $office_id = $_GET['office_id'];

    // Fetch office name for display
    $stmt = $conn->prepare("SELECT office_name FROM offices WHERE id = ?");
    $stmt->bind_param("i", $office_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $office_name = "";
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $office_name = $row['office_name'];
    }

    // Fetch users belonging to this office
    $stmt = $conn->prepare("SELECT id, username, fullname, email, role, status FROM users WHERE office_id = ?");
    $stmt->bind_param("i", $office_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
} else {
    echo "No office selected.";
    exit;
}

// Handle status change (inactive or active)
if (isset($_GET['change_status']) && isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $new_status = $_GET['change_status'];

    // Update user status
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $user_id);
    $stmt->execute();

    // Redirect to the same page to refresh user status
    header("Location: users.php?office_id=" . $office_id);
    exit;
}

// Handle user update form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user_id'])) {
    $user_id = $_POST['edit_user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Update the user's information
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $email, $role, $user_id);
    $stmt->execute();

    // Redirect to the same page to refresh the list of users
    header("Location: users.php?office_id=" . $office_id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - <?php echo htmlspecialchars($office_name); ?></title>
    <?php include '../includes/links.php'; ?>
    <style>
        /* Prevent scrolling in the register user form */
        .card {
            height: 100%;
        }

        .card-body {
            overflow-y: auto;
            /* Add scrolling only in the card-body if necessary */
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <?php include 'include/sidebar.php'; ?>
        <div class="container-fluid">
        <?php include 'include/topbar.php'; ?>

            <div class="container mt-5">
                <!-- Back Button -->
                <div class="container mt-3 mb-3">
                    <a href="create_office.php" class="btn btn-secondary">Back to Office Creation</a>
                </div>

                <div class="row">
                    <!-- Column 1: Add New User Form -->
<div class="col-md-4">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h3>Add New User to Office</h3>
        </div>
        <div class="card-body">
            <form action="register_user.php" method="POST" onsubmit="return validatePassword();">
                <input type="hidden" name="office_id" value="<?php echo $office_id; ?>">

                <div class="mb-3">
                    <label for="fullname" class="form-label">Full Name</label>
                    <input type="text" name="fullname" id="fullname" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password (min. 8 characters)</label>
                    <input type="password" name="password" id="password" class="form-control" 
                        pattern=".{8,}" title="Password must be at least 8 characters long" required>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" 
                        class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" id="role" class="form-select" required>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                        <option value="office_user">Office User</option>
                        <option value="office_admin">Office Admin</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success w-100">Register</button>
            </form>
        </div>
    </div>
</div>


                    <!-- Column 2: Current Users List -->
                    <div class="col-md-8">
                        <div class="card shadow-lg mt-4 mt-md-0">
                            <div class="card-header bg-info text-white">
                                <h3>Current Users</h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Fullname</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $user_result->fetch_assoc()) { ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td>
                                                    <?php
                                                    $role = htmlspecialchars($row['role']);
                                                    // Add badge based on the role
                                                    if ($role == 'admin') {
                                                        echo '<span class="badge bg-primary">' . $role . '</span>';
                                                    } else if ($role == 'user') {
                                                        echo '<span class="badge bg-secondary">' . $role . '</span>';
                                                    } else {
                                                        echo '<span class="badge bg-light text-dark">' . $role . '</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status = htmlspecialchars($row['status']);
                                                    // Add badge based on the status
                                                    if ($status == 'active') {
                                                        echo '<span class="badge bg-success">' . $status . '</span>';
                                                    } else if ($status == 'inactive') {
                                                        echo '<span class="badge bg-danger">' . $status . '</span>';
                                                    } else {
                                                        echo '<span class="badge bg-warning text-dark">' . $status . '</span>';
                                                    }
                                                    ?>
                                                </td>

                                                <td>
                                                    <!-- Edit Icon -->
                                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>">
                                                        <i class="bi bi-pencil-square"></i> Edit
                                                    </button>

                                                    <!-- Status Toggle Button -->
                                                    <?php if ($row['role'] != 'super_admin') { ?>
                                                        <?php if ($row['status'] == 'active') { ?>
                                                            <a href="?office_id=<?php echo $office_id; ?>&user_id=<?php echo $row['id']; ?>&change_status=inactive" class="btn btn-warning btn-sm">Deactivate</a>
                                                        <?php } else { ?>
                                                            <a href="?office_id=<?php echo $office_id; ?>&user_id=<?php echo $row['id']; ?>&change_status=active" class="btn btn-success btn-sm">Activate</a>
                                                        <?php } ?>
                                                    <?php } else { ?>
                                                        <!-- If user is super_admin, show a message instead of Deactivate option -->
                                                        <button class="btn btn-secondary btn-sm" disabled>Cannot Deactivate</button>
                                                    <?php } ?>
                                                </td>
                                            </tr>

                                            <!-- Edit Modal -->
                                            <?php include '../modal/edit_user_modal.php'; ?>
                                        <?php } ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div> <!-- End of row -->
            </div>
        </div>
    </div>
    <?php include '../includes/script.php'; ?>
</body>

</html>