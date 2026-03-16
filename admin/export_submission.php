<?php
include('../config/database.php');

$submission_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($submission_id <= 0) {
    die("Invalid submission ID.");
}

// Get submission + form details
$stmt = $conn->prepare("
    SELECT submissions.id, submissions.submitted_at, forms.name AS form_name
    FROM submissions
    JOIN forms ON submissions.form_id = forms.id
    WHERE submissions.id = ?
");
$stmt->bind_param("i", $submission_id);
$stmt->execute();
$result = $stmt->get_result();
$submission = $result->fetch_assoc();

if (!$submission) {
    die("Submission not found.");
}

// Get submitted field values
$value_stmt = $conn->prepare("
    SELECT fields.label, submission_values.value
    FROM submission_values
    JOIN fields ON submission_values.field_id = fields.id
    WHERE submission_values.submission_id = ?
    ORDER BY fields.field_order ASC, fields.id ASC
");
$value_stmt->bind_param("i", $submission_id);
$value_stmt->execute();
$values = $value_stmt->get_result();

// Export folder
$export_dir = dirname(__DIR__) . '/exports';
if (!is_dir($export_dir)) {
    mkdir($export_dir, 0777, true);
}

// File path
$file_path = $export_dir . '/submissions_export.csv';

// Check if file already exists
$file_exists = file_exists($file_path);

// Open file in append mode
$file = fopen($file_path, 'a');

if (!$file) {
    die("Unable to create export file.");
}

// Add header only once
if (!$file_exists || filesize($file_path) == 0) {
    fputcsv($file, ['Form Name', 'Submission ID', 'Submission Time', 'Field Label', 'Field Value']);
}

// Append all rows of current submission
while ($row = $values->fetch_assoc()) {
    fputcsv($file, [
        $submission['form_name'],
        $submission['id'],
        date("d M Y h:i A", strtotime($submission['submitted_at'])),
        $row['label'],
        $row['value']
    ]);
}

fclose($file);

// Force download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="submissions_export.csv"');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit;