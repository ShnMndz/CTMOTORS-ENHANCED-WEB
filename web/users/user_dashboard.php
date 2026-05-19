<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$id = $_SESSION['user_id'];

// USER DATA
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// TIME + DATE
date_default_timezone_set('Asia/Manila');
$currentDateTime = date("l, F d, Y - h:i A");

// ==========================
// GET LATEST 5 TEST DRIVES
// ==========================
$stmt = $conn->prepare("
    SELECT td.*, v.model_name, v.model_variant
    FROM test_drives td
    JOIN vehicles v ON td.vehicle_id = v.id
    WHERE td.user_id = ?
    ORDER BY td.id DESC
    LIMIT 5
");
$stmt->bind_param("i", $id);
$stmt->execute();
$testdrives = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Profile - CITI MOTORS</title>

<link rel="stylesheet" href="user_dashboard.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
body {
    background: #f5f7fb;
    color: #222;
}

.table {
    background: #ffffff !important;
}

.table th {
    background: #f1f3f6 !important;
    color: #333 !important;
}

.table td {
    background: #ffffff !important;
    color: #333 !important;
}

.table-hover tbody tr:hover {
    background: #f9fbff !important;
}

td.status-pending { color: #ff9800 !important; font-weight: 600; }
td.status-approved { color: #28a745 !important; font-weight: 600; }
td.status-rejected { color: #dc3545 !important; font-weight: 600; }
td.status-completed { color: #0dcaf0 !important; font-weight: 600; }
</style>

</head>

<body>

<div class="dashboard">


    <?php include 'user_sidebar.php'; ?>

    <!-- MAIN PANEL -->
    <main class="panel">

        <div class="top-bar">
            <div>
                <h3>Good day, <?= htmlspecialchars($user['fullname']) ?> 👋</h3>
                <p class="text-muted mb-0"><?= $currentDateTime ?></p>
            </div>

            <a href="../home.php" class="btn btn-outline-dark">
                Return to Homepage
            </a>
        </div>

        <div class="grid">

            <div class="card">
                <h4>Saved Vehicles</h4>
                <p>No saved vehicles yet.</p>
                <div class="card-actions">
                    <a href="saved_vehicles.php" class="btn btn-outline-dark btn-sm">Browse vehicles</a>
                </div>
            </div>

            <div class="card">
                <h4>Account Info</h4>
                <div class="info-row"><strong>Name:</strong> <?= htmlspecialchars($user['fullname']) ?></div>
                <div class="info-row"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
                <div class="info-row"><strong>Birthday:</strong> <?= !empty($user['date_of_birth']) ? date("F d, Y", strtotime($user['date_of_birth'])) : '<span class="text-muted">Not set</span>' ?></div>
                <div class="info-row"><strong>Address:</strong> <?= !empty($user['address']) ? htmlspecialchars($user['address']) : '<span class="text-muted">Not set</span>' ?></div>
                <div class="info-row"><strong>Preferred Branch:</strong> <?= !empty($user['preferred_branch']) ? htmlspecialchars($user['preferred_branch']) : '<span class="text-muted">Not set</span>' ?></div>
                <div class="card-actions">
                    <a href="profile.php?tab=personal" class="btn btn-outline-dark btn-sm">Edit profile</a>
                </div>
            </div>

            <div class="card">
                <h4>Vehicle Preferences</h4>

                <?php
                $models = !empty($user['interested_models']) ? explode(',', $user['interested_models']) : [];
                $fuel   = $user['fuel_preference'] ?? null;
                $budget = $user['budget_range']    ?? null;
                ?>

                <?php if (empty($models) && !$fuel && !$budget): ?>
                    <p class="text-muted small">No preferences set yet.</p>
                <?php else: ?>

                    <?php if (!empty($models)): ?>
                        <p class="mb-2"><strong>Interested Models</strong></p>
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            <?php foreach ($models as $model): ?>
                                <span class="badge-pill secondary"><?= htmlspecialchars(trim($model)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($fuel): ?>
                        <div class="info-row"><strong>Fuel Preference:</strong> <span class="text-muted"><?= htmlspecialchars($fuel) ?></span></div>
                    <?php endif; ?>

                    <?php if ($budget): ?>
                        <div class="info-row"><strong>Budget Range:</strong> <span class="text-muted"><?= htmlspecialchars($budget) ?></span></div>
                    <?php endif; ?>

                <?php endif; ?>

                <div class="card-actions">
                    <a href="profile.php?tab=preferences" class="btn btn-outline-dark btn-sm">Update preferences</a>
                </div>
            </div>

            <!-- TEST DRIVE -->
            <div class="card full">
                <h4>Test Drive Appointment (Recent Requests)</h4>

                <?php if ($testdrives->num_rows > 0): ?>

                <table class="table table-hover mt-3 text-center">
                    <thead>
                        <tr>
                            <th>Vehicle</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php while($row = $testdrives->fetch_assoc()): 
                        $status = strtolower($row['status']);
                    ?>
                        <tr>

                            <td>
                                <?= htmlspecialchars($row['model_name']) ?>
                                <br>
                                <small>(<?= htmlspecialchars($row['model_variant']) ?>)</small>
                            </td>

                            <td><?= $row['date'] ?></td>
                            <td><?= $row['time'] ?></td>

                            <td class="status-<?= $status ?>">
                                <?= ucfirst($status) ?>
                            </td>

                            <!-- DETAILS BUTTON -->
                           <td class="text-center">

    <div class="d-grid gap-2">

        <?php if ($status == 'approved' && !empty($row['admin_message'])): ?>

            <button class="btn btn-success btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#modal<?= $row['id'] ?>">
                View Message
            </button>

        <?php elseif ($status == 'rejected' && !empty($row['admin_notes'])): ?>

            <button class="btn btn-danger btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#modal<?= $row['id'] ?>">
                View Reason
            </button>

        <?php else: ?>

            <span class="text-muted small">No details</span>

        <?php endif; ?>

    </div>

</td>

                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>

                <?php else: ?>
                    <p>No appointment scheduled yet.</p>
                <?php endif; ?>

            </div>

        </div>

    </main>

</div>

<!-- ================= MODALS ================= -->
<?php 
$testdrives->data_seek(0);
while($row = $testdrives->fetch_assoc()):
$status = strtolower($row['status']);
?>

<div class="modal fade" id="modal<?= $row['id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white">

      <div class="modal-header">
        <h5 class="modal-title">
            <?= ucfirst($status) ?> Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <?php if ($status == 'rejected'): ?>

            <h6 class="text-danger">Rejection Reason:</h6>
            <p><?= htmlspecialchars($row['admin_notes'] ?: 'No reason provided') ?></p>

        <?php elseif ($status == 'approved'): ?>

            <h6 class="text-success">Admin Message:</h6>
            <p><?= htmlspecialchars($row['admin_message'] ?: 'Approved without message') ?></p>

        <?php endif; ?>

      </div>

    </div>
  </div>
</div>

<?php endwhile; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>