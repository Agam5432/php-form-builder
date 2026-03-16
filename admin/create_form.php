<?php
include('../config/database.php');

$error = "";
$success = "";

if(isset($_POST['submit'])){

$name = trim($_POST['name']);
$description = trim($_POST['description']);

if($name == ""){

$error = "Form name is required";

}else{

// Check duplicate form
$stmt = $conn->prepare("SELECT id FROM forms WHERE LOWER(name)=LOWER(?) AND deleted_at IS NULL");
$stmt->bind_param("s",$name);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){

$error = "Form already exists";

}else{

$stmt = $conn->prepare("INSERT INTO forms(name,description) VALUES(?,?)");
$stmt->bind_param("ss",$name,$description);
$stmt->execute();

$success = "Form created successfully";

}

}

}
?>

<link rel="stylesheet" href="../assets/css/style.css">

<div class="container">

<h2>Create Form</h2>

<?php if(!empty($error)){ ?>
<div class="error-box auto-hide"><?php echo htmlspecialchars($error); ?></div>
<?php } ?>

<?php if(!empty($success)){ ?>
<div class="success auto-hide"><?php echo htmlspecialchars($success); ?></div>
<?php } ?>

<form method="POST" onsubmit="return validateForm()">

<label>Form Name</label>
<input type="text" name="name" required>

<label>Description</label>
<textarea name="description"></textarea>

<button name="submit">Create Form</button>

</form>

<br>

<button onclick="location.href='index.php'">Back</button>
<button type="button" class="home-btn" onclick="location.href='index.php'">Home</button>

</div>

<script src="../assets/js/validation.js"></script>