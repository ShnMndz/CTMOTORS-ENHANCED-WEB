<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

$currentPage = 'recent_activity';

$pending_test_drives = $conn->query("
    SELECT COUNT(*) as total 
    FROM test_drives 
    WHERE status = 'pending'
")->fetch_assoc()['total'];

/* =========================
   SEARCH FILTER
========================= */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($search)) {

    $searchLike = "%" . $search . "%";

    $stmt = $conn->prepare("
        SELECT * 
        FROM activities
        WHERE user LIKE ?
        OR action LIKE ?
        OR description LIKE ?
        ORDER BY created_at DESC
        LIMIT 50
    ");

    $stmt->bind_param(
        "sss",
        $searchLike,
        $searchLike,
        $searchLike
    );

    $stmt->execute();
    $result = $stmt->get_result();

} else {

    $query = "
        SELECT * 
        FROM activities 
        ORDER BY created_at DESC 
        LIMIT 50
    ";

    $result = $conn->query($query);
}

/* =========================
   FETCH ACTIVITIES
========================= */
$activities = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
} else {
    $error = "Query failed: " . $conn->error;
}

/* =========================
   CHECK TABLE
========================= */
$tableCheck = $conn->query("SHOW TABLES LIKE 'activities'");
$tableExists = $tableCheck && $tableCheck->num_rows > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recent Activity</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="admin_dashboard.css">

    <style>

        .search-box{
            max-width:400px;
        }

        .table{
            background:#fff;
            border-radius:12px;
            overflow:hidden;
        }

        .table thead{
            background:#dc3545;
            color:#fff;
        }

        .table th{
            border:none;
        }

        .table td{
            vertical-align:middle;
        }

    </style>

</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <h4>Admin Panel</h4>

    <a href="admin_dashboard.php" class="<?= $currentPage=='dashboard'?'active':'' ?>">
        <i class="fas fa-chart-line"></i> Dashboard
    </a>

    <a href="admin_profile.php">
        <i class="fas fa-user"></i> Your Profile
    </a>

    <a href="admin_users.php">
        <i class="fas fa-users"></i> Manage Users
    </a>

    <a href="admin_vehicles.php">
        <i class="fas fa-car"></i> Manage Vehicles
    </a>

    <a href="admin_posts.php">
        <i class="fas fa-newspaper"></i> Posts
    </a>

    <a href="recent_activity.php" class="<?= $currentPage=='recent_activity'?'active':'' ?>">
        <i class="fas fa-history"></i> Recent Activity
    </a>

    <a href="admin_test_drives.php">
        <i class="fas fa-key"></i> Test Drives

        <?php if($pending_test_drives > 0): ?>
            <span class="badge bg-danger badge-notif">
                <?= $pending_test_drives ?>
            </span>
        <?php endif; ?>
    </a>

    <a href="../logout.php">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>

</div>

<!-- CONTENT -->
<div class="content">

    <div id="greetingBox">
        <h3>Recent Activity</h3>
    </div>

    <div class="container mt-4">

        <?php if (!$tableExists): ?>

            <div class="alert alert-warning">
                Activities table does not exist.
            </div>

        <?php elseif (isset($error)): ?>

            <div class="alert alert-danger">
                <?= htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

        <!-- HEADER + SEARCH -->
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">

            <h2 class="mb-3 mb-md-0">
                Recent Admin Activities
            </h2>

            <form method="GET" class="search-box d-flex">

                <input 
                    type="text"
                    name="search"
                    class="form-control me-2"
                    placeholder="Search activity..."
                    value="<?= htmlspecialchars($search) ?>"
                >

                <button class="btn btn-danger">
                    <i class="fas fa-search"></i>
                </button>

                <?php if(!empty($search)): ?>
                    <a href="recent_activity.php" class="btn btn-secondary ms-2">
                        Reset
                    </a>
                <?php endif; ?>

            </form>

        </div>

        <!-- TABLE -->
        <div class="table-responsive">

            <table class="table table-striped align-middle">

                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (empty($activities)): ?>

                        <tr>
                            <td colspan="4" class="text-center py-4">
                                No activities found.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($activities as $activity): ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars($activity['user']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($activity['action']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($activity['description'] ?? ''); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($activity['created_at']); ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>