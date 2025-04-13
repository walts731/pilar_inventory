<?php
include '../connect.php'; // or your actual db connection file

$office_name = '';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Fetch the user's office_id
    $stmt = $conn->prepare("SELECT office_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($office_id);
    $stmt->fetch();
    $stmt->close();

    // Now fetch the office_name based on office_id
    if ($office_id) {
        $stmt2 = $conn->prepare("SELECT office_name FROM offices WHERE id = ?");
        $stmt2->bind_param("i", $office_id);
        $stmt2->execute();
        $stmt2->bind_result($office_name);
        $stmt2->fetch();
        $stmt2->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Inventory Navbar</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/user.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="../img/logo.jpg" alt="Logo" width="30" height="30" class="d-inline-block align-top mr-2">
            Pilar Asset Inventory System
        </a>
        <small class="text-light"><?= htmlspecialchars($office_name) ?></small>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto align-items-center">
                <li class="nav-item <?= ($currentPage == 'user_dashboard.php') ? 'active' : '' ?>">
                    <a class="nav-link" href="user_dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item <?= ($currentPage == 'inventory.php') ? 'active' : '' ?>">
                    <a class="nav-link" href="inventory.php">Asset Inventory</a>
                </li>
                <li class="nav-item <?= ($currentPage == 'reports.php') ? 'active' : '' ?>">
                    <a class="nav-link" href="reports.php">Reports</a>
                </li>
                <li class="nav-item <?= ($currentPage == 'analytics.php') ? 'active' : '' ?>">
                    <a class="nav-link" href="analytics.php">Analytics</a>
                </li>
                <!-- Profile Picture -->
                <li class="nav-item ml-3">
                    <img src="../img/josh.jpg" alt="Profile" class="profile-img" id="toggleSidebar">
                </li>
            </ul>
        </div>
    </nav>

    <!-- Off-Canvas Right Sidebar -->
    <div class="offcanvas-right" id="profileSidebar">
        <div class="text-center">
            <img src="../img/josh.jpg" alt="Profile Picture" class="profile-img mt-3 mb-2" style="width: 80px; height: 80px;">
            <h5 class="mb-0">Joshua Escano</h5>
            <small class="text-muted">User</small>
        </div>
        <hr>
        <div class="px-3">
            <a href="profile.php" class="btn btn-outline-primary btn-block mb-2">Profile</a>
            <a href="archives.php" class="btn btn-outline-secondary btn-block mb-2">Archives</a> <!-- ✅ New -->
            <a href="../../logout.php" class="btn btn-outline-danger btn-block mb-3">Logout</a>

            <!-- Dark Mode Toggle -->
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="darkModeToggle">
                <label class="custom-control-label" for="darkModeToggle">Dark Mode</label>
            </div>
        </div>
        <button type="button" class="close position-absolute" id="closeSidebar" style="top: 10px; right: 15px;">&times;</button>
    </div>



    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script>
        // Sidebar toggle functionality
        $('#toggleSidebar').click(function() {
            $('#profileSidebar').addClass('show');
        });

        $('#closeSidebar').click(function() {
            $('#profileSidebar').removeClass('show');
        });

        // Optional: close sidebar when clicking outside
        $(document).mouseup(function(e) {
            var sidebar = $("#profileSidebar");
            if (!sidebar.is(e.target) && sidebar.has(e.target).length === 0 && sidebar.hasClass('show')) {
                sidebar.removeClass('show');
            }
        });

        // Dark Mode Toggle
        $('#darkModeToggle').change(function() {
            $('body').toggleClass('dark-mode');
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>