<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings</title>
  <?php include '../includes/links.php'; ?>

  <!-- DataTables CSS -->
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
  
  <!-- Additional Styling for Tabs -->
  <style>
    .tab-content {
      padding: 20px;
      border: 1px solid #ddd;
      border-top: none;
      margin-top: -1px;
    }
    .nav-pills .nav-link {
      border-radius: 0;
    }
    .nav-pills .nav-link.active {
      background-color: #007bff;
    }
  </style>
</head>
<body>
  <!-- Wrapper div for Sidebar and Content -->
  <div class="d-flex">

    <!-- Include Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="container-fluid">
      <!-- Include Topbar -->
      <?php include '../includes/topbar.php'; ?>

      <!-- Main Content Section -->
      <div class="container mt-5">
        <div class="row">
          <div class="col-md-3">
            <!-- Left Menu for Settings -->
            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
              <a class="nav-link active" id="general-settings-tab" data-bs-toggle="pill" href="#general-settings" role="tab" aria-controls="general-settings" aria-selected="true">General Settings</a>
              <a class="nav-link" id="appearance-tab" data-bs-toggle="pill" href="#appearance" role="tab" aria-controls="appearance" aria-selected="false">Appearance</a>
              <a class="nav-link" id="advanced-tab" data-bs-toggle="pill" href="#advanced" role="tab" aria-controls="advanced" aria-selected="false">Advanced</a>
              <a class="nav-link" id="backup-restore-tab" data-bs-toggle="pill" href="#backup-restore" role="tab" aria-controls="backup-restore" aria-selected="false">Backup & Restore</a>
            </div>
          </div>

          <div class="col-md-9">
            <!-- Right Content Area for Settings -->
            <div class="tab-content" id="v-pills-tabContent">
              <!-- General Settings -->
              <div class="tab-pane fade show active" id="general-settings" role="tabpanel" aria-labelledby="general-settings-tab">
                <h4>System Name and Logo</h4>
                <p>Here you can view and update your system name and logo.</p>
                <!-- Form for updating system name and logo -->
                <form action="save_general_settings.php" method="POST" enctype="multipart/form-data">
                  <div class="mb-3">
                    <label for="system-name" class="form-label">System Name</label>
                    <input type="text" class="form-control" id="system-name" name="system_name" value="Pilar Inventory Management System">
                  </div>
                  <div class="mb-3">
                    <label for="system-logo" class="form-label">Upload Logo</label>
                    <input type="file" class="form-control" id="system-logo" name="logo">
                    <br><img src="../img/logo.jpg" alt="Current Logo" width="100" height="100">
                  </div>
                  <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
              </div>

              <!-- Appearance Settings -->
              <div class="tab-pane fade" id="appearance" role="tabpanel" aria-labelledby="appearance-tab">
                <h4>Appearance Settings</h4>
                <p>Toggle dark mode for the appearance of the system.</p>
                <form action="save_appearance.php" method="POST">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="dark-mode" name="dark_mode" checked>
                    <label class="form-check-label" for="dark-mode">
                      Dark Mode
                    </label>
                  </div>
                  <button type="submit" class="btn btn-primary mt-2">Save Changes</button>
                </form>
              </div>

              <!-- Advanced Settings -->
              <div class="tab-pane fade" id="advanced" role="tabpanel" aria-labelledby="advanced-tab">
                <h4>Advanced Settings</h4>
                <p>Configure the session timeout settings.</p>
                <form action="save_advanced.php" method="POST">
                  <div class="mb-3">
                    <label for="session-timeout" class="form-label">Session Timeout (minutes)</label>
                    <input type="number" class="form-control" id="session-timeout" name="session_timeout" value="30" min="1">
                  </div>
                  <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
              </div>

              <!-- Backup & Restore Settings -->
              <div class="tab-pane fade" id="backup-restore" role="tabpanel" aria-labelledby="backup-restore-tab">
                <h4>Backup and Restore</h4>
                <p>Download a backup of your data or restore from an existing backup.</p>
                <form action="backup_restore.php" method="POST" enctype="multipart/form-data">
                  <button type="submit" name="download_backup" class="btn btn-primary">Download Backup</button>
                </form>
                <form action="backup_restore.php" method="POST" enctype="multipart/form-data" class="mt-3">
                  <div class="mb-3">
                    <label for="restore-backup" class="form-label">Restore Backup</label>
                    <input type="file" class="form-control" id="restore-backup" name="backup_file">
                  </div>
                  <button type="submit" name="restore_backup" class="btn btn-danger">Restore Backup</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include '../includes/script.php'; ?>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Bootstrap JS for tabs -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- DataTables JS -->
  <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>

  <!-- Initialize DataTables -->
  <script>
    $(document).ready(function() {
      $('#officeInventoryTable').DataTable(); // Apply DataTables on the office inventory table
    });
  </script>
</body>
</html>
