<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

$currentPage = 'dashboard'; // mark this page as active

// FETCH STATS
$total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];

$user_counts = ['admin'=>0,'user'=>0];
$res_roles = $conn->query("SELECT role, COUNT(*) as total FROM users GROUP BY role");
while($row = $res_roles->fetch_assoc()) { $user_counts[$row['role']] = $row['total']; }

// VEHICLE MODEL COUNTS
$model_counts = [];
$res_models = $conn->query("SELECT model_name, COUNT(*) as total FROM vehicles GROUP BY model_name");
while($row = $res_models->fetch_assoc()) { $model_counts[$row['model_name']] = $row['total']; }

// TOTAL VEHICLES
$total_vehicles = array_sum($model_counts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {background:#f8f9fa;}
.sidebar {width:230px;position:fixed;height:100%;background:#dc3545;color:#fff;padding:20px;}
.sidebar a{color:#fff;display:block;margin:10px 0;text-decoration:none;padding:5px;}
.sidebar a.active{background:#b52a34;border-radius:5px;}
.content {margin-left:250px;padding:20px;}
.card {border-left:5px solid #dc3545;}
</style>
</head>
<body>

<div class="sidebar">
<h4>Admin Panel</h4>
<a href="admin_dashboard.php" class="<?= $currentPage=='dashboard'?'active':'' ?>">Dashboard</a>
<a href="admin_users.php" class="<?= $currentPage=='users'?'active':'' ?>">Manage Users</a>
<a href="admin_vehicles.php" class="<?= $currentPage=='vehicles'?'active':'' ?>">Manage Vehicles</a>
<a href="../logout.php">Logout</a>
</div>

<div class="content">
<h2>Welcome, <?= htmlspecialchars($_SESSION['user']) ?></h2>

<div class="row mb-4">
<div class="col-md-3"><div class="card p-3"><h6>Total Users</h6><h3><?= $total_users ?></h3></div></div>
<div class="col-md-3"><div class="card p-3"><h6>Admins</h6><h3><?= $user_counts['admin'] ?></h3></div></div>
<div class="col-md-3"><div class="card p-3"><h6>Users</h6><h3><?= $user_counts['user'] ?></h3></div></div>
</div>

<hr>
<h5>Vehicle Breakdown</h5>
<div class="row">
<!-- Total Vehicles card first -->
<div class="col-md-3">
    <div class="card p-3 mb-2">
        <h6>Total Vehicles</h6>
        <h4><?= $total_vehicles ?></h4>
    </div>
</div>

<!-- Individual model counts -->
<?php foreach($model_counts as $model=>$count): ?>
<div class="col-md-3">
    <div class="card p-3 mb-2">
        <h6><?= htmlspecialchars($model) ?></h6>
        <h4><?= $count ?></h4>
    </div>
</div>
<?php endforeach; ?>
</div>

</div>
</body>
</html>