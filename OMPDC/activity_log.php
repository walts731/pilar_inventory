<?php
session_start();
include('../connect.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs</title>
    <?php include '../includes/links.php'; ?>
</head>

<body>
    <div class="d-flex">
        <?php include '../includes/sidebar.php'; ?>
        <div class="container-fluid">
            <?php include '../includes/topbar.php'; ?>

            <div class="container mt-4">
                <h4 class="mb-4">System Activity Log</h4>

                <!-- Filter Form -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label for="user_id" class="form-label">User</label>
                        <select name="user_id" id="user_id" class="form-select">
                            <option value="">All Users</option>
                            <?php
                            $users = mysqli_query($conn, "SELECT id, fullname FROM users");
                            while ($user = mysqli_fetch_assoc($users)) {
                                $selected = ($_GET['user_id'] ?? '') == $user['id'] ? 'selected' : '';
                                echo "<option value='{$user['id']}' $selected>" . htmlspecialchars($user['fullname']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="office_id" class="form-label">Office</label>
                        <select name="office_id" id="office_id" class="form-select">
                            <option value="">All Offices</option>
                            <?php
                            $offices = mysqli_query($conn, "SELECT id, office_name FROM offices");
                            while ($office = mysqli_fetch_assoc($offices)) {
                                $selected = ($_GET['office_id'] ?? '') == $office['id'] ? 'selected' : '';
                                echo "<option value='{$office['id']}' $selected>" . htmlspecialchars($office['office_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="module" class="form-label">Module</label>
                        <input type="text" name="module" id="module" value="<?= htmlspecialchars($_GET['module'] ?? '') ?>" class="form-control" placeholder="Module name">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Date Range</label>
                        <div class="input-group">
                            <input type="date" name="start_date" class="form-control" value="<?= $_GET['start_date'] ?? '' ?>">
                            <span class="input-group-text">to</span>
                            <input type="date" name="end_date" class="form-control" value="<?= $_GET['end_date'] ?? '' ?>">
                        </div>
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="activity_logs.php" class="btn btn-secondary">Reset</a>
                    </div>
                </form>

                <!-- Activity Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Date & Time</th>
                                <th>User</th>
                                <th>Office</th>
                                <th>Module</th>
                                <th>Action</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Build dynamic WHERE clause
                            $where = [];
                            if (!empty($_GET['user_id'])) {
                                $user_id = intval($_GET['user_id']);
                                $where[] = "l.user_id = $user_id";
                            }
                            if (!empty($_GET['office_id'])) {
                                $office_id = intval($_GET['office_id']);
                                $where[] = "l.office_id = $office_id";
                            }
                            if (!empty($_GET['module'])) {
                                $module = mysqli_real_escape_string($conn, $_GET['module']);
                                $where[] = "l.module LIKE '%$module%'";
                            }
                            if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
                                $start = $_GET['start_date'] . " 00:00:00";
                                $end = $_GET['end_date'] . " 23:59:59";
                                $where[] = "l.datetime BETWEEN '$start' AND '$end'";
                            }

                            $filter_sql = implode(" AND ", $where);
                            $query = "
                    SELECT l.datetime, u.fullname, u.username, o.office_name, l.module, l.action, l.ip_address
                    FROM system_logs l
                    JOIN users u ON l.user_id = u.id
                    JOIN offices o ON l.office_id = o.id
                    " . ($filter_sql ? "WHERE $filter_sql" : "") . "
                    ORDER BY l.datetime DESC
                ";

                            $result = mysqli_query($conn, $query);

                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . date("M d, Y h:i:s A", strtotime($row['datetime'])) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['fullname']) . " (" . $row['username'] . ")</td>";
                                    echo "<td>" . htmlspecialchars($row['office_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['module']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['action']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['ip_address']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>No activity logs found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <?php include '../includes/script.php'; ?>
</body>

</html>