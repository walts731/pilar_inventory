<?php
session_start();
include('../connect.php');

// Fetch offices to display for selection
$sql = "SELECT * FROM offices";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Office</title>
    <?php include '../includes/links.php'; ?>
</head>

<body>
    <div class="d-flex">
        <?php include '../includes/sidebar.php'; ?>
        <div class="container-fluid">
            <?php include '../includes/topbar.php'; ?>

            <div class="container mt-5">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h3>Manage Offices</h3>
                    </div>
                    <div class="card-body">
                        <h5>Select Office to Manage</h5>
                        <form action="users.php" method="GET">
                            <select name="office_id" class="form-select" required>
                                <option value="">Select Office</option>
                                <?php
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='" . $row['id'] . "'>" . $row['office_name'] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                            <button type="submit" class="btn btn-primary mt-3">Select Office</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/script.php'; ?>
</body>

</html>
