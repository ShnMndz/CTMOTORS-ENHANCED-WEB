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
<title>User Dashboard</title>

<link rel="stylesheet" href="user_dashboard.css">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>

/* PAGE */
body {
    background: #f5f7fb;
    color: #222;
}

/* TABLE (WHITE) */
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

/* STATUS COLORS (TABLE ONLY) */
td.status-pending {
    color: #ff9800 !important;
    font-weight: 600;
}

td.status-approved {
    color: #28a745 !important;
    font-weight: 600;
}

td.status-rejected {
    color: #dc3545 !important;
    font-weight: 600;
}

td.status-completed {
    color: #0dcaf0 !important;
    font-weight: 600;
}

</style>

</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <div class="profile-box">
            <img src="../uploads/<?= $user['profile_pic'] ?: 'default.png' ?>" class="avatar">

            <div class="username">
                <?= htmlspecialchars($user['fullname']) ?>
            </div>

            <div class="small">
                <?= htmlspecialchars($user['email']) ?>
            </div>

            <div class="small">
                Member since: <?= date("Y") ?>
            </div>

            <a href="profile.php">
                <button class="btn-edit">Edit Profile</button>
            </a>
        </div>

    
            <nav class="menu">
            <a href="user_dashboard.php" class="menu-btn active">
                <i class="fa-solid fa-user"></i>
                Profile Status
            </a>

            <a href="my_testdrives.php" class="menu-btn">
                <i class="fa-solid fa-calendar-check"></i>
                Test Drive Request
            </a>

            <a href="saved_vehicles.php" class="menu-btn">
                <i class="fa-solid fa-heart"></i>
                Saved Vehicles
            </a>
        </nav>

    </aside>

    <!-- MAIN PANEL -->
    <main class="panel">

        <div class="top-bar">
            <div>
                <h3>Dashboard</h3>
                <p class="text-muted">Welcome back 👋</p>
            </div>

            <a href="../home.php" class="btn btn-outline-dark">
                Return to Homepage
            </a>
        </div>

        <div class="grid">

            <div class="card">
                <h4>Saved Vehicles</h4>
                <p>No saved vehicles yet.</p>
            </div>

            <div class="card">
                <h4>Account Info</h4>
                <p><b>Name:</b> <?= htmlspecialchars($user['fullname']) ?></p>
                <p><b>Email:</b> <?= htmlspecialchars($user['email']) ?></p>
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
                            </tr>
                        </thead>

                        <tbody>
                        <?php while($row = $testdrives->fetch_assoc()): 
                            $status = strtolower($row['status']); // FIX
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

                            </tr>
                        <?php endwhile; ?>
                        </tbody>

                    </table>

                    <a href="my_testdrives.php" class="btn btn-outline-dark w-100 mt-2">
                        View Full History
                    </a>

                <?php else: ?>
                    <p>No appointment scheduled yet.</p>
                <?php endif; ?>

            </div>

        </div>

    </main>

</div>

</body>
</html>