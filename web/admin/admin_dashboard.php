<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

$currentPage = 'dashboard';
include '../admin_sidebar/sidebar.php';

/* STATS */
$total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];

$user_counts = ['admin' => 0, 'user' => 0];
$res_roles = $conn->query("SELECT role, COUNT(*) as total FROM users GROUP BY role");
while ($row = $res_roles->fetch_assoc()) {
    $user_counts[$row['role']] = $row['total'];
}

$model_counts = [];
$res_models = $conn->query("SELECT model_name, COUNT(*) as total FROM vehicles GROUP BY model_name");
while ($row = $res_models->fetch_assoc()) {
    $model_counts[$row['model_name']] = $row['total'];
}

$total_vehicles = array_sum($model_counts);

$pending_test_drives = $conn->query("SELECT COUNT(*) as total FROM test_drives WHERE status = 'pending'")->fetch_assoc()['total'];
$completed_test_drives = $conn->query("SELECT COUNT(*) as total FROM test_drives WHERE status = 'completed'")->fetch_assoc()['total'];
$rejected_test_drives = $conn->query("SELECT COUNT(*) as total FROM test_drives WHERE status = 'rejected'")->fetch_assoc()['total'];
$total_requests = $conn->query("SELECT COUNT(*) as total FROM test_drives")->fetch_assoc()['total'];

$weeklyCounts = array_fill(0, 5, 0);
$res_weeks = $conn->query(
    "SELECT FLOOR((DAY(created_at)-1)/7) as week_index, COUNT(*) as total 
     FROM test_drives 
     WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())
     GROUP BY week_index"
);
while ($row = $res_weeks->fetch_assoc()) {
    $idx = (int)$row['week_index'];
    if ($idx >= 0 && $idx < 5) {
        $weeklyCounts[$idx] = $row['total'];
    }
}

$recent_requests = $conn->query(
    "SELECT td.*, v.model_name, v.model_variant 
     FROM test_drives td 
     JOIN vehicles v ON td.vehicle_id = v.id 
     ORDER BY td.created_at DESC 
     LIMIT 4"
);

