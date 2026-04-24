<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

$currentPage = 'test_drives';

// ==========================
// UPDATE STATUS (FIXED + NOTIF)
// ==========================
if (isset($_POST['update_status'])) {

    $id = $_POST['id'];
    $status = $_POST['status'];

    $admin_notes = isset($_POST['admin_notes']) ? trim($_POST['admin_notes']) : null;
    $admin_message = isset($_POST['admin_message']) ? trim($_POST['admin_message']) : null;

    if ($status == 'rejected' && empty($admin_notes)) {
        $_SESSION['error'] = "Reject reason is required.";
        header("Location: admin_test_drives.php");
        exit();
    }

    if ($status == 'approved' && empty($admin_message)) {
        $admin_message = null;
    }

    // GET USER ID
    $stmt = $conn->prepare("SELECT user_id FROM test_drives WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if ($data) {

        $user_id = $data['user_id'];

        // UPDATE STATUS
        $stmt = $conn->prepare("
            UPDATE test_drives 
            SET status=?, admin_notes=?, admin_message=? 
            WHERE id=?
        ");
        $stmt->bind_param("sssi", $status, $admin_notes, $admin_message, $id);
        $stmt->execute();

        // NOTIFICATIONS
        $title = "";
        $message = "";

        if ($status == 'approved') {
            $title = "Test Drive Approved";
            $message = "Your test drive request has been approved.";
        } elseif ($status == 'rejected') {
            $title = "Test Drive Rejected";
            $message = "Your test drive request was rejected.";
        } elseif ($status == 'completed') {
            $title = "Test Drive Completed";
            $message = "Your test drive has been completed.";
        }

        if (!empty($title)) {
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, title, message, type, reference_id)
                VALUES (?, ?, ?, 'test_drive', ?)
            ");
            $stmt->bind_param("issi", $user_id, $title, $message, $id);
            $stmt->execute();
        }
    }

    header("Location: admin_test_drives.php");
    exit();
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
        <small><?= htmlspecialchars($row['model_variant']) ?></small>
    </td>

    <td><?= $row['date'] ?></td>
    <td><?= $row['time'] ?></td>

    <td><?= htmlspecialchars(mb_strimwidth($row['message'], 0, 25, "...")) ?></td>

    <td class="status-<?= $row['status'] ?>">
        <?= ucfirst($row['status']) ?>
    </td>

    <td>

        <?php if ($row['status'] == 'pending'): ?>

            <button class="btn btn-success btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#approveModal<?= $row['id'] ?>">
                <i class="fas fa-check"></i>
            </button>

            <button class="btn btn-danger btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#rejectModal<?= $row['id'] ?>">
                <i class="fas fa-times"></i>
            </button>

        <?php elseif ($row['status'] == 'approved'): ?>

            <form method="POST" class="d-inline">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <input type="hidden" name="status" value="completed">
                <button class="btn btn-primary btn-sm" name="update_status">Done</button>
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

<!-- ================= MODALS ================= -->

<?php
$result2 = $conn->query("
    SELECT td.*, v.model_name, v.model_variant 
    FROM test_drives td
    JOIN vehicles v ON td.vehicle_id = v.id
    ORDER BY td.created_at DESC
");
?>

<?php while($row = $result2->fetch_assoc()): ?>

<!-- APPROVE MODAL -->
<div class="modal fade" id="approveModal<?= $row['id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <form method="POST">

        <div class="modal-header">
            <h5 class="modal-title">Approve Request</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            <input type="hidden" name="status" value="approved">

            <label>Admin Message (Optional)</label>
            <textarea name="admin_message" class="form-control"></textarea>

        </div>

        <div class="modal-footer">
            <button class="btn btn-success" name="update_status">Approve</button>
        </div>

      </form>

    </div>
  </div>
</div>

<!-- REJECT MODAL -->
<div class="modal fade" id="rejectModal<?= $row['id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <form method="POST">

        <div class="modal-header">
            <h5 class="modal-title">Reject Request</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            <input type="hidden" name="status" value="rejected">

            <label>Admin Notes (Required)</label>
            <textarea name="admin_notes" class="form-control" required></textarea>

        </div>

        <div class="modal-footer">
            <button class="btn btn-danger" name="update_status">Reject</button>
        </div>

      </form>

    </div>
  </div>
</div>

<?php endwhile; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>