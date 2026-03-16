
<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "form_builder";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Database connection failed");
}
?>
