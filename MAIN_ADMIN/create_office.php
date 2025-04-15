<?php
session_start();
include('../connect.php');

// Handle form submission to insert new office
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['office_name'])) {
    $office_name = trim($_POST['office_name']);

    if (!empty($office_name)) {
        $stmt = $conn->prepare("INSERT INTO offices (office_name) VALUES (?)");
        $stmt->bind_param("s", $office_name);

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Office created successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }

        $stmt->close();
    } else {
        $message = "<div class='alert alert-warning'>Please enter an office name.</div>";
    }
}

// Fetch offices to display
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
        <?php include 'include/sidebar.php'; ?>
        <div class="container-fluid">
            <?php include '../includes/topbar.php'; ?>

            <div class="container mt-5">
                <?php echo $message; ?>
                <div class="row">
                    <!-- Left Column: Create Office -->
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-lg">
                            <div class="card-header bg-success text-white">
                                <h3>Create New Office</h3>
                            </div>
                            <div class="card-body">
                                <form action="create_office.php" method="POST">
                                    <div class="mb-3">
                                        <label for="office_name" class="form-label">Office Name</label>
                                        <input type="text" name="office_name" id="office_name" class="form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">Create Office</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Select Office -->
                    <div class="col-md-6 mb-4">
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
                                                echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['office_name']) . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary mt-3">Select Office</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div> <!-- End of row -->
            </div>
        </div>
    </div>
    <?php include '../includes/script.php'; ?>
</body>

</html>
