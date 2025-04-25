<?php
session_start();
require '../connect.php';

// Fixed asset ID
$asset_id = 1;

// Fetch asset with id = 1, including category_name and office_name
$stmt = $conn->prepare("
    SELECT 
        assets.id, assets.asset_name, categories.category_name, assets.description, assets.quantity, 
        assets.unit, assets.status, assets.acquisition_date, assets.red_tagged, assets.last_updated, 
        assets.value, assets.qr_code, offices.office_name 
    FROM assets
    JOIN categories ON assets.category = categories.id
    JOIN offices ON assets.office_id = offices.id
    WHERE assets.id = ?
");
$stmt->bind_param("i", $asset_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Asset not found.";
    exit();
}

$asset = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Asset Information</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
<?php include 'includes/navbar.php'; ?>

    <h2 class="mb-4">Asset Details </h2>
    <table class="table table-bordered">
        <tr><th>Asset Name</th><td><?= htmlspecialchars($asset['asset_name']) ?></td></tr>
        <tr><th>Category</th><td><?= htmlspecialchars($asset['category_name']) ?></td></tr>
        <tr><th>Description</th><td><?= $asset['description'] ?></td></tr>
        <tr><th>Quantity</th><td><?= $asset['quantity'] ?></td></tr>
        <tr><th>Unit</th><td><?= $asset['unit'] ?></td></tr>
        <tr><th>Status</th><td><?= $asset['status'] ?></td></tr>
        <tr><th>Acquisition Date</th><td><?= $asset['acquisition_date'] ?></td></tr>
        <tr><th>Office</th><td><?= $asset['office_name'] ?></td></tr>
        <tr><th>Red Tagged</th><td><?= $asset['red_tagged'] ? 'Yes' : 'No' ?></td></tr>
        <tr><th>Last Updated</th><td><?= $asset['last_updated'] ?></td></tr>
        <tr><th>Value</th><td>₱<?= number_format($asset['value'], 2) ?></td></tr>
        <tr><th>QR Code</th>
            <td>
                <?php if (!empty($asset['qr_code'])): ?>
                    <img src="../qrcodes/<?= htmlspecialchars($asset['qr_code']) ?>" alt="QR Code" width="100">
                <?php else: ?>
                    <em>No QR Code</em>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
