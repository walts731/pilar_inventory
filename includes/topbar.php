<!-- Top Bar -->
<div class="topbar d-flex justify-content-between align-items-center p-3 bg-light border-bottom">
    <button class="toggle-sidebar btn btn-outline-dark" id="toggleSidebar">
        <i class="bi bi-list"></i>
    </button>

    <!-- Dynamic Page Title -->
    <h5 class="mb-0">
        <?php
            // Set dynamic title based on the current page
            $currentPage = basename($_SERVER['PHP_SELF'], ".php");
            if ($currentPage === 'dashboard') {
                echo "System Admin Dashboard";
            } elseif ($currentPage === 'assets') {
                echo "Assets Management";
            } elseif ($currentPage === 'profile') {
                echo "User Profile";
            } elseif ($currentPage === 'settings') {
                echo "Settings";
            } elseif ($currentPage === 'requests') {
                echo "Asset Requests";
            } elseif ($currentPage === 'asset_categories') {
                echo "Asset Categories";
            } elseif ($currentPage === 'users') {
                echo "Users Management";
            } elseif ($currentPage === 'offices') {
                echo "Offices Management";
            } elseif ($currentPage === 'reports') {
                echo "Reports";
            } elseif ($currentPage === 'audit_trail') {
                echo "Audit Trail";
            } elseif ($currentPage === 'user_roles') {
                echo "User Roles Management";
            } elseif ($currentPage === 'user_permissions') {
                echo "User Permissions Management";
            } elseif ($currentPage === 'asset_requests_history') {
                echo "Asset Requests History";
            } elseif ($currentPage === 'asset_requests_approval') {
                echo "Asset Requests Approval";
            } elseif ($currentPage === 'asset_requests_rejected') {
                echo "Rejected Asset Requests";
            } elseif ($currentPage === 'asset_requests_approved') {
                echo "Approved Asset Requests";
            } elseif ($currentPage === 'asset_requests_pending') {
                echo "Pending Asset Requests";
            } elseif ($currentPage === 'notifications') {
                echo "Notifications";
            } elseif ($currentPage === 'overall_inventory') {
                echo "Overall Inventory";  // Title for overall inventory page
            } elseif ($currentPage === 'office_inventory' && isset($_GET['office_id'])) {
                // Fetch the office name based on the office_id passed in the URL
                $officeId = $_GET['office_id'];
                include('../connect.php');
                $officeQuery = "SELECT office_name FROM offices WHERE id = $officeId";
                $officeResult = mysqli_query($conn, $officeQuery);
                $officeData = mysqli_fetch_assoc($officeResult);
                echo "Assets Management > " . $officeData['office_name'];
            } else {
                echo "System Admin Dashboard"; // Default title
            }
        ?>
    </h5>

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
