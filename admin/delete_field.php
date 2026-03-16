<?php
include('../config/database.php');

$field_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$form_id = isset($_GET['form_id']) ? (int) $_GET['form_id'] : 0;

if ($field_id > 0) {
    $stmt = $conn->prepare("DELETE FROM fields WHERE id = ?");
    $stmt->bind_param("i", $field_id);
    $stmt->execute();
}

header("Location: edit_form.php?id=" . $form_id);
exit;