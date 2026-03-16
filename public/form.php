<?php
include('../config/database.php');

$form_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$form_query = $conn->prepare("SELECT * FROM forms WHERE id = ? AND deleted_at IS NULL");
$form_query->bind_param("i", $form_id);
$form_query->execute();
$form_result = $form_query->get_result();
$form = $form_result->fetch_assoc();

$field_stmt = $conn->prepare("SELECT * FROM fields WHERE form_id = ? ORDER BY field_order ASC, id ASC");
$field_stmt->bind_param("i", $form_id);
$field_stmt->execute();
$field_result = $field_stmt->get_result();

$fields = [];
while ($row = $field_result->fetch_assoc()) {
    $fields[] = $row;
}

$success = "";
$error = "";

if (!$form) {
    $error = "Invalid form.";
}

if (isset($_POST['submit']) && $form) {

    if (count($fields) == 0) {
        $error = "This form has no fields. Please add fields first.";
    } else {
        $hasError = false;

        foreach ($fields as $field) {
            $field_name = 'field_' . $field['id'];
            $value = isset($_POST[$field_name]) ? $_POST[$field_name] : '';

            // if field missing from submitted form
            if (!isset($_POST[$field_name]) && (int)$field['required'] === 1) {
                $error = "This form was updated recently. Please refresh the page and submit again.";
                $hasError = true;
                break;
            }

            // required validation
            if ((int)$field['required'] === 1) {
                if (is_array($value) && count($value) === 0) {
                    $error = "Please fill all required fields.";
                    $hasError = true;
                    break;
                } elseif (!is_array($value) && trim((string)$value) === '') {
                    $error = "Please fill all required fields.";
                    $hasError = true;
                    break;
                }
            }

            // email validation
            if ($field['field_type'] === 'email' && !is_array($value) && trim((string)$value) !== '') {
                if (!filter_var(trim((string)$value), FILTER_VALIDATE_EMAIL)) {
                    $error = "Please enter a valid email address.";
                    $hasError = true;
                    break;
                }
            }
        }

        if (!$hasError) {
            $insert_submission = $conn->prepare("INSERT INTO submissions(form_id) VALUES(?)");
            $insert_submission->bind_param("i", $form_id);
            $insert_submission->execute();

            $submission_id = $conn->insert_id;

            foreach ($fields as $field) {
                $field_name = 'field_' . $field['id'];
                $value = isset($_POST[$field_name]) ? $_POST[$field_name] : '';

                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                $value = trim((string)$value);

                $insert_value = $conn->prepare("INSERT INTO submission_values(submission_id, field_id, value) VALUES(?, ?, ?)");
                $insert_value->bind_param("iis", $submission_id, $field['id'], $value);
                $insert_value->execute();
            }

            $success = "Form submitted successfully.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dynamic Form</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/validation.js"></script>
</head>
<body>
<div class="container">

    <h2><?php echo $form ? htmlspecialchars($form['name']) : 'Form'; ?></h2>

    <?php if ($form && !empty($form['description'])) { ?>
        <p><?php echo htmlspecialchars($form['description']); ?></p>
    <?php } ?>

    <?php if ($success != "") { ?>
        <div class="success auto-hide"><?php echo htmlspecialchars($success); ?></div>
    <?php } ?>

    <?php if ($error != "") { ?>
        <div class="error-box auto-hide"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <?php if ($form && count($fields) > 0) { ?>
        <form method="POST" onsubmit="return validateForm()">

            <?php foreach ($fields as $row) { ?>
                <div class="form-group">
                    <label>
                        <?php echo htmlspecialchars($row['label']); ?>
                        <?php if ((int)$row['required'] === 1) { ?>
                            <span style="color:red;">*</span>
                        <?php } ?>
                    </label>

                    <?php
                    $req = ((int)$row['required'] === 1) ? "required" : "";
                    $placeholder = htmlspecialchars((string)$row['placeholder']);
                    $options = array_filter(array_map('trim', explode(',', (string)$row['options'])));

                    if ($row['field_type'] == "text") {
                        echo "<input type='text' name='field_" . $row['id'] . "' placeholder='" . $placeholder . "' $req>";
                    }

                    elseif ($row['field_type'] == "email") {
                        echo "<input type='email' name='field_" . $row['id'] . "' placeholder='" . $placeholder . "' $req>";
                    }

                    elseif ($row['field_type'] == "number") {
                        echo "<input type='number' name='field_" . $row['id'] . "' placeholder='" . $placeholder . "' $req>";
                    }

                    elseif ($row['field_type'] == "textarea") {
                        echo "<textarea name='field_" . $row['id'] . "' placeholder='" . $placeholder . "' $req></textarea>";
                    }

                    elseif ($row['field_type'] == "radio") {
                        if (!empty($options)) {
                            echo "<div class='option-group'>";
                            foreach ($options as $opt) {
                                $safeOpt = htmlspecialchars($opt);
                                echo "<label class='inline-option'>";
                                echo "<input type='radio' name='field_" . $row['id'] . "' value='" . $safeOpt . "' $req> " . $safeOpt;
                                echo "</label>";
                            }
                            echo "</div>";
                        }
                    }

                    elseif ($row['field_type'] == "checkbox") {
                        if (!empty($options)) {
                            echo "<div class='option-group'>";
                            foreach ($options as $opt) {
                                $safeOpt = htmlspecialchars($opt);
                                echo "<label class='inline-option'>";
                                echo "<input type='checkbox' name='field_" . $row['id'] . "[]' value='" . $safeOpt . "'> " . $safeOpt;
                                echo "</label>";
                            }
                            echo "</div>";
                        }
                    }
                    ?>
                </div>
            <?php } ?>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" name="submit">Submit</button>
                <button type="button" class="home-btn" onclick="location.href='../admin/index.php'">Home</button>
            </div>
        </form>
    <?php } elseif ($form) { ?>
        <div style="background:#fff3cd;padding:10px;border-radius:4px;">
            No fields found for this form. Please add fields first.
        </div>
    <?php } ?>

</div>
</body>
</html>