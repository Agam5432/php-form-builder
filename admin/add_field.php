<?php
include('../config/database.php');

$forms = mysqli_query($conn, "SELECT id, name FROM forms WHERE deleted_at IS NULL ORDER BY name ASC");

$success = "";
$error = "";

if(isset($_POST['save_fields'])){

    $form_id = isset($_POST['form_id']) ? (int)$_POST['form_id'] : 0;

    $types = isset($_POST['field_type']) ? $_POST['field_type'] : [];
    $labels = isset($_POST['label']) ? $_POST['label'] : [];
    $placeholders = isset($_POST['placeholder']) ? $_POST['placeholder'] : [];
    $options_list = isset($_POST['options']) ? $_POST['options'] : [];
    $requireds = isset($_POST['required']) ? $_POST['required'] : [];

    if($form_id <= 0){
        $error = "Please select a form.";
    } elseif(count($types) == 0){
        $error = "Please add at least one field.";
    } else {

        $added = false;

        for($i = 0; $i < count($types); $i++){

            $type = trim($types[$i]);
            $label = trim($labels[$i]);
            $placeholder = trim($placeholders[$i]);
            $option = trim($options_list[$i]);
            $required = (int)$requireds[$i];

            if($label == ""){
                continue;
            }

            // Radio / Checkbox must have options
            if(($type == "radio" || $type == "checkbox") && $option == ""){
                $error = "Please enter options for radio or checkbox fields.";
                break;
            }

            $order = $i + 1;

            $stmt = $conn->prepare("INSERT INTO fields(form_id, field_type, label, placeholder, required, options, field_order) VALUES(?,?,?,?,?,?,?)");
            $stmt->bind_param("isssisi", $form_id, $type, $label, $placeholder, $required, $option, $order);
            $stmt->execute();

            $added = true;
        }

        if($error == "" && $added){
            $success = "Fields added successfully.";
        } elseif($error == "" && !$added){
            $error = "Please fill at least one valid field.";
        }
    }
}
?>

<link rel="stylesheet" href="../assets/css/style.css">

<div class="container">

    <h2>Add Fields</h2>

    <?php if($error != ""){ ?>
        <div class="error-box auto-hide"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <?php if($success != ""){ ?>
        <div class="success auto-hide"><?php echo htmlspecialchars($success); ?></div>
    <?php } ?>

    <form method="POST">

        <label>Select Form</label>
        <select name="form_id" required>
            <option value="">Select Form</option>

            <?php while($f = mysqli_fetch_assoc($forms)){ ?>
                <option value="<?php echo $f['id']; ?>">
                    <?php echo htmlspecialchars($f['name']); ?>
                </option>
            <?php } ?>
        </select>

        <br><br>

        <table id="field_table">

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

        <button type="button" onclick="addRow()">+ Add Field</button>

        <br><br>

        <button type="submit" name="save_fields">Save Fields</button>
        <button type="button" class="home-btn" onclick="location.href='index.php'">Home</button>

    </form>

</div>

<script>
function toggleFieldInputs(selectElement){
    let row = selectElement.closest("tr");
    let placeholderInput = row.querySelector(".placeholder-input");
    let optionsInput = row.querySelector(".options-input");

    let type = selectElement.value;

    if(type === "radio" || type === "checkbox"){
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

function addRow(){
    let table = document.getElementById("field_table");
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

function removeRow(btn){
    let table = document.getElementById("field_table");
    if(table.rows.length > 2){
        btn.parentNode.parentNode.remove();
    } else {
        alert("At least one field row must remain.");
    }
}
</script>