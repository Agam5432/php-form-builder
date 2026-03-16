<?php
include('../config/database.php');

if(isset($_GET['id'])){
    $id = (int)$_GET['id'];

    $stmt = $conn->prepare("UPDATE forms SET deleted_at = NULL WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: deleted_forms.php");
exit;