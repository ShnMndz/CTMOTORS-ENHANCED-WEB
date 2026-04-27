<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

$currentPage = 'dashboard';

/* STATS */
$total_users = $conn->query("SELECT COUNT(*) as total FROM users")
->fetch_assoc()['total'];

$user_counts = ['admin'=>0,'user'=>0];
$res_roles = $conn->query("SELECT role, COUNT(*) as total FROM users GROUP BY role");
while($row = $res_roles->fetch_assoc()) {
    $user_counts[$row['role']] = $row['total'];
}

$model_counts = [];
$res_models = $conn->query("SELECT model_name, COUNT(*) as total FROM vehicles GROUP BY model_name");
while($row = $res_models->fetch_assoc()) {
    $model_counts[$row['model_name']] = $row['total'];
}

$total_vehicles = array_sum($model_counts);

$pending_test_drives = $conn->query("
    SELECT COUNT(*) as total 
    FROM test_drives 
    WHERE status = 'pending'
")->fetch_assoc()['total'];
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

<!-- SIDEBAR -->
<div class="sidebar">

    <h4>Admin Panel</h4>

    <a href="admin_dashboard.php" class="<?= $currentPage=='dashboard'?'active':'' ?>">
        <i class="fas fa-chart-line"></i> Dashboard
    </a>
    <a href="admin_profile.php"><i class="fas fa-user"></i>Your Profile</a>
    <a href="admin_users.php"><i class="fas fa-users"></i> Manage Users</a>
    <a href="admin_vehicles.php"><i class="fas fa-car"></i> Manage Vehicles</a>
    <a href="admin_posts.php"><i class="fas fa-newspaper"></i> Posts</a>

    <a href="admin_test_drives.php">
        <i class="fas fa-key"></i> Test Drives
        <?php if($pending_test_drives > 0): ?>
            <span class="badge bg-danger badge-notif"><?= $pending_test_drives ?></span>
        <?php endif; ?>
    </a>

    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>

</div>

<!-- CONTENT -->
<div class="content">

    <div id="greetingBox">
        <h3>Welcome, <?= htmlspecialchars($_SESSION['user']) ?></h3>
        <div id="liveDate"></div>
    </div>

    <!-- TEST DRIVES -->
    <div class="row mb-4">
        <div class="col-md-4">
            <a href="admin_test_drives.php" class="text-decoration-none">
                <div class="stat-card">
                    <h2><?= $pending_test_drives ?></h2>
                    <small>Pending Test Drives</small>

                    <?php if($pending_test_drives > 0): ?>
                        <span class="notif-dot">!</span>
                    <?php endif; ?>
                </div>
            </a>
        </div>
    </div>

    <!-- USERS -->
    <div class="row g-3">

        <div class="col-md-4">
            <div class="stat-card">
                <h2><?= $total_users ?></h2>
                <small>Total Users</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <h2><?= $user_counts['admin'] ?></h2>
                <small>Admins</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <h2><?= $user_counts['user'] ?></h2>
                <small>Users</small>
            </div>
        </div>

    </div>

    <h5>Vehicle Breakdown</h5>

    <div class="row g-3">

        <div class="col-md-3">
            <div class="stat-card">
                <h2><?= $total_vehicles ?></h2>
                <small>Total Vehicles</small>
            </div>
        </div>

        <?php foreach($model_counts as $model=>$count): ?>
        <div class="col-md-3">
            <div class="stat-card">
                <h2><?= $count ?></h2>
                <small><?= htmlspecialchars($model) ?></small>
            </div>
        </div>
        <?php endforeach; ?>

    </div>

</div>

<!-- 🔊 SOUND -->
<audio id="testDriveSound" src="/citimotorsweb/web/sounds/testdrivenotif.mp3" preload="auto"></audio>

<script>
function updateDateTime(){
    document.getElementById("liveDate").innerHTML =
        new Date().toDateString();
}
updateDateTime();
setInterval(updateDateTime, 60000);

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