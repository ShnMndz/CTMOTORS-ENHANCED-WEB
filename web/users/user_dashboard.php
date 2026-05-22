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

// VEHICLE PREFERENCES
$models = !empty($user['interested_models']) ? explode(',', $user['interested_models']) : [];
$fuel   = $user['fuel_preference'] ?? null;
$budget = $user['budget_range'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>User Dashboard - CITI MOTORS</title>

<link rel="stylesheet" href="user_dashboard.css">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

</head>

<body>

<div class="dashboard">

    <?php include 'user_sidebar.php'; ?>

    <!-- MAIN PANEL -->
    <main class="panel">

        <!-- HERO -->
        <section class="hero-card">

            <div class="hero-left">

                <span class="welcome-badge">
                    <i class="fas fa-user-circle"></i>
                    USER DASHBOARD
                </span>

                <h1>
                    Welcome back,
                    <span><?= htmlspecialchars($user['fullname']) ?></span>
                </h1>

                <p>
                    Manage your account, view test drive appointments,
                    update your vehicle preferences, and stay connected with CITI MOTORS.
                </p>

                <div class="hero-actions">

                    <a href="profile.php?tab=personal" class="btn btn-danger">
                        <i class="fas fa-user-edit"></i>
                        Edit Profile
                    </a>

                    <a href="../home.php" class="btn btn-outline-light">
                        <i class="fas fa-home"></i>
                        Homepage
                    </a>

                </div>

            </div>

            <div class="hero-right">

                <div class="quick-stat">
                    <small>Current Date & Time</small>
                    <h5><?= $currentDateTime ?></h5>
                </div>

                <div class="quick-stat">
                    <small>Recent Appointments</small>
                    <h2><?= $testdrives->num_rows ?></h2>
                </div>

            </div>

        </section>

        <!-- QUICK STATS -->
        <section class="stats-grid">

            <div class="stat-card">

                <div class="stat-icon bg-primary">
                    <i class="fas fa-car"></i>
                </div>

                <div>
                    <p>Saved Vehicles</p>
                    <h3>0</h3>
                </div>

            </div>

            <div class="stat-card">

                <div class="stat-icon bg-success">
                    <i class="fas fa-calendar-check"></i>
                </div>

                <div>
                    <p>Appointments</p>
                    <h3><?= $testdrives->num_rows ?></h3>
                </div>

            </div>

            <div class="stat-card">

                <div class="stat-icon bg-danger">
                    <i class="fas fa-gas-pump"></i>
                </div>

                <div>
                    <p>Fuel Preference</p>
                    <h3>
                        <?= !empty($fuel) ? htmlspecialchars($fuel) : 'N/A' ?>
                    </h3>
                </div>

            </div>

        </section>

        <!-- MAIN GRID -->
        <div class="dashboard-grid">

            <!-- LEFT COLUMN -->
            <div class="left-column">

                <!-- ACCOUNT INFO -->
                <div class="modern-card">

                    <div class="card-header-custom">

                        <h4>Account Information</h4>

                        <a href="profile.php?tab=personal">
                            Edit
                        </a>

                    </div>

                    <div class="profile-info">

                        <div class="profile-item">
                            <span>Full Name</span>
                            <strong><?= htmlspecialchars($user['fullname']) ?></strong>
                        </div>

                        <div class="profile-item">
                            <span>Email Address</span>
                            <strong><?= htmlspecialchars($user['email']) ?></strong>
                        </div>

                        <div class="profile-item">
                            <span>Birthday</span>
                            <strong>
                                <?= !empty($user['date_of_birth'])
                                    ? date("F d, Y", strtotime($user['date_of_birth']))
                                    : 'Not set' ?>
                            </strong>
                        </div>

                        <div class="profile-item">
                            <span>Address</span>
                            <strong>
                                <?= !empty($user['address'])
                                    ? htmlspecialchars($user['address'])
                                    : 'Not set' ?>
                            </strong>
                        </div>

                        <div class="profile-item">
                            <span>Preferred Branch</span>
                            <strong>
                                <?= !empty($user['preferred_branch'])
                                    ? htmlspecialchars($user['preferred_branch'])
                                    : 'Not set' ?>
                            </strong>
                        </div>

                    </div>

                </div>

                <!-- TEST DRIVE -->
                <div class="modern-card">

                    <div class="card-header-custom">

                        <h4>Recent Test Drive Requests</h4>

                        <span class="mini-badge">
                            <?= $testdrives->num_rows ?> Recent
                        </span>

                    </div>

                    <?php if ($testdrives->num_rows > 0): ?>

                    <div class="table-responsive mt-3">

                        <table class="table align-middle">

                            <thead>
                                <tr>
                                    <th>Vehicle</th>
                                    <th>Schedule</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>

                            <?php
                            $testdrives->data_seek(0);

                            while($row = $testdrives->fetch_assoc()):

                                $status = strtolower($row['status']);
                            ?>

                                <tr>

                                    <td>

                                        <strong>
                                            <?= htmlspecialchars($row['model_name']) ?>
                                        </strong>

                                        <br>

                                        <small class="text-muted">
                                            <?= htmlspecialchars($row['model_variant']) ?>
                                        </small>

                                    </td>

                                    <td>

                                        <?= $row['date'] ?>

                                        <br>

                                        <small class="text-muted">
                                            <?= $row['time'] ?>
                                        </small>

                                    </td>

                                    <td>

                                        <span class="status-pill status-<?= $status ?>">
                                            <?= ucfirst($status) ?>
                                        </span>

                                    </td>

                                    <td>

                                        <?php if ($status == 'approved' && !empty($row['admin_message'])): ?>

                                            <button class="btn btn-success btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modal<?= $row['id'] ?>">

                                                View

                                            </button>

                                        <?php elseif ($status == 'rejected' && !empty($row['admin_notes'])): ?>

                                            <button class="btn btn-danger btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modal<?= $row['id'] ?>">

                                                View

                                            </button>

                                        <?php else: ?>

                                            <span class="text-muted small">
                                                —
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                            </tbody>

                        </table>

                    </div>

                    <?php else: ?>

                        <div class="empty-state">

                            <i class="fas fa-calendar-times"></i>

                            <p>No appointment requests yet.</p>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

            <!-- RIGHT COLUMN -->
            <div class="right-column">

                <!-- VEHICLE PREFERENCES -->
                <div class="modern-card">

                    <div class="card-header-custom">

                        <h4>Vehicle Preferences</h4>

                        <a href="profile.php?tab=preferences">
                            Update
                        </a>

                    </div>

                    <?php if (empty($models) && !$fuel && !$budget): ?>

                        <div class="empty-state">

                            <i class="fas fa-sliders-h"></i>

                            <p>No preferences added yet.</p>

                        </div>

                    <?php else: ?>

                        <?php if (!empty($models)): ?>

                            <div class="mb-4">

                                <p class="section-title">
                                    Interested Models
                                </p>

                                <div class="tag-wrapper">

                                    <?php foreach ($models as $model): ?>

                                        <span class="custom-tag">
                                            <?= htmlspecialchars(trim($model)) ?>
                                        </span>

                                    <?php endforeach; ?>

                                </div>

                            </div>

                        <?php endif; ?>

                        <?php if ($fuel): ?>

                            <div class="profile-item">
                                <span>Fuel Preference</span>
                                <strong><?= htmlspecialchars($fuel) ?></strong>
                            </div>

                        <?php endif; ?>

                        <?php if ($budget): ?>

                            <div class="profile-item mt-3">
                                <span>Budget Range</span>
                                <strong><?= htmlspecialchars($budget) ?></strong>
                            </div>

                        <?php endif; ?>

                    <?php endif; ?>

                </div>

                <!-- SAVED VEHICLES -->
                <div class="modern-card">

                    <div class="card-header-custom">

                        <h4>Saved Vehicles</h4>

                    </div>

                    <div class="empty-state">

                        <i class="fas fa-bookmark"></i>

                        <p>No saved vehicles yet.</p>

                        <a href="saved_vehicles.php" class="btn btn-dark btn-sm">
                            Browse Vehicles
                        </a>

                    </div>

                </div>

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

        <div class="modal-content bg-dark text-white border-0">

            <div class="modal-header border-secondary">

                <h5 class="modal-title">
                    <?= ucfirst($status) ?> Details
                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <?php if ($status == 'rejected'): ?>

                    <h6 class="text-danger">
                        Rejection Reason
                    </h6>

                    <p>
                        <?= htmlspecialchars($row['admin_notes'] ?: 'No reason provided') ?>
                    </p>

                <?php elseif ($status == 'approved'): ?>

                    <h6 class="text-success">
                        Admin Message
                    </h6>

                    <p>
                        <?= htmlspecialchars($row['admin_message'] ?: 'Approved without message') ?>
                    </p>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>

<?php endwhile; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>