$recent_activities = $conn->query(
    "SELECT * FROM activities ORDER BY created_at DESC LIMIT 4"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<link rel="stylesheet" href="admin_dashboard.css">
</head>

<body>


<!-- CONTENT -->
<div class="content">

 <p class="hero-clock" id="liveDateTime"></p>
 
    <section class="hero-card">
        <div>
            <p class="eyebrow">Welcome back</p>
            <h1>Admin <?= htmlspecialchars($_SESSION['user']) ?></h1>
            <p class="hero-copy">Here&rsquo;s what&rsquo;s happening with your dealership today.</p>
        </div>
        <div class="hero-actions">
            <a href="admin_vehicles.php" class="btn btn-accent">Manage Vehicles</a>
            <a href="recent_activity.php" class="btn btn-outline">View Reports</a>
        </div>
    </section>

    <p class="hero-clock" id="liveDateTime"></p>

    <section class="stat-grid">
        <a href="admin_test_drives.php" class="stat-card link-card">
            <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
            <div>
                <span class="stat-label">Pending Test Drives</span>
                <h2><?= $pending_test_drives ?></h2>
            </div>
        </a>

        <div class="stat-card">
            <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
            <div>
                <span class="stat-label">Total Users</span>
                <h2><?= $total_users ?></h2>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon"><i class="fa-solid fa-user-shield"></i></div>
            <div>
                <span class="stat-label">Admins</span>
                <h2><?= $user_counts['admin'] ?></h2>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon"><i class="fa-solid fa-user"></i></div>
            <div>
                <span class="stat-label">Regular Users</span>
                <h2><?= $user_counts['user'] ?></h2>
            </div>
        </div>
    </section>

    <section class="dashboard-grid">
        <div class="panel panel-large">
            <div class="panel-header">
                <div>
                    <span class="panel-title">Vehicle Breakdown</span>
                    <p class="panel-subtitle">Total models and inventory distribution</p>
                </div>
                <a href="admin_vehicles.php" class="text-link">View All</a>
            </div>
            <div class="breakdown-card">
                <div class="breakdown-chart">
                    <span><?= $total_vehicles ?></span>
                    <small>Total Vehicles</small>
                </div>
                <ul class="breakdown-list">
                    <?php foreach ($model_counts as $model => $count):
                        $percent = round($count / max(1, $total_vehicles) * 100);
                    ?>
                        <li>
                            <div>
                                <span class="item-name"><?= htmlspecialchars($model) ?></span>
                                <small><?= $percent ?>%</small>
                            </div>
                            <strong><?= $count ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="panel panel-large">
            <div class="panel-header">
                <div>
                    <span class="panel-title">Test Drive Overview</span>
                    <p class="panel-subtitle">This month</p>
                </div>
                <div class="summary-pill">Total Requests: <?= $total_requests ?></div>
            </div>
            <div class="overview-body">
                <div class="status-pills">
                    <span class="pill pill-success">Completed <?= $completed_test_drives ?></span>
                    <span class="pill pill-warning">Pending <?= $pending_test_drives ?></span>
                    <span class="pill pill-danger">Rejected <?= $rejected_test_drives ?></span>
                </div>
                <div class="chart-line">
                    <?php foreach ($weeklyCounts as $index => $count):
                        $height = min(100, max(10, $count * 10 + 10));
                    ?>
                        <div class="chart-bar" style="height:<?= $height ?>%;">
                            <span><?= $count ?></span>
                            <small>Week <?= $index + 1 ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="recent-grid">
        <div class="panel">
            <div class="panel-header">
                <div>
                    <span class="panel-title">Recent Test Drive Requests</span>
                    <p class="panel-subtitle">Latest activity from your customers</p>
                </div>
                <a href="admin_test_drives.php" class="text-link">View All</a>
            </div>
            <div class="list-group">
                <?php if ($recent_requests->num_rows === 0): ?>
                    <div class="list-empty">No recent requests</div>
                <?php else: ?>
                    <?php while ($row = $recent_requests->fetch_assoc()): ?>
                        <div class="list-item">
                            <div>
                                <strong><?= htmlspecialchars($row['model_name']) ?></strong>
                                <small><?= htmlspecialchars($row['model_variant']) ?> · <?= date('M d, Y', strtotime($row['date'])) ?> <?= date('h:i A', strtotime($row['time'])) ?></small>
                            </div>
                            <span class="status-tag <?= htmlspecialchars($row['status']) ?>"><?= ucfirst($row['status']) ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <div>
                    <span class="panel-title">Recent Activity</span>
                    <p class="panel-subtitle">System actions and admin updates</p>
                </div>
                <a href="recent_activity.php" class="text-link">View All</a>
            </div>
            <div class="list-group">
                <?php if ($recent_activities->num_rows === 0): ?>
                    <div class="list-empty">No activity found</div>
                <?php else: ?>
                    <?php while ($row = $recent_activities->fetch_assoc()): ?>
                        <div class="list-item activity-item">
                            <div>
                                <strong><?= htmlspecialchars($row['action']) ?></strong>
                                <small><?= htmlspecialchars($row['description']) ?></small>
                            </div>
                            <span><?= date('M d, Y', strtotime($row['created_at'])) ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<!-- 🔊 SOUND -->
<audio id="testDriveSound" src="/citimotorsweb/web/sounds/testdrivenotif.mp3" preload="auto"></audio>

<script>
function updateDateTime() {
    const now = new Date();
    const date = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    const time = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    document.getElementById("liveDateTime").innerHTML = `${date} &nbsp;·&nbsp; ${time}`;
}
updateDateTime();
setInterval(updateDateTime, 1000); // update every second for the clock

/* =========================
   SOUND UNLOCK (IMPORTANT)
========================= */
document.addEventListener("click", function unlockSound() {
    const sound = document.getElementById("testDriveSound");

    if (sound) {
        sound.play().then(() => {
            sound.pause();
            sound.currentTime = 0;
        }).catch(() => {});
    }

    document.removeEventListener("click", unlockSound);
});

/* =========================
   REAL-TIME NOTIFICATION
========================= */

let previousTestDriveCount = 0;

function updateTestDriveNotif(){
    fetch('admin_test_drive_count.php')
        .then(res => res.json())
        .then(data => {

            const count = data.count;

            // 🔥 DETECT NEW REQUEST BEFORE UI UPDATE
            const isNew = count > previousTestDriveCount;

            if (isNew) {
                const sound = document.getElementById("testDriveSound");

                if (sound) {
                    sound.currentTime = 0;

                    // 🔊 force immediate play
                    let playPromise = sound.play();

                    if (playPromise !== undefined) {
                        playPromise.catch(err => {
                            console.log("Sound blocked:", err);
                        });
                    }
                }
            }

            // update AFTER sound check
            previousTestDriveCount = count;

            // badge update
            const badge = document.querySelector('.badge-notif');

            if(count > 0){
                if(badge){
                    badge.innerText = count;
                    badge.style.display = "inline-block";
                }
            } else {
                if(badge){
                    badge.style.display = "none";
                }
            }

            // dashboard number
            const cards = document.querySelectorAll('.stat-card h2');
            cards.forEach(el => {
                if(el.closest('.stat-card').innerText.includes("Pending Test Drives")){
                    el.innerText = count;
                }
            });

            // notif dot
            const dot = document.querySelector('.notif-dot');
            const card = document.querySelector('.stat-card');

            if(count > 0){
                if(!dot){
                    const span = document.createElement('span');
                    span.className = 'notif-dot';
                    span.innerText = '!';
                    card.appendChild(span);
                }
            } else {
                if(dot){
                    dot.remove();
                }
            }
        });
}

// run loop
updateTestDriveNotif();
setInterval(updateTestDriveNotif, 5000);
</script>

</body>
</html>