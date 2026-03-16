<?php
include('../config/database.php');

$submission_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($submission_id <= 0) {
    die("Invalid submission ID.");
}

// Submission + form details
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

// Submitted values with labels
$value_stmt = $conn->prepare("
    SELECT fields.label, fields.field_type, submission_values.value
    FROM submission_values
    JOIN fields ON submission_values.field_id = fields.id
    WHERE submission_values.submission_id = ?
    ORDER BY fields.field_order ASC, fields.id ASC
");
$value_stmt->bind_param("i", $submission_id);
$value_stmt->execute();
$values = $value_stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Submission</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">

    <h2>Submission Details</h2>

    <div class="detail-box">
        <p><strong>Form Name:</strong> <?php echo htmlspecialchars($submission['form_name']); ?></p>
        <p><strong>Submission Time:</strong> <?php echo date("d M Y h:i A", strtotime($submission['submitted_at'])); ?></p>
    </div>

    <table>
        <tr>
            <th>S.No</th>
            <th>Field Label</th>
            <th>Submitted Value</th>
        </tr>

        <?php
        $sn = 1;
        while($row = mysqli_fetch_assoc($values)){
        ?>
        <tr>
            <td><?php echo $sn++; ?></td>
            <td><?php echo htmlspecialchars($row['label']); ?></td>
            <td>
                <?php
                $value = trim((string)$row['value']);
                echo $value !== '' ? nl2br(htmlspecialchars($value)) : '-';
                ?>
            </td>
        </tr>
        <?php } ?>
    </table>

    <br>

   <div class="action-buttons">
    <button class="view-btn" type="button" onclick="history.back()">Back</button>
    <button class="edit-btn" type="button" onclick="location.href='export_submission.php?id=<?php echo $submission_id; ?>'">Export</button>
    <button type="button" class="home-btn" onclick="location.href='index.php'">Home</button>
</div>

</div>

</body>
</html>