<?php
include('../config/database.php');

$form_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($form_id <= 0){
    die("Invalid form ID.");
}

$stmt = $conn->prepare("SELECT * FROM forms WHERE id = ? AND deleted_at IS NOT NULL");
$stmt->bind_param("i", $form_id);
$stmt->execute();
$form_result = $stmt->get_result();
$form = $form_result->fetch_assoc();

if(!$form){
    die("Deleted form not found.");
}

$field_stmt = $conn->prepare("SELECT * FROM fields WHERE form_id = ? ORDER BY field_order ASC, id ASC");
$field_stmt->bind_param("i", $form_id);
$field_stmt->execute();
$fields = $field_stmt->get_result();

$sn = 1;
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Deleted Form</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">

    <h2>View Deleted Form</h2>

    <div class="detail-box">
        <p><strong>Form Name:</strong> <?php echo htmlspecialchars($form['name']); ?></p>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($form['description']); ?></p>
        <p><strong>Deleted Time:</strong> <?php echo date("d M Y h:i A", strtotime($form['deleted_at'])); ?></p>
    </div>

    <h3>Fields</h3>

    <table>
        <tr>
            <th>S.No</th>
            <th>Field Type</th>
            <th>Label</th>
            <th>Placeholder</th>
            <th>Options</th>
            <th>Required</th>
        </tr>

        <?php while($row = mysqli_fetch_assoc($fields)) { ?>
            <tr>
                <td><?php echo $sn++; ?></td>
                <td><?php echo htmlspecialchars($row['field_type']); ?></td>
                <td><?php echo htmlspecialchars($row['label']); ?></td>
                <td><?php echo htmlspecialchars($row['placeholder']); ?></td>
                <td><?php echo htmlspecialchars($row['options']); ?></td>
                <td><?php echo $row['required'] ? 'Yes' : 'No'; ?></td>
            </tr>
        <?php } ?>

        <?php if(mysqli_num_rows($fields) == 0) { ?>
            <tr>
                <td colspan="6" style="text-align:center;">No fields found for this form.</td>
            </tr>
        <?php } ?>
    </table>

    <br>

    <div class="action-buttons">
        <button class="view-btn" type="button" onclick="history.back()"> Back</button>
        <button class="home-btn" type="button" onclick="location.href='restore_form.php?id=<?php echo $form['id']; ?>'">Recycle</button>
    </div>

</div>

</body>
</html>