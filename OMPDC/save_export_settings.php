<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csv_header = $_POST['csv_header'] ?? '';
    $csv_footer = $_POST['csv_footer'] ?? '';
    $pdf_header = $_POST['pdf_header'] ?? '';
    $pdf_footer = $_POST['pdf_footer'] ?? '';
    $upload_dir = '../uploads/';
    $export_logo_path = '../img/logo.jpg';

    if (!is_dir('../config')) mkdir('../config', 0755, true);
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    if (!empty($_FILES['export_logo']['tmp_name'])) {
        $file_tmp = $_FILES['export_logo']['tmp_name'];
        $file_name = basename($_FILES['export_logo']['name']);
        $target_file = $upload_dir . time() . '_' . $file_name;

        if (move_uploaded_file($file_tmp, $target_file)) {
            $export_logo_path = $target_file;
        }
    } else {
        if (file_exists('../config/export_settings.json')) {
            $current = json_decode(file_get_contents('../config/export_settings.json'), true);
            $export_logo_path = $current['export_logo'] ?? $export_logo_path;
        }
    }

    $settings = [
        'csv_header' => trim($csv_header),
        'csv_footer' => trim($csv_footer),
        'pdf_header' => trim($pdf_header),
        'pdf_footer' => trim($pdf_footer),
        'export_logo' => $export_logo_path
    ];

    file_put_contents('../config/export_settings.json', json_encode($settings, JSON_PRETTY_PRINT));
    header("Location: settings.php?tab=export&status=success");
    exit();
}
?>
