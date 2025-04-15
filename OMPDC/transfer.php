<?php
session_start();
require '../connect.php';



// Fetch all offices for destination selection
$offices = $conn->query("SELECT * FROM offices ORDER BY office_name ASC");

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assetId = $_POST['asset_id'];
    $destinationOfficeId = $_POST['destination_office'];
    $transferQty = intval($_POST['quantity']);
    $remarks = $_POST['remarks'];

    $assetQuery = $conn->query("SELECT * FROM assets WHERE id = $assetId");
    $asset = $assetQuery->fetch_assoc();

    if ($asset) {
        if ($asset['quantity'] < $transferQty) {
            $error = "Not enough quantity available.";
        } else {
            $newQty = $asset['quantity'] - $transferQty;
            $conn->query("UPDATE assets SET quantity = $newQty WHERE id = $assetId");

            $existingAssetQuery = $conn->query("SELECT * FROM assets WHERE asset_name = '{$asset['asset_name']}' AND office_id = $destinationOfficeId");
            if ($existingAssetQuery->num_rows > 0) {
                $existingAsset = $existingAssetQuery->fetch_assoc();
                $updatedQty = $existingAsset['quantity'] + $transferQty;
                $conn->query("UPDATE assets SET quantity = $updatedQty, last_updated = NOW() WHERE id = {$existingAsset['id']}");
            } else {
                $stmt = $conn->prepare("INSERT INTO assets (asset_name, category, description, quantity, unit, status, acquisition_date, office_id, red_tagged, last_updated, value, qr_code)
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)");
                $stmt->bind_param(
                    "sssisssisds",
                    $asset['asset_name'],
                    $asset['category'],
                    $asset['description'],
                    $transferQty,
                    $asset['unit'],
                    $asset['status'],
                    $asset['acquisition_date'],
                    $destinationOfficeId,
                    $asset['red_tagged'],
                    $asset['value'],
                    $asset['qr_code']
                );
                $stmt->execute();
            }

            $userId = $_SESSION['user_id'];
            $conn->query("INSERT INTO inventory_actions (action_name, office_id, user_id, category, quantity, action_date)
                          VALUES ('transfer', $destinationOfficeId, $userId, {$asset['category']}, $transferQty, NOW())");

            $success = "Asset transferred successfully.";
        }
    } else {
        $error = "Asset not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Transfer Assets</title>
    <?php include '../includes/links.php'; ?>
    <style>
        .suggestions {
            border: 1px solid #ccc;
            max-height: 200px;
            overflow-y: auto;
            position: absolute;
            background: #fff;
            width: 100%;
            z-index: 1000;
        }

        .suggestions div {
            padding: 8px;
            cursor: pointer;
        }

        .suggestions div:hover {
            background: #f0f0f0;
        }

        .quantity-suggestion {
            margin-right: 5px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .quantity-suggestion:hover {
            background-color: #0d6efd !important;
            color: white;
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <?php include '../includes/sidebar.php'; ?>
        <div class="container-fluid p-4">
            <?php include '../includes/topbar.php'; ?>

            <h3>Transfer Assets</h3>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="transfer_assets.php">
                        <div class="row">
                            <!-- Column 1: Asset Search & Current Info -->
                            <div class="col-md-4">
                                <div class="mb-3 position-relative">
                                    <label for="asset_search" class="form-label">Search Asset</label>
                                    <input type="text" id="asset_search" class="form-control" placeholder="Type to search..." autocomplete="off">
                                    <div id="suggestions" class="suggestions"></div>
                                    <input type="hidden" name="asset_id" id="asset_id">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Current Quantity</label>
                                    <input type="text" class="form-control" id="current_quantity" disabled>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Current Office</label>
                                    <input type="text" class="form-control" id="current_office" disabled>
                                </div>
                            </div>

                            <!-- Column 2: Destination and Quantity -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="destination_office" class="form-label">Destination Office</label>
                                    <select name="destination_office" id="destination_office" class="form-control" required>
                                        <option value="">-- Select Office --</option>
                                        <?php while ($o = $offices->fetch_assoc()): ?>
                                            <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['office_name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity to Transfer</label>
                                    <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
                                    <div class="mt-2" id="quickSuggestions">
                                        <span class="badge bg-secondary quantity-suggestion">5</span>
                                        <span class="badge bg-secondary quantity-suggestion">10</span>
                                        <span class="badge bg-secondary quantity-suggestion">15</span>
                                        <span class="badge bg-secondary quantity-suggestion">20</span>
                                        <span class="badge bg-secondary quantity-suggestion">50</span>
                                        <span class="badge bg-secondary quantity-suggestion">100</span>
                                    </div>
                                    <small id="quantityError" class="text-danger d-none">Not enough quantity available.</small>
                                </div>

                            </div>

                            <!-- Column 3: Remarks + Submit -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="remarks" class="form-label">Remarks (Optional)</label>
                                    <textarea name="remarks" id="remarks" class="form-control" rows="6" placeholder="Write any remarks here..."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label>&nbsp;</label><br>
                                    <button type="submit" class="btn btn-primary w-100">Transfer Asset</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/script.php'; ?>
    <script>
        $(document).ready(function() {
            $('#asset_search').on('input', function() {
                let query = $(this).val();
                if (query.length > 1) {
                    $.ajax({
                        url: 'fetch_assets.php',
                        method: 'GET',
                        data: {
                            q: query
                        },
                        success: function(data) {
                            $('#suggestions').html(data).show();
                        }
                    });
                } else {
                    $('#suggestions').hide();
                }
            });

            let availableQuantity = 0;

            $(document).on('click', '.suggestion-item', function() {
                let assetId = $(this).data('id');
                let assetName = $(this).text();
                availableQuantity = parseInt($(this).data('qty')); // store for validation
                let office = $(this).data('office');

                $('#asset_id').val(assetId);
                $('#asset_search').val(assetName);
                $('#current_quantity').val(availableQuantity);
                $('#current_office').val(office);
                $('#quantityHint').text(`Available: ${availableQuantity} units`);
                $('#quantity').attr('max', availableQuantity);
                $('#quantityError').addClass('d-none');
                $('#suggestions').hide();
            });

            $('#quantity').on('input', function() {
                const inputQty = parseInt($(this).val());
                if (inputQty > availableQuantity) {
                    $('#quantityError').removeClass('d-none');
                } else {
                    $('#quantityError').addClass('d-none');
                }
            });

            // Prevent clicking a suggestion that's too high
            $(document).on('click', '.quantity-suggestion', function() {
                const suggested = parseInt($(this).text());
                if (suggested <= availableQuantity) {
                    $('#quantity').val(suggested);
                    $('#quantityError').addClass('d-none');
                } else {
                    $('#quantity').val('');
                    $('#quantityError').removeClass('d-none').text(`You only have ${availableQuantity} available.`);
                }
            });


            // Quantity suggestion clicks
            $(document).on('click', '.quantity-suggestion', function() {
                let val = $(this).text();
                $('#quantity').val(val);
            });


            $(document).on('click', '.suggestion-item', function() {
                let assetId = $(this).data('id');
                let assetName = $(this).text();
                let quantity = $(this).data('qty');
                let office = $(this).data('office');

                $('#asset_id').val(assetId);
                $('#asset_search').val(assetName);
                $('#current_quantity').val(quantity);
                $('#current_office').val(office);
                $('#suggestions').hide();
            });

            $(document).click(function(e) {
                if (!$(e.target).closest('#asset_search').length) {
                    $('#suggestions').hide();
                }
            });
        });
    </script>
</body>

</html>