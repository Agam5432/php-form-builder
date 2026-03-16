<?php
include('../config/database.php');

$field_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$form_id = isset($_GET['form_id']) ? (int) $_GET['form_id'] : 0;

if ($field_id <= 0 || $form_id <= 0) {
    die("Invalid request");
}

$success = "";
$error = "";

if (isset($_POST['update_field'])) {
    $field_type = trim($_POST['field_type']);
    $label = trim($_POST['label']);
    $placeholder = trim($_POST['placeholder']);
    $required = isset($_POST['required']) ? (int) $_POST['required'] : 0;

    if ($field_type == "" || $label == "") {
        $error = "Please fill all required fields.";
    } else {
        $stmt = $conn->prepare("UPDATE fields SET field_type = ?, label = ?, placeholder = ?, required = ? WHERE id = ?");
        $stmt->bind_param("sssii", $field_type, $label, $placeholder, $required, $field_id);

        if ($stmt->execute()) {
            header("Location: edit_form.php?id=" . $form_id);
            exit;
        } else {
            $error = "Unable to update field.";
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM fields WHERE id = ? AND form_id = ?");
$stmt->bind_param("ii", $field_id, $form_id);
$stmt->execute();
$result = $stmt->get_result();
$field = $result->fetch_assoc();

if (!$field) {
    die("Field not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Field</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">

    <h2>Edit Field</h2>

    <?php if ($success != "") { ?>
        <div class="success auto-hide"><?php echo $success; ?></div>
    <?php } ?>

    <?php if ($error != "") { ?>
        <div class="error-box auto-hide"><?php echo $error; ?></div>
    <?php } ?>

    <form method="POST">
        <label>Field Type</label>
        <select name="field_type" required>
            <option value="text" <?php if($field['field_type'] == 'text') echo 'selected'; ?>>Text</option>
            <option value="email" <?php if($field['field_type'] == 'email') echo 'selected'; ?>>Email</option>
            <option value="number" <?php if($field['field_type'] == 'number') echo 'selected'; ?>>Number</option>
            <option value="textarea" <?php if($field['field_type'] == 'textarea') echo 'selected'; ?>>Textarea</option>
            <option value="radio" <?php if($field['field_type'] == 'radio') echo 'selected'; ?>>Radio</option>
            <option value="checkbox" <?php if($field['field_type'] == 'checkbox') echo 'selected'; ?>>Checkbox</option>
        </select>

        <label>Label</label>
        <input type="text" name="label" value="<?php echo htmlspecialchars($field['label']); ?>" required>

        <label>Placeholder</label>
        <input type="text" name="placeholder" value="<?php echo htmlspecialchars($field['placeholder']); ?>">

        <label>Required</label>
        <select name="required" required>
            <option value="1" <?php if($field['required'] == 1) echo 'selected'; ?>>Required</option>
            <option value="0" <?php if($field['required'] == 0) echo 'selected'; ?>>Optional</option>
        </select>

        <div class="action-buttons">
            <button type="submit" name="update_field">Update Field</button>
            <button type="button" class="home-btn" onclick="goBack()">
                Back
            </button>
            <button type="button" class="home-btn" onclick="location.href='edit_form.php?id=<?php echo $form_id; ?>'">Home</button>
        </div>
    </form>

</div>
<script>

function goBack(){

    window.history.back();

}

</script>
</body>
</html>