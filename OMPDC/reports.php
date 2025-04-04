<?php
session_start();
include('../connect.php');

//SELECT `id`, `asset_name`, `category`, `description`, `quantity`, `unit`, `status`, `acquisition_date`, `office_id`, `red_tagged`, `last_updated`, `value`, `qr_code` FROM `assets` 

//SELECT `request_id`, `asset_id`, `user_id`, `status`, `request_date`, `quantity`, `unit`, `description`, `office_id` FROM `asset_requests` 

//SELECT `id`, `category_name`, `type` FROM `categories` 

//SELECT `id`, `office_name` FROM `offices`

//SELECT `id`, `username`, `fullname`, `email`, `password`, `role`, `status` FROM `users`
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <?php include '../includes/links.php'; ?>
</head>

<body>
    <div class="d-flex">
        <?php include '../includes/sidebar.php'; ?>
        <div class="container-fluid">
            <?php include '../includes/topbar.php'; ?>

            <div class="container mt-5">
                
            </div>
        </div>
    </div>
    <?php include '../includes/script.php'; ?>
</body>

</html>
