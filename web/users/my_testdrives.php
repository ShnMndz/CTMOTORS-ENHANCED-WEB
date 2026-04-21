<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$id = $_SESSION['user_id'];

/* USER DATA (MISSING BEFORE) */
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

/* STATUS COLORS */
.status-pending { color: orange; font-weight: bold; }
.status-approved { color: green; font-weight: bold; }
.status-rejected { color: red; font-weight: bold; }
.status-completed { color: #00c3ff; font-weight: bold; }

table { color:#fff; }

/* TABLE STATUS COLORS ONLY */
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
            <a href="user_dashboard.php" class="menu-btn">
                <i class="fa-solid fa-user"></i>
                Profile Status
            </a>

            <a href="my_testdrives.php" class="menu-btn active">
                <i class="fa-solid fa-calendar-check"></i>
                Test Drive Request
            </a>

            <a href="saved_vehicles.php" class="menu-btn">
                <i class="fa-solid fa-heart"></i>
                Saved Vehicles
            </a>
        </nav>

    </aside>

    <!-- MAIN PANEL (FIXED MISSING STRUCTURE) -->
    <main class="panel">

        <div class="box">

            <h3>Test Drives History</h3>

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

            <table class="table table-dark table-hover mt-3 text-center">
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Message</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>

                        <td>
                            <?= htmlspecialchars($row['model_name']) ?>
                            <br>
                            <small>(<?= htmlspecialchars($row['model_variant']) ?>)</small>
                        </td>

                        <td><?= $row['date'] ?></td>
                        <td><?= $row['time'] ?></td>

                        <td>
                            <?= htmlspecialchars(mb_strimwidth($row['message'], 0, 30, "...")) ?>
                        </td>

                        <td class="status-<?= $row['status'] ?>">
                            <?= ucfirst($row['status']) ?>
                        </td>

                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <?php else: ?>
                <p>No bookings yet.</p>
            <?php endif; ?>

            <a href="user_dashboard.php" class="btn btn-secondary w-100 mt-3">Back</a>

        </div>

    </main>

</div>

</body>
</html>