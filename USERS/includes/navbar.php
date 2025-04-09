<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Inventory Navbar</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .profile-img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
        }

        .offcanvas-right {
            position: fixed;
            top: 0;
            right: -300px;
            width: 300px;
            height: 100%;
            background-color: #fff;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.3);
            z-index: 1050;
            transition: right 0.3s ease-in-out;
            padding: 20px;
        }

        .offcanvas-right.show {
            right: 0;
        }

        .offcanvas-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        body.dark-mode {
            background-color: #121212;
            color: #f1f1f1;
        }

        body.dark-mode .navbar {
            background-color: #1f1f1f !important;
        }

        body.dark-mode .offcanvas-right {
            background-color: #1f1f1f;
            color: #f1f1f1;
        }

        body.dark-mode .btn-outline-primary {
            border-color: #90caf9;
            color: #90caf9;
        }

        body.dark-mode .btn-outline-danger {
            border-color: #ef9a9a;
            color: #ef9a9a;
        }

        body.dark-mode hr {
            border-color: #444;
        }

        .custom-control-label::before {
            background-color: #ccc;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="../img/logo.jpg" alt="Logo" width="30" height="30" class="d-inline-block align-top mr-2">
            Pilar Asset Inventory System
        </a>
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
            <a href="profile.php" class="btn btn-outline-primary btn-block mb-2">View Profile</a>
            <a href="archives.php" class="btn btn-outline-secondary btn-block mb-2">Archives</a> <!-- ✅ New -->
            <a href="logout.php" class="btn btn-outline-danger btn-block mb-3">Logout</a>

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