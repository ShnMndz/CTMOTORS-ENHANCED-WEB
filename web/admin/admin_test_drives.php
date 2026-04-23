<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

$currentPage = 'test_drives';

// UPDATE STATUS
if (isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE test_drives SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
}

// FETCH DATA
$result = $conn->query("
    SELECT td.*, v.model_name, v.model_variant 
    FROM test_drives td
    JOIN vehicles v ON td.vehicle_id = v.id
    ORDER BY td.created_at DESC
");

// PENDING COUNT
$pendingCount = $conn->query("
    SELECT COUNT(*) as total FROM test_drives WHERE status='pending'
")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Test Drive Requests</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="admin_dashboard.css">

<style>
.status-pending { color: orange; font-weight: bold; }
.status-approved { color: green; font-weight: bold; }
.status-rejected { color: red; font-weight: bold; }
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h4>Admin Panel</h4>

    <a href="admin_dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
    <a href="admin_profile.php"><i class="fas fa-user"></i>Your Profile</a>
    <a href="admin_users.php"><i class="fas fa-users"></i>Manage Users</a>
    <a href="admin_vehicles.php"><i class="fas fa-car"></i>Manage Vehicles</a>
    <a href="admin_posts.php"><i class="fas fa-newspaper"></i>Posts</a>

    <a href="admin_test_drives.php" class="active">
        <i class="fas fa-key"></i>Test Drive
        <?php if ($pendingCount > 0): ?>
            <span class="badge bg-warning text-dark"><?= $pendingCount ?></span>
        <?php endif; ?>
    </a>

    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<!-- CONTENT -->
<div class="content">

<h2 class="mb-4">🚗 Test Drive Requests</h2>

<div class="card p-3">

<table class="table table-hover table-bordered text-center align-middle">

<thead class="table-dark">
<tr>
    <th>Name</th>
    <th>Email</th>
    <th>Contact</th>
    <th>Vehicle</th>
    <th>Date</th>
    <th>Time</th>
    <th>Message</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php while($row = $result->fetch_assoc()): ?>
<tr>

    <td><?= htmlspecialchars($row['fullname']) ?></td>
    <td><?= htmlspecialchars($row['email']) ?></td>
    <td><?= htmlspecialchars($row['contact']) ?></td>

    <td>
        <?= htmlspecialchars($row['model_name']) ?><br>
        <small>(<?= htmlspecialchars($row['model_variant']) ?>)</small>
    </td>

    <td><?= $row['date'] ?></td>
    <td><?= $row['time'] ?></td>

    <!-- MESSAGE PREVIEW -->
    <td>
        <?= htmlspecialchars(mb_strimwidth($row['message'], 0, 25, "...")) ?>
        <br>
        <button class="btn btn-info btn-sm mt-1"
                data-bs-toggle="modal"
                data-bs-target="#msgModal<?= $row['id'] ?>">
            View
        </button>
    </td>

    <!-- STATUS -->
    <td class="status-<?= $row['status'] ?>">
        <?= ucfirst($row['status']) ?>

        <?php if ($row['status'] == 'pending'): ?>
            <span class="badge bg-warning text-dark">NEW</span>
        <?php endif; ?>
    </td>

    <!-- ACTION -->
    <td>

        <?php if ($row['status'] == 'pending'): ?>

            <form method="POST" class="d-inline">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <input type="hidden" name="status" value="approved">
                <button class="btn btn-success btn-sm" name="update_status">
                    <i class="fas fa-check"></i>
                </button>
            </form>

            <form method="POST" class="d-inline">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <input type="hidden" name="status" value="rejected">
                <button class="btn btn-danger btn-sm" name="update_status">
                    <i class="fas fa-times"></i>
                </button>
            </form>

        <?php elseif ($row['status'] == 'approved'): ?>

            <form method="POST" class="d-inline">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <input type="hidden" name="status" value="completed">
                <button class="btn btn-primary btn-sm" name="update_status">
                    Done
                </button>
            </form>

        <?php else: ?>
            <span class="text-muted">No Action</span>
        <?php endif; ?>

    </td>

</tr>
<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

<!-- ✅ MODALS OUTSIDE TABLE (FIXED BUG) -->
<?php 
$result->data_seek(0);
while($row = $result->fetch_assoc()): 
?>

<div class="modal fade" id="msgModal<?= $row['id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white">

      <div class="modal-header">
        <h5 class="modal-title">Customer Message</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <?= nl2br(htmlspecialchars($row['message'])) ?>
      </div>

    </div>
  </div>
</div>

<?php endwhile; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>