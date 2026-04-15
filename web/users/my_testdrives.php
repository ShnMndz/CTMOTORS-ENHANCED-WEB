<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html>
<head>
<title>My Test Drives</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

<!-- Global CSS -->
<link rel="stylesheet" href="/citimotorsweb/web/global.css">

<style>
body { background:#0f0f0f; color:#fff; }

.box {
    max-width:800px;
    margin:40px auto;
    background:#1a1a1a;
    padding:20px;
    border-radius:12px;
}

table { color:#fff; }
</style>
</head>

<body>

<div class="box">

<h3>My Test Drives</h3>

<?php
$stmt = $conn->prepare("
    SELECT td.*, v.model_name, v.model_variant
    FROM test_drives td
    JOIN vehicles v ON td.vehicle_id = v.id
    WHERE td.user_id = ?
    ORDER BY td.id DESC
");

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php if ($result->num_rows > 0): ?>

<table class="table table-dark table-hover mt-3">
    <thead>
        <tr>
            <th>Vehicle</th>
            <th>Date</th>
            <th>Time</th>
            <th>Message</th>
        </tr>
    </thead>

    <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['model_name'] ?> (<?= $row['model_variant'] ?>)</td>
            <td><?= $row['date'] ?></td>
            <td><?= $row['time'] ?></td>
            <td><?= $row['message'] ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php else: ?>
<p>No bookings yet.</p>
<?php endif; ?>

<a href="profile.php" class="btn btn-secondary w-100 mt-3">Back</a>

</div>

</body>
</html>