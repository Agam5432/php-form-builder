<?php
include('../config/database.php');

$q = "SELECT id, name, deleted_at FROM forms WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC";
$r = mysqli_query($conn, $q);

$sn = 1;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Deleted Forms</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">

    <h2>Deleted Forms</h2>

    <table>
        <tr>
            <th>S.No</th>
            <th>Form Name</th>
            <th>Deleted Time</th>
            <th>Action</th>
        </tr>

        <?php while($row = mysqli_fetch_assoc($r)) { ?>
            <tr>
                <td><?php echo $sn++; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo date("d M Y h:i A", strtotime($row['deleted_at'])); ?></td>
                <td>
                    <div class="action-buttons">
                        <button class="view-btn" onclick="location.href='view_deleted_form.php?id=<?php echo $row['id']; ?>'">View</button>
                        <button class="home-btn" onclick="restoreForm(<?php echo $row['id']; ?>)"> Restore</button>
                    </div>
                </td>
            </tr>
        <?php } ?>

        <?php if(mysqli_num_rows($r) == 0) { ?>
            <tr>
                <td colspan="4" style="text-align:center;">No deleted forms found.</td>
            </tr>
        <?php } ?>
    </table>

    <br>
    <button class="home-btn" onclick="location.href='index.php'">Home</button>

</div>

<script>
function restoreForm(id){
    if(confirm("Do you want to restore this form?")){
        window.location.href = "restore_form.php?id=" + id;
    }
}
</script>

</body>
</html>