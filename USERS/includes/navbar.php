<?php
include '../connect.php'; // DB connection

$office_name = '';
$user_name = '';
$user_role = '';
$profile_picture = 'default_profile.png'; // fallback profile picture

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Fetch user details
    $stmt = $conn->prepare("SELECT office_id, fullname, role, profile_picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($office_id, $user_name, $user_role, $profile_picture);
    $stmt->fetch();
    $stmt->close();

    // Fetch office name
    if ($office_id) {
        $stmt2 = $conn->prepare("SELECT office_name FROM offices WHERE id = ?");
        $stmt2->bind_param("i", $office_id);
        $stmt2->execute();
        $stmt2->bind_result($office_name);
        $stmt2->fetch();
        $stmt2->close();
    }
}

// Check if profile picture file exists
$profile_picture_path = "../uploads/" . $profile_picture;
if (!file_exists($profile_picture_path) || empty($profile_picture)) {
    $profile_picture_path = "../uploads/default_profile.png";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Inventory Navbar</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/user.css">
    <style>
        .offcanvas-right {
            position: fixed;
            top: 0;
            right: -300px;
            width: 300px;
            height: 100%;
            background-color: #fff;
            box-shadow: -2px 0 5px rgba(0,0,0,0.3);
            transition: right 0.3s ease-in-out;
            z-index: 1050;
        }
        .offcanvas-right.show {
            right: 0;
        }
        .profile-img {
            border-radius: 50%;
            object-fit: cover;
        }
        .dark-mode {
            background-color: #1e1e2f;
            color: #ffffff;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="../img/PILAR LOGO TRANSPARENT.png" alt="Logo" width="30" height="30" class="d-inline-block align-top mr-2">
        Pilar Asset Inventory System
    </a>
    <small class="text-light"><?= htmlspecialchars($office_name) ?></small>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto align-items-center">
            <li class="nav-item">
                <a class="nav-link" href="user_dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="inventory.php">Asset Inventory</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="reports.php">Reports</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="analytics.php">Analytics</a>
            </li>
            <li class="nav-item ml-3">
                <img src="<?= $profile_picture_path ?>" alt="Profile" class="profile-img" id="toggleSidebar" width="40" height="40">
            </li>
        </ul>
    </div>
</nav>

<!-- Sidebar -->
<div class="offcanvas-right" id="profileSidebar">
    <div class="text-center">
        <img src="<?= $profile_picture_path ?>" alt="Profile Picture" class="profile-img mt-3 mb-2" style="width: 80px; height: 80px;">
        <h5 class="mb-0"><?= htmlspecialchars($user_name) ?></h5>
        <small class="text-muted"><?= htmlspecialchars(ucfirst($user_role)) ?></small>
    </div>
    <hr>
    <div class="px-3">
        <a href="profile.php" class="btn btn-outline-primary btn-block mb-2">Profile</a>
        <a href="archives.php" class="btn btn-outline-secondary btn-block mb-2">Archives</a>
        <a href="../logout.php" class="btn btn-outline-danger btn-block mb-3">Logout</a>

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
    $('#toggleSidebar').click(function() {
        $('#profileSidebar').addClass('show');
    });

    $('#closeSidebar').click(function() {
        $('#profileSidebar').removeClass('show');
    });

    $(document).mouseup(function(e) {
        var sidebar = $("#profileSidebar");
        if (!sidebar.is(e.target) && sidebar.has(e.target).length === 0 && sidebar.hasClass('show')) {
            sidebar.removeClass('show');
        }
    });

    $('#darkModeToggle').change(function() {
        $('body').toggleClass('dark-mode');
    });
</script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
