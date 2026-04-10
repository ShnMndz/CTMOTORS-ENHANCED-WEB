<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

$currentPage = 'dashboard';

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
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<!-- LINK EXTERNAL CSS -->
<link rel="stylesheet" href="dashboard.css">

</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h4>Admin Panel</h4>

    <a href="admin_dashboard.php" class="<?= $currentPage=='dashboard'?'active':'' ?>">
        <i class="fas fa-chart-line"></i> Dashboard
    </a>

    <a href="admin_users.php">
        <i class="fas fa-users"></i> Manage Users
    </a>

    <a href="admin_vehicles.php">
        <i class="fas fa-car"></i> Manage Vehicles
    </a>
    
    <a href="admin_posts.php" class="<?= $currentPage=='posts'?'active':'' ?>">
    <i class="fas fa-newspaper"></i> Posts (News/Articles)
</a>

    <a href="../logout.php">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<!-- CONTENT -->
<div class="content">

<h2>Welcome, <?= htmlspecialchars($_SESSION['user']) ?></h2>

<!-- USER STATS -->
<div class="row mb-4">

    <div class="col-md-4">
        <div class="card stat-card">
            <h2><?= $total_users ?></h2>
            <small>Total Users</small>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card">
            <h2><?= $user_counts['admin'] ?></h2>
            <small>Admins</small>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card">
            <h2><?= $user_counts['user'] ?></h2>
            <small>Users</small>
        </div>
    </div>

</div>

<hr>

<h5>Vehicle Breakdown</h5>

<div class="row">

<div class="col-md-3">
    <div class="card stat-card">
        <h2><?= $total_vehicles ?></h2>
        <small>Total Vehicles</small>
    </div>
</div>

<?php foreach($model_counts as $model=>$count): ?>
<div class="col-md-3">
    <div class="card stat-card">
        <h2><?= $count ?></h2>
        <small><?= htmlspecialchars($model) ?></small>
    </div>
</div>
<?php endforeach; ?>

</div>

</div>

</body>
</html>