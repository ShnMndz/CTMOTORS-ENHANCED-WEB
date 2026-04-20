<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Dashboard</title>

<link rel="stylesheet" href="user_dashboard.css">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
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

            <a href="testdrive.php" class="menu-btn">
                <i class="fa-solid fa-calendar-check"></i>
                Test Drive Request
            </a>

            <a href="saved.php" class="menu-btn">
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

            <a href="../home.php" class="btn-home">
                Return to Homepage
            </a>

        </div>

        <!-- ALERT -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['type'] ?> alert-dismissible fade show">
                <?= $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['type']); ?>
        <?php endif; ?>

        <!-- GRID -->
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

            <div class="card full">
                <h4>Test Drive Appointment</h4>
                <p>No appointment scheduled yet.</p>
            </div>

        </div>

    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
setTimeout(() => {
    let alert = document.querySelector('.alert');
    if(alert){
        alert.classList.remove('show');
    }
}, 3000);
</script>

</body>
</html>