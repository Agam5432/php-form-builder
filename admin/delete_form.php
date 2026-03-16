<?php
include('../config/database.php');

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    $stmt = $conn->prepare("UPDATE forms SET deleted_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: view_forms.php");
exit;