<!-- Top Bar -->
<div class="topbar d-flex justify-content-between align-items-center p-3 bg-light border-bottom">
    <button class="toggle-sidebar btn btn-outline-dark" id="toggleSidebar">
        <i class="bi bi-list"></i>
    </button>
    
    <h5 class="mb-0">System Admin Dashboard</h5>

    <div class="d-flex align-items-center">
        <div class="dropdown me-3">
            <button class="btn btn-sm btn-outline-secondary position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                <li><a class="dropdown-item" href="#">🔔 New asset request</a></li>
                <li><a class="dropdown-item" href="#">⚠️ Low stock alert</a></li>
                <li><a class="dropdown-item" href="#">✅ Asset approved</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-center" href="notifications.php">View all notifications</a></li>
            </ul>
        </div>

        <span class="me-3"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</div>
