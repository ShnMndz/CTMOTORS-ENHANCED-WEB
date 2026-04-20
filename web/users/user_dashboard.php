<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$id = $_SESSION['user_id'];

/* FETCH USER */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    margin:0;
    font-family:Segoe UI;
    background:#0f0f0f;
    color:#fff;
}

/* MAIN LAYOUT */
.dashboard{
    display:flex;
    height:100vh;
}

/* SIDEBAR */
.sidebar{
    width:260px;
    background:#000;
    padding:20px;
    border-right:1px solid #222;
}

.profile-box{
    text-align:center;
    padding-bottom:20px;
    border-bottom:1px solid #222;
}

.avatar{
    width:80px;
    height:80px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid #ff4d4d;
    margin-bottom:10px;
}

.username{
    font-weight:bold;
}

.small{
    font-size:12px;
    color:#aaa;
}

.btn-edit{
    width:100%;
    margin-top:15px;
    padding:10px;
    background:#ff4d4d;
    border:none;
    color:#fff;
    border-radius:6px;
    cursor:pointer;
}

.btn-edit:hover{
    background:#e60023;
}

.menu{
    margin-top:20px;
}

.menu p{
    padding:12px;
    margin:8px 0;
    background:#1a1a1a;
    border-radius:6px;
    cursor:pointer;
}

.menu p:hover{
    background:#2a2a2a;
}

/* PANELS */
.panel{
    flex:1;
    padding:20px;
    overflow:auto;
}

.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}

.card{
    background:#1e1e1e;
    padding:20px;
    border-radius:10px;
    min-height:150px;
}

h4{
    margin-bottom:15px;
}

/* RIGHT PANEL SPECIAL */
.full{
    grid-column:span 2;
}
</style>
</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->
    <div class="sidebar">

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

        <div class="menu">
            <p>🚗 Test Drive Request</p>
            <p>❤️ Saved Vehicles</p>
        </div>

    </div>

    <!-- MAIN CONTENT -->
    <div class="panel">

        <!-- ✅ NOTIFICATION HERE -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['type'] ?> alert-dismissible fade show" role="alert">
                <?= $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

            <?php 
            unset($_SESSION['message']);
            unset($_SESSION['type']);
            ?>
        <?php endif; ?>

        <h3>Dashboard</h3>
        <p class="text-muted">Welcome back 👋</p>

        <div class="grid">

            <!-- SAVED VEHICLES -->
            <div class="card">
                <h4>Saved Vehicles</h4>
                <p>No saved vehicles yet.</p>
            </div>

            <!-- QUICK INFO -->
            <div class="card">
                <h4>Account Info</h4>
                <p><b>Name:</b> <?= htmlspecialchars($user['fullname']) ?></p>
                <p><b>Email:</b> <?= htmlspecialchars($user['email']) ?></p>
            </div>

            <!-- TEST DRIVE -->
            <div class="card full">
                <h4>Test Drive Appointment</h4>
                <p>No appointment scheduled yet.</p>
            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- AUTO HIDE ALERT -->
<script>
setTimeout(() => {
    let alert = document.querySelector('.alert');
    if(alert){
        alert.classList.remove('show');
        alert.classList.add('fade');
    }
}, 3000);
</script>

</body>
</html>