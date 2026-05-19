<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$id = $_SESSION['user_id'];

/* USER DATA */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>My Test Drives</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<link rel="stylesheet" href="user_dashboard.css">

<style>
body { background:#0f0f0f; color:#fff; }

.status-pending { color: orange; font-weight: bold; }
.status-approved { color: green; font-weight: bold; }
.status-rejected { color: red; font-weight: bold; }
.status-completed { color: #00c3ff; font-weight: bold; }

table { color:#fff; }

td.status-pending { color: #ff9800 !important; font-weight: 600; }
td.status-approved { color: #28a745 !important; font-weight: 600; }
td.status-rejected { color: #dc3545 !important; font-weight: 600; }
td.status-completed { color: #0dcaf0 !important; font-weight: 600; }
</style>

</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->
   <?php include 'user_sidebar.php'; ?>

    <!-- MAIN PANEL -->
    <main class="panel">

        <div class="box">

            <h3>
    <i class="bi bi-speedometer2"></i>
    Test Drive History
</h3>

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

        
                <table class="table table-dark table-hover mt-3 text-center align-middle">

<thead>
    <tr>
        <th>Vehicle</th>
        <th>Date</th>
        <th>Time</th>
        <th>Message</th>
        <th>Status</th>
        <th>Details</th>
    </tr>
</thead>

<tbody>
<?php while($row = $result->fetch_assoc()): ?>
    <tr>

        <td>
            <?= htmlspecialchars($row['model_name']) ?>
            <br>
            <small class="text-muted">
                <?= htmlspecialchars($row['model_variant']) ?>
            </small>
        </td>

        <td><?= date("M d, Y", strtotime($row['date'])) ?></td>
        <td><?= date("h:i A", strtotime($row['time'])) ?></td>

        <td>
            <?= htmlspecialchars(mb_strimwidth($row['message'], 0, 25, "...")) ?>
        </td>

        <td class="status-<?= $row['status'] ?>">
            <?= ucfirst($row['status']) ?>
        </td>

        <td>

            <?php if ($row['status'] == 'rejected' && !empty($row['admin_notes'])): ?>

                <button class="btn btn-sm btn-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#adminModal<?= $row['id'] ?>">
                    View Reason
                </button>

            <?php elseif ($row['status'] == 'approved' && !empty($row['admin_message'])): ?>

                <button class="btn btn-sm btn-success"
                        data-bs-toggle="modal"
                        data-bs-target="#adminModal<?= $row['id'] ?>">
                    View Message
                </button>

            <?php else: ?>

                <span class="text-muted small">
                    <?= ($row['status'] == 'pending') ? 'Waiting' : 'No details' ?>
                </span>

            <?php endif; ?>

        </td>

    </tr>
<?php endwhile; ?>
</tbody>

</table>
            </table>

            <?php else: ?>
                <p>No bookings yet.</p>
            <?php endif; ?>

            <a href="user_dashboard.php" class="btn btn-secondary w-100 mt-3">Back</a>

        </div>

    </main>

</div>

<!-- ================= MODALS ================= -->
<?php 
$result->data_seek(0);
while($row = $result->fetch_assoc()): 
?>

<div class="modal fade" id="adminModal<?= $row['id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white">

      <div class="modal-header">
        <h5 class="modal-title">
            <?= ucfirst($row['status']) ?> Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <?php if ($row['status'] == 'rejected'): ?>

            <h6 class="text-danger">Rejection Reason:</h6>
            <p><?= htmlspecialchars($row['admin_notes'] ?: 'No reason provided') ?></p>

        <?php elseif ($row['status'] == 'approved'): ?>

            <h6 class="text-success">Admin Message:</h6>
            <p><?= htmlspecialchars($row['admin_message'] ?: 'Approved without message') ?></p>

        <?php else: ?>

            <p>No additional details.</p>

        <?php endif; ?>

      </div>

    </div>
  </div>
</div>

<?php endwhile; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>