<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $reportSettings = [
    'frequency' => $_POST['report_frequency'],
    'email' => $_POST['report_email'],
    'time' => $_POST['report_time']
  ];

  file_put_contents('../config/report_settings.json', json_encode($reportSettings));
  header('Location: settings.php?status=report_saved');
  exit;
}
?>
