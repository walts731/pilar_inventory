<!-- Top Bar -->
<div class="topbar d-flex justify-content-between align-items-center p-3 bg-light border-bottom">
    <button class="toggle-sidebar btn btn-outline-dark" id="toggleSidebar">
        <i class="bi bi-list"></i>
    </button>

    <!-- Dynamic Page Title with Office Name -->
    <h5 class="mb-0">
        <?php
        // Determine current page
        $currentPage = basename($_SERVER['PHP_SELF'], ".php");
        $title = "";
        $officeName = "";

        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            $adminId = $_SESSION['user_id'];
            $officeQuery = $conn->query("SELECT offices.office_name 
                                         FROM users 
                                         JOIN offices ON users.office_id = offices.id 
                                         WHERE users.id = $adminId");
            if ($officeRow = $officeQuery->fetch_assoc()) {
                $officeName = $officeRow['office_name'];
            }
        }

        switch ($currentPage) {
            case 'admin_dashboard': $title = "Admin Dashboard"; break;
            case 'transfer': $title = "Transfer Assets"; break;
            case 'assets': $title = "Assets Inventory"; break;
            case 'profile': $title = "User Profile"; break;
            case 'settings': $title = "Settings"; break;
            case 'requests': $title = "Asset Requests"; break;
            case 'request': $title = "Borrow Requests"; break;
            case 'asset_categories': $title = "Asset Categories"; break;
            case 'users': $title = "Users Management"; break;
            case 'create_office': $title = "Offices Management"; break;
            case 'reports': $title = "Reports"; break;
            case 'borrow_asset': $title = "Borrow Assets"; break;
            case 'audit_trail': $title = "Audit Trail"; break;
            case 'user_roles': $title = "User Roles Management"; break;
            case 'analytics': $title = "Analytics"; break;
            case 'archive': $title = "Archives"; break;
            case 'activity_log': $title = "Activity Logs"; break;
            case 'user_permissions': $title = "User Permissions Management"; break;
            case 'asset_requests_history': $title = "Asset Requests History"; break;
            case 'asset_requests_approval': $title = "Asset Requests Approval"; break;
            case 'asset_requests_rejected': $title = "Rejected Asset Requests"; break;
            case 'asset_requests_approved': $title = "Approved Asset Requests"; break;
            case 'asset_requests_pending': $title = "Pending Asset Requests"; break;
            case 'notifications': $title = "Notifications"; break;
            case 'overall_inventory': $title = "Overall Inventory"; break;
            case 'office_inventory':
                if (isset($_GET['office_id'])) {
                    include('../connect.php');
                    $officeId = (int) $_GET['office_id'];
                    $officeQuery = "SELECT office_name FROM offices WHERE id = $officeId";
                    $officeResult = mysqli_query($conn, $officeQuery);
                    if ($officeData = mysqli_fetch_assoc($officeResult)) {
                        $title = "Assets Management > " . $officeData['office_name'];
                    } else {
                        $title = "Assets Management";
                    }
                } else {
                    $title = "Assets Management";
                }
                break;
            default:
                $title = "System Admin Dashboard";
        }

        // Optional office name for specific pages (like users, assets, requests)
        if (isset($_GET['office_id']) && in_array($currentPage, ['users', 'assets', 'requests'])) {
            include_once('../connect.php');
            $officeId = (int) $_GET['office_id'];
            $officeQuery = "SELECT office_name FROM offices WHERE id = $officeId";
            $officeResult = mysqli_query($conn, $officeQuery);
            if ($officeData = mysqli_fetch_assoc($officeResult)) {
                $title .= " > " . $officeData['office_name'];
            }
        }

        // Echo the title and office name
        echo htmlspecialchars($title);
        if (!empty($officeName)) {
            echo " <small class='text-muted' style='font-size: 0.9rem;'>| " . htmlspecialchars($officeName) . "</small>";
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
