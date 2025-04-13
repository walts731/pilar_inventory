<?php
session_start();
require_once "connect.php"; // Include database connection

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (empty($username) || empty($password)) {
        echo "<script>alert('Please fill in all fields.');</script>";
    } else {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("
            SELECT id, username, password, role, office_id 
            FROM users 
            WHERE username = ?
        ");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user["password"])) {
                // Store session variables
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];
                $_SESSION["office_id"] = $user["office_id"]; // Still storing office ID if needed

                // Redirect based on role
                switch ($user["role"]) {
                    case "super_admin":
                        header("Location: OMPDC/system_admin_dashboard.php?office=" . $user["office_id"]);
                        break;
                    case "office_admin":
                        header("Location: ADMIN/admin_dashboard.php?office=" . $user["office_id"]);
                        break;
                    case "admin";
                        header("Location: MAIN_ADMIN/admin_dashboard.php?office=" . $user["office_id"]);
                        break;
                    case "office_user";
                        header("Location: MAIN_USER/user_dashboard.php?office=" . $user["office_id"]);
                        break;
                    default:
                        header("Location: USERS/user_dashboard.php?office=" . $user["office_id"]);
                        break;
                }
                exit;
            } else {
                echo "<script>alert('Invalid username or password.');</script>";
            }
        } else {
            echo "<script>alert('Invalid username or password.');</script>";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <title>Login - Pilar Inventory Management System</title>
</head>

<body class="bg-light d-flex flex-column justify-content-center align-items-center vh-100">

    <!-- Card Container for Login Form -->
    <div class="card shadow-lg p-4 rounded" style="max-width: 400px; width: 100%;">
        <div class="card-body text-center">
            <!-- Login Title Inside the Card -->
            <h3 class="fw-bold mb-3">LOGIN</h3>

            <!-- Logo Section -->
            <img src="img/logo.jpg" alt="Website Logo" class="img-fluid mb-3" style="max-width: 100px;">

            <form action="" method="post" autocomplete="off">
                <!-- Username Field -->
                <div class="mb-3 text-start">
                    <label for="username" class="fw-bold">Username</label>
                    <input type="text" name="username" id="username" class="form-control" autocomplete="off" placeholder="Enter your username" required>
                </div>

                <!-- Password Field -->
                <div class="mb-3 text-start">
                    <label for="password" class="fw-bold">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" autocomplete="new-password" placeholder="Enter your password" required>
                        <span class="input-group-text bg-white border border-start-0 p-0" id="togglePassword" style="cursor: pointer; width: 40px; display: flex; justify-content: center; align-items: center;">
                            <i class="bi bi-eye" id="eyeIcon" style="font-size: 1rem;"></i>
                        </span>
                    </div>
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn btn-primary w-100">Login</button>

                <!-- Forgot Password Link -->
                <div class="mt-3">
                    <a href="forgot_password.php" class="text-decoration-none text-primary">Forgot Password?</a>
                </div>
            </form>
        </div>
    </div>

</body>

</html>

<!-- JavaScript to Toggle Password Visibility -->
<script>
    document.getElementById("togglePassword").addEventListener("click", function() {
        const passwordField = document.getElementById("password");
        const eyeIcon = document.getElementById("eyeIcon");

        if (passwordField.type === "password") {
            passwordField.type = "text";
            eyeIcon.classList.remove("bi-eye");
            eyeIcon.classList.add("bi-eye-slash");
        } else {
            passwordField.type = "password";
            eyeIcon.classList.remove("bi-eye-slash");
            eyeIcon.classList.add("bi-eye");
        }
    });
</script>
