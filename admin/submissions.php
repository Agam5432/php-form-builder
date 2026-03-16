<?php
include('../config/database.php');

$q = "SELECT submissions.id, forms.name, submissions.submitted_at
FROM submissions
JOIN forms ON submissions.form_id = forms.id
ORDER BY submissions.submitted_at DESC";

$r = mysqli_query($conn, $q);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Submissions</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <div class="container">

        <h2>Submissions</h2>

        <table>
            <tr>
                <th>S.No</th>
                <th>Form Name</th>
                <th>Submission Time</th>
                <th>View</th>
            </tr>

            <?php
        $sn = 1;

        if(mysqli_num_rows($r) > 0) {

            while($row = mysqli_fetch_assoc($r)) {
        ?>
            <tr>
                <td><?php echo $sn++; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo date("d M Y h:i A", strtotime($row['submitted_at'])); ?></td>
                <td>
                    <button class="view-btn" onclick="location.href='view_submission.php?id=<?php echo $row['id']; ?>'">
                        View
                    </button>
                </td>
            </tr>
            <?php
            }

        } else {
        ?>
            <tr>
                <td colspan="4" style="text-align:center;">No submissions found.</td>
            </tr>
            <?php
        }
        ?>

        </table>

        <br>
        <button class="home-btn" onclick="location.href='index.php'">Home</button>

    </div>

</body>

</html>