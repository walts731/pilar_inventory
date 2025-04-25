<?php
session_start();
require '../connect.php';

// Allow only users
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header('Location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scan QR Code</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 QR Code Scanner -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body class="bg-light">

<div class="container py-5">
    <h2 class="mb-4 text-center">Scan Asset QR Code</h2>

    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <!-- Camera preview -->
            <div id="reader" style="width: 100%; max-width: 400px; margin: auto;"></div>

            <!-- Asset Info -->
            <div id="asset-info" class="mt-4"></div>

            <!-- Scan QR Button -->
            <button class="btn btn-success rounded-pill">
                            <a href="view_assets.php" class="text-white text-decoration-none">
                               RESULT EXAMPLE
                            </a>
                        </button>
        </div>
    </div>
</div>

<script>
function displayAsset(data) {
    const info = document.getElementById("asset-info");
    if (data.success) {
        const a = data.asset;
        info.innerHTML = `
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">${a.asset_name}</h5>
                    <p><strong>Category:</strong> ${a.category_name}</p>
                    <p><strong>Office:</strong> ${a.office_name}</p>
                    <p><strong>Quantity:</strong> ${a.quantity}</p>
                    <p><strong>Last Updated:</strong> ${a.last_updated}</p>
                </div>
            </div>
        `;
    } else {
        info.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
    }
}

// QR code scan success handler
function onScanSuccess(decodedText, decodedResult) {
    // Stop scanning after success
    html5QrCode.stop().then(() => {
        console.log("Scanning stopped.");
    }).catch(err => console.error("Stop error:", err));

    // Fetch asset info
    fetch(`get_asset.php?id=${encodeURIComponent(decodedText)}`)
        .then(res => res.json())
        .then(displayAsset)
        .catch(err => console.error("Fetch error:", err));
}

// Initialize scanner
const html5QrCode = new Html5Qrcode("reader");
html5QrCode.start(
    { facingMode: "environment" }, // Use back camera
    { fps: 10, qrbox: 250 },
    onScanSuccess
).catch(err => {
    console.error("Camera start error:", err);
    document.getElementById("asset-info").innerHTML =
        `<div class="alert alert-danger">Unable to access camera. ${err}</div>`;
});
</script>

</body>
</html>
