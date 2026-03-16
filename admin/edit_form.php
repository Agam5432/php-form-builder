<?php
include('../config/database.php');

$form_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($form_id <= 0) {
    die("Invalid form ID");
}

$success = "";
$error = "";

// Update form details
if (isset($_POST['update_form'])) {
    $name = isset($_POST['name']) ? trim($_POST['name']) : "";
    $description = isset($_POST['description']) ? trim($_POST['description']) : "";

    if ($name == "") {
        $error = "Form name is required.";
    } else {
        $stmt = $conn->prepare("UPDATE forms SET name = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $description, $form_id);

        if ($stmt->execute()) {
            $success = "Form updated successfully.";
        } else {
            $error = "Unable to update form.";
        }
    }
}

// Add multiple new fields at once
if (isset($_POST['save_fields'])) {
    $types = isset($_POST['field_type']) ? $_POST['field_type'] : [];
    $labels = isset($_POST['label']) ? $_POST['label'] : [];
    $placeholders = isset($_POST['placeholder']) ? $_POST['placeholder'] : [];
    $options_list = isset($_POST['options']) ? $_POST['options'] : [];
    $requireds = isset($_POST['required']) ? $_POST['required'] : [];

    if (count($types) == 0) {
        $error = "Please add at least one field.";
    } else {
        $added_any = false;

        for ($i = 0; $i < count($types); $i++) {
            $type = isset($types[$i]) ? trim($types[$i]) : "";
            $label = isset($labels[$i]) ? trim($labels[$i]) : "";
            $placeholder = isset($placeholders[$i]) ? trim($placeholders[$i]) : "";
            $option = isset($options_list[$i]) ? trim($options_list[$i]) : "";
            $required = isset($requireds[$i]) ? (int)$requireds[$i] : 0;

            // Radio / Checkbox must have options
            if (($type == "radio" || $type == "checkbox") && $option == "") {
                $error = "Please enter options for radio or checkbox fields.";
                break;
            }

            if ($type != "" && $label != "") {
                $order_q = $conn->prepare("SELECT COALESCE(MAX(field_order), 0) + 1 AS next_order FROM fields WHERE form_id = ?");
                $order_q->bind_param("i", $form_id);
                $order_q->execute();
                $order_result = $order_q->get_result()->fetch_assoc();
                $field_order = (int)$order_result['next_order'];

                $stmt = $conn->prepare("INSERT INTO fields (form_id, field_type, label, placeholder, options, required, field_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssii", $form_id, $type, $label, $placeholder, $option, $required, $field_order);

                if ($stmt->execute()) {
                    $added_any = true;
                }
            }
        }

        if ($error == "" && $added_any) {
            $success = "Fields added successfully.";
        } elseif ($error == "" && !$added_any) {
            $error = "Please add at least one valid field.";
        }
    }
}

// Fetch form data
$form_stmt = $conn->prepare("SELECT * FROM forms WHERE id = ? AND deleted_at IS NULL");
$form_stmt->bind_param("i", $form_id);
$form_stmt->execute();
$form_result = $form_stmt->get_result();
$form = $form_result->fetch_assoc();

if (!$form) {
    die("Form not found.");
}

// Fetch existing fields
$field_q = $conn->prepare("SELECT * FROM fields WHERE form_id = ? ORDER BY field_order ASC, id ASC");
$field_q->bind_param("i", $form_id);
$field_q->execute();
$fields = $field_q->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Form</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">

    <h2>Edit Form</h2>

    <?php if ($success != "") { ?>
        <div class="success auto-hide"><?php echo htmlspecialchars($success); ?></div>
    <?php } ?>

    <?php if ($error != "") { ?>
        <div class="error-box auto-hide"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <form method="POST">
        <label>Form Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($form['name']); ?>" required>

        <label>Description</label>
        <textarea name="description"><?php echo htmlspecialchars($form['description']); ?></textarea>

        <button type="submit" name="update_form">Update Form</button>
    </form>

    <hr>

    <h3>Existing Fields</h3>

    <table>
        <tr>
            <th>S.No</th>
            <th>Type</th>
            <th>Label</th>
            <th>Placeholder</th>
            <th>Options</th>
            <th>Required</th>
            <th>Action</th>
        </tr>

        <?php
        $sn = 1;
        while ($field = $fields->fetch_assoc()) {
        ?>
            <tr>
                <td><?php echo $sn++; ?></td>
                <td><?php echo htmlspecialchars($field['field_type']); ?></td>
                <td><?php echo htmlspecialchars($field['label']); ?></td>
                <td><?php echo htmlspecialchars($field['placeholder']); ?></td>
                <td><?php echo htmlspecialchars($field['options']); ?></td>
                <td><?php echo $field['required'] ? 'Yes' : 'No'; ?></td>
                <td>
                    <div class="action-buttons">
                        <button type="button" class="edit-btn" onclick="location.href='edit_field.php?id=<?php echo $field['id']; ?>&form_id=<?php echo $form_id; ?>'">✏ Edit</button>
                        <button type="button" class="delete-btn" onclick="deleteField(<?php echo $field['id']; ?>, <?php echo $form_id; ?>)">🗑 Delete</button>
                    </div>
                </td>
            </tr>
        <?php } ?>
    </table>

    <hr>

    <h3>Add Multiple Fields</h3>

    <form method="POST" onsubmit="return validateMultipleFields()">
        <table id="field_builder">
            <tr>
                <th>Type</th>
                <th>Label</th>
                <th>Placeholder</th>
                <th>Options</th>
                <th>Required</th>
                <th>Action</th>
            </tr>

            <tr>
                <td>
                    <select name="field_type[]" onchange="toggleFieldInputs(this)">
                        <option value="text">Text</option>
                        <option value="email">Email</option>
                        <option value="number">Number</option>
                        <option value="textarea">Textarea</option>
                        <option value="radio">Radio</option>
                        <option value="checkbox">Checkbox</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="label[]" required>
                </td>
                <td>
                    <input type="text" name="placeholder[]" class="placeholder-input">
                </td>
                <td>
                    <input type="text" name="options[]" class="options-input" placeholder="Option1,Option2,Option3" disabled>
                </td>
                <td>
                    <select name="required[]">
                        <option value="1">Required</option>
                        <option value="0">Optional</option>
                    </select>
                </td>
                <td>
                    <button type="button" class="delete-btn" onclick="removeRow(this)">Remove</button>
                </td>
            </tr>
        </table>

        <br>

        <div class="action-buttons">
            <button type="button" class="view-btn" onclick="addRow()">+ Add Field</button>
            <button type="submit" name="save_fields">Save Fields</button>
        </div>
    </form>

    <br>

    <div class="action-buttons">
        <button type="button" class="view-btn" onclick="location.href='../public/form.php?id=<?php echo $form_id; ?>'">👁 View Form</button>
        <button type="button" class="home-btn" onclick="location.href='index.php'">Home</button>
    </div>

</div>

<script>
function toggleFieldInputs(selectElement) {
    let row = selectElement.closest("tr");
    let placeholderInput = row.querySelector(".placeholder-input");
    let optionsInput = row.querySelector(".options-input");

    let type = selectElement.value;

    if (type === "radio" || type === "checkbox") {
        placeholderInput.value = "";
        placeholderInput.disabled = true;
        placeholderInput.placeholder = "Not allowed";

        optionsInput.disabled = false;
        optionsInput.placeholder = "Option1,Option2,Option3";
    } else {
        optionsInput.value = "";
        optionsInput.disabled = true;
        optionsInput.placeholder = "Not required";

        placeholderInput.disabled = false;
        placeholderInput.placeholder = "";
    }
}

function addRow() {
    let table = document.getElementById("field_builder");
    let row = table.insertRow();

    row.innerHTML = `
        <td>
            <select name="field_type[]" onchange="toggleFieldInputs(this)">
                <option value="text">Text</option>
                <option value="email">Email</option>
                <option value="number">Number</option>
                <option value="textarea">Textarea</option>
                <option value="radio">Radio</option>
                <option value="checkbox">Checkbox</option>
            </select>
        </td>
        <td>
            <input type="text" name="label[]" required>
        </td>
        <td>
            <input type="text" name="placeholder[]" class="placeholder-input">
        </td>
        <td>
            <input type="text" name="options[]" class="options-input" placeholder="Option1,Option2,Option3" disabled>
        </td>
        <td>
            <select name="required[]">
                <option value="1">Required</option>
                <option value="0">Optional</option>
            </select>
        </td>
        <td>
            <button type="button" class="delete-btn" onclick="removeRow(this)">Remove</button>
        </td>
    `;
}

function removeRow(btn) {
    let row = btn.parentNode.parentNode;
    let table = document.getElementById("field_builder");

    if (table.rows.length > 2) {
        row.remove();
    } else {
        alert("At least one row must remain.");
    }
}

function deleteField(id, formId) {
    if (confirm("Are you sure you want to delete this field?")) {
        window.location.href = "delete_field.php?id=" + id + "&form_id=" + formId;
    }
}

function validateMultipleFields() {
    let types = document.getElementsByName("field_type[]");
    let labels = document.getElementsByName("label[]");
    let options = document.getElementsByName("options[]");

    for (let i = 0; i < labels.length; i++) {
        if (labels[i].value.trim() === "") {
            alert("Please fill all field labels.");
            labels[i].focus();
            return false;
        }

        if ((types[i].value === "radio" || types[i].value === "checkbox") && options[i].value.trim() === "") {
            alert("Please enter options for radio or checkbox fields.");
            options[i].focus();
            return false;
        }
    }

    return true;
}

// Hide success/error messages automatically
setTimeout(function () {
    let alerts = document.querySelectorAll(".auto-hide");
    alerts.forEach(function(alertBox) {
        alertBox.style.display = "none";
    });
}, 3000);
</script>

</body>
</html>