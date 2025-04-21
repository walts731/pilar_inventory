<?php
session_start();
require '../include/log_activity.php'; // Include the logging function


// Load export settings
$exportSettings = [];
if (file_exists('../config/export_settings.json')) {
  $exportSettings = json_decode(file_get_contents('../config/export_settings.json'), true);
}
$csv_header = $exportSettings['csv_header'] ?? '';
$csv_footer = $exportSettings['csv_footer'] ?? '';
$pdf_header = $exportSettings['pdf_header'] ?? '';
$pdf_footer = $exportSettings['pdf_footer'] ?? '';
$export_logo = $exportSettings['export_logo'] ?? '../img/logo.jpg';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Settings</title>
  <?php include '../includes/links.php'; ?>

  <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" />
  <link rel="stylesheet" href="../css/office_settings.css" />
</head>

<body>
  <div class="d-flex">
    <?php include 'include/sidebar.php'; ?>

    <div class="container-fluid">
      <?php include 'include/topbar.php'; ?>

      <div class="container mt-5">
        <div class="row">
          <div class="col-md-3">
            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
              <a class="nav-link" id="appearance-tab" data-bs-toggle="pill" href="#appearance" role="tab">Appearance</a>
              <a class="nav-link" id="export-tab" data-bs-toggle="pill" href="#export" role="tab">Export Settings</a>
            </div>
          </div>

          <div class="col-md-9">
            <div class="tab-content" id="v-pills-tabContent">
              <!-- Appearance Settings -->
              <div class="tab-pane fade" id="appearance" role="tabpanel">
                <h4>Appearance Settings</h4>
                <form action="save_appearance.php" method="POST">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="dark_mode" value="1" id="dark-mode" checked>
                    <label class="form-check-label" for="dark-mode">Dark Mode</label>
                  </div>
                  <button type="submit" class="btn btn-primary mt-2">Save Changes</button>
                </form>
              </div>

              <!-- Export Settings -->
              <div class="tab-pane fade" id="export" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h4>Export Customization</h4>
                  <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#templatesModal">
                    View Saved Templates
                  </button>
                </div>

                <div class="row">
                  <!-- Left Column: Inputs -->
                  <div class="col-md-7">
                    <form action="save_export_settings.php" method="POST" enctype="multipart/form-data" id="export-form">
                      <h5>CSV Export</h5>
                      <div class="mb-3">
                        <label class="form-label">CSV and PDF Header</label>
                        <input type="text" class="form-control preview-input" name="csv_header" id="csv-header" value="<?= htmlspecialchars($csv_header) ?>">
                      </div>
                      <div class="mb-3">
                        <label class="form-label">CSV and PDF Footer</label>
                        <input type="text" class="form-control preview-input" name="csv_footer" id="csv-footer" value="<?= htmlspecialchars($csv_footer) ?>">
                      </div>
                      <h5 class="mt-4">Export Logo</h5>
                      <div class="mb-3">
                        <label class="form-label">Upload Logo</label>
                        <input type="file" class="form-control" name="export_logo" id="export-logo">
                        <div class="mt-2">
                          <strong>Current Logo:</strong><br>
                          <img src="<?= htmlspecialchars($export_logo) ?>" id="logo-preview" alt="Logo Preview" width="120" style="border:1px solid #ccc;padding:5px;">
                        </div>
                      </div>

                      <div class="mb-3">
                        <label class="form-label">Logo Position</label>
                        <div class="form-check form-check-inline">
                          <input class="form-check-input" type="radio" name="logo_position" id="logo-left" value="left" checked>
                          <label class="form-check-label" for="logo-left">Left</label>
                        </div>
                        <div class="form-check form-check-inline">
                          <input class="form-check-input" type="radio" name="logo_position" id="logo-right" value="right">
                          <label class="form-check-label" for="logo-right">Right</label>
                        </div>
                      </div>

                      <button type="submit" class="btn btn-primary">Save Template</button>

                    </form>
                  </div>

                  <!-- Right Column: Preview -->
                  <div class="col-md-5">
                    <h5 class="mt-1">Live Preview</h5>
                    <div class="paper-preview mb-4">
                      <div class="paper-header">
                        <img src="<?= htmlspecialchars($export_logo) ?>" alt="Live Logo" id="live-logo">
                        <h5 id="live-pdf-header"><?= htmlspecialchars($pdf_header) ?></h5>
                      </div>
                      <div class="paper-content">
                        <p>[ Exported data will appear here... ]</p>
                      </div>
                      <div class="paper-footer">
                        <em id="live-pdf-footer"><?= htmlspecialchars($pdf_footer) ?></em>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="templatesModal" tabindex="-1" aria-labelledby="templatesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="templatesModalLabel">Saved Export Templates</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Template list with View buttons -->
          <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <strong>Template 1:</strong> Header: "Inventory Custodian Slip", Footer: "Signatories", Logo: logo1.png
              </div>
              <button class="btn btn-outline-primary btn-sm">View</button>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <strong>Template 2:</strong> Header: "Requisition and Issue Slip", Footer: "Signatories", Logo: logo2.png
              </div>
              <button class="btn btn-outline-primary btn-sm">View</button>
            </li>
            <!-- Add more templates here as needed -->
          </ul>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>


  <?php include '../includes/script.php'; ?>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>

  <script>
    $(document).ready(function() {
      $('#officeInventoryTable').DataTable();

      // Live text preview
      $('#pdf-header, #pdf-footer').on('input', function() {
        const id = $(this).attr('id');
        $('#live-' + id).text($(this).val());
      });

      // Logo preview
      $('#export-logo').on('change', function(event) {
        const reader = new FileReader();
        reader.onload = function(e) {
          $('#logo-preview').attr('src', e.target.result);
          $('#live-logo').attr('src', e.target.result);
        };
        if (event.target.files[0]) {
          reader.readAsDataURL(event.target.files[0]);
        }
      });

      // Logo position change (left or right)
      $('input[name="logo_position"]').on('change', function() {
        const position = $('input[name="logo_position"]:checked').val();
        if (position === 'left') {
          $('#live-logo').css('float', 'left');
        } else {
          $('#live-logo').css('float', 'right');
        }
      });
    });
  </script>
</body>

</html>