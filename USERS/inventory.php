
<?php
// my tables

// SELECT `id`, `asset_name`, `category`, `description`, `quantity`, `unit`, `status`, `acquisition_date`, `office_id`, `red_tagged`, `last_updated`, `value`, `qr_code` FROM `assets` 

// SELECT `id`, `office_name`, `icon` FROM `offices` 

// SELECT `id`, `username`, `fullname`, `email`, `password`, `role`, `status`, `created_at`, `reset_token`, `reset_token_expiry`, `office_id` FROM `users` 

// SELECT `id`, `borrow_request_id`, `asset_id`, `user_id`, `return_date`, `condition_on_return`, `remarks`, `office_id` FROM `returned_assets` 

// SELECT `request_id`, `asset_id`, `user_id`, `office_id`, `request_date`, `status` FROM `borrow_requests` 

// SELECT `id`, `category_name`, `type` FROM `categories` 

// SELECT `action_id`, `action_name`, `office_id`, `user_id`, `category`, `quantity`, `action_date` FROM `inventory_actions`

// SELECT `log_id`, `user_id`, `activity`, `timestamp`, `module` FROM `activity_log` 

// SELECT `id`, `user_id`, `action_type`, `filter_status`, `filter_office`, `filter_category`, `filter_start_date`, `filter_end_date`, `created_at`, `file_name` FROM `archives`

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Inventory</title>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
</body>
</html>