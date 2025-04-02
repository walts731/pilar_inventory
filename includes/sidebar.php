<!-- Sidebar -->
<div id="sidebar" class="d-flex flex-column flex-shrink-0 p-3 bg-primary text-white vh-100" style="width: 250px;">
    <a href="system_admin_dashboard.php" class="text-white text-decoration-none d-flex align-items-center mb-3">
        <img src="../img/logo.jpg" alt="Logo" width="40" class="me-2">
        <span class="fs-5 fw-bold">Pilar Inventory</span>
    </a>
    <hr>

    <?php $current_page = basename($_SERVER['PHP_SELF']); ?>

    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="system_admin_dashboard.php" class="nav-link <?php echo ($current_page == 'system_admin_dashboard.php') ? 'active bg-white text-primary' : 'text-white'; ?>">
                <i class="bi bi-house-door me-2"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="assets.php" class="nav-link <?php echo ($current_page == 'assets.php') ? 'active bg-white text-primary' : 'text-white'; ?>">
                <i class="bi bi-box me-2"></i> Manage Assets
            </a>
        </li>
        <li>
            <a href="requests.php" class="nav-link <?php echo ($current_page == 'requests.php') ? 'active bg-white text-primary' : 'text-white'; ?>">
                <i class="bi bi-file-earmark-text me-2"></i> Asset Requests
            </a>
        </li>
        <li>
            <a href="users.php" class="nav-link <?php echo ($current_page == 'users.php') ? 'active bg-white text-primary' : 'text-white'; ?>">
                <i class="bi bi-people me-2"></i> User Management
            </a>
        </li>
        <li>
            <a href="activity_log.php" class="nav-link <?php echo ($current_page == 'activity_log.php') ? 'active bg-white text-primary' : 'text-white'; ?>">
                <i class="bi bi-list-check me-2"></i> Activity Logs
            </a>
        </li>
        <li>
            <a href="reports.php" class="nav-link <?php echo ($current_page == 'reports.php') ? 'active bg-white text-primary' : 'text-white'; ?>">
                <i class="bi bi-bar-chart-line me-2"></i> Reports
            </a>
        </li>
        <li>
            <a href="analytics.php" class="nav-link <?php echo ($current_page == 'analytics.php') ? 'active bg-white text-primary' : 'text-white'; ?>">
                <i class="bi bi-graph-up me-2"></i> Analytics
            </a>
        </li>
        <li>
            <a href="archive.php" class="nav-link <?php echo ($current_page == 'archive.php') ? 'active bg-white text-primary' : 'text-white'; ?>">
                <i class="bi bi-archive me-2"></i> Archive
            </a>
        </li>
        <li>
            <a href="settings.php" class="nav-link <?php echo ($current_page == 'settings.php') ? 'active bg-white text-primary' : 'text-white'; ?>">
                <i class="bi bi-gear me-2"></i> Settings
            </a>
        </li>
    </ul>

    <hr>
    <a href="logout.php" class="btn btn-danger w-100">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>
