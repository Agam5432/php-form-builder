<?php
include('../config/database.php');

$q = "SELECT * FROM forms WHERE deleted_at IS NULL ORDER BY id DESC";
$r = mysqli_query($conn, $q);

$sn = 1;
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Forms</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">

    <h2>Forms</h2>

    <table>
        <tr>
            <th>S.No</th>
            <th>Form Name</th>
            <th>Description</th>
            <th>Action</th>
        </tr>

        <?php if(mysqli_num_rows($r) > 0) { ?>

            <?php while($row = mysqli_fetch_assoc($r)) { ?>
                <tr>
                    <td><?php echo $sn++; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td>
                        <div class="action-buttons">
                            <button class="view-btn" onclick="location.href='../public/form.php?id=<?php echo $row['id']; ?>'">
                                 View
                            </button>

                            <button class="edit-btn" onclick="location.href='edit_form.php?id=<?php echo $row['id']; ?>'">
                                 Edit
                            </button>

                            <button class="delete-btn" onclick="deleteForm(<?php echo $row['id']; ?>)">
                                 Delete
                            </button>
                        </div>
                    </td>
                </tr>
            <?php } ?>

        <?php } else { ?>

            <tr>
                <td colspan="4" style="text-align:center;">No forms found.</td>
            </tr>

        <?php } ?>
    </table>

    <br>
    <button class="home-btn" onclick="location.href='index.php'">Home</button>

</div>

<script>
function deleteForm(id) {
    if (confirm("Are you sure you want to delete this form?")) {
        window.location.href = "delete_form.php?id=" + id;
    }
}
</script>

</body>
</html>