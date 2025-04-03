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
    $stmt = $conn->prepare("SELECT id, username, fullname, email, role FROM users WHERE office_id = ?");
    $stmt->bind_param("i", $office_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
} else {
    echo "No office selected.";
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
            overflow-y: auto; /* Add scrolling only in the card-body if necessary */
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <?php include '../includes/sidebar.php'; ?>
        <div class="container-fluid">
            <?php include '../includes/topbar.php'; ?>

            <div class="container mt-5">
                <div class="row">
                    <!-- Column 1: Add New User Form -->
                    <div class="col-md-4">
                        <div class="card shadow-lg">
                            <div class="card-header bg-primary text-white">
                                <h3>Add New User to Office</h3>
                            </div>
                            <div class="card-body">
                                <form action="register_user.php" method="POST">
                                    <input type="hidden" name="office_id" value="<?php echo $office_id; ?>">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" name="username" id="username" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" name="email" id="email" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" name="password" id="password" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Role</label>
                                        <select name="role" id="role" class="form-select" required>
                                            <option value="admin">Admin</option>
                                            <option value="user">User</option>
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
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $user_result->fetch_assoc()) { ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td><?php echo htmlspecialchars($row['role']); ?></td>
                                                <td>
                                                    <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                                    <a href="delete_user.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                                </td>
                                            </tr>
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


