<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

$currentPage = 'recent_activity';; 
include '../admin_sidebar/sidebar.php';

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
        SELECT * FROM activities
        WHERE user LIKE ? OR action LIKE ? OR description LIKE ?
        ORDER BY created_at DESC LIMIT 50
    ");
    $stmt->bind_param("sss", $searchLike, $searchLike, $searchLike);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM activities ORDER BY created_at DESC LIMIT 50");
}

$activities = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
} else {
    $error = "Query failed: " . $conn->error;
}

$tableCheck  = $conn->query("SHOW TABLES LIKE 'activities'");
$tableExists = $tableCheck && $tableCheck->num_rows > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Recent Activity</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin_dashboard.css">

<style>
/* ── Mitsubishi theme — content area only ── */

.content {
    background: #080808;
    font-family: 'Inter', sans-serif;
    color: #ccc;
}

/* Page header */
.mit-page-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 6px;
}
.mit-diamond {
    width: 30px; height: 30px;
    background: #e8001c;
    clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
    flex-shrink: 0;
}
.mit-page-title {
    font-family: 'Rajdhani', sans-serif;
    font-size: 22px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #fff;
    margin: 0;
}
.mit-red-bar {
    height: 2px;
    background: #e8001c;
    margin: 12px 0 20px;
}

/* Alerts */
.mit-alert {
    padding: 10px 14px;
    font-size: 13px;
    font-family: 'Rajdhani', sans-serif;
    font-weight: 600;
    letter-spacing: .05em;
    text-transform: uppercase;
    margin-bottom: 14px;
    border-left: 3px solid;
}
.mit-alert.warning { background: #1a1000; border-color: #ffa000; color: #ffa000; }
.mit-alert.danger  { background: #1a0005; border-color: #e8001c; color: #e8001c; }

/* Search bar */
.mit-search-bar {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 18px;
}
.mit-search-bar input[type="text"] {
    flex: 1;
    min-width: 180px;
    max-width: 380px;
    height: 36px;
    padding: 0 12px;
    background: #111;
    border: 1px solid #222;
    color: #ccc;
    font-size: 12px;
    font-family: 'Inter', sans-serif;
    outline: none;
    border-radius: 0;
}
.mit-search-bar input::placeholder { color: #444; }
.mit-search-bar input:focus        { border-color: #e8001c; }

.btn-mit {
    height: 36px;
    padding: 0 16px;
    border: none;
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    clip-path: polygon(0 0, calc(100% - 7px) 0, 100% 7px, 100% 100%, 7px 100%, 0 calc(100% - 7px));
    text-decoration: none;
    white-space: nowrap;
    transition: background .12s;
}
.btn-mit.primary         { background: #e8001c; color: #fff; }
.btn-mit.primary:hover   { background: #ff1a33; color: #fff; }
.btn-mit.secondary       { background: transparent; border: 1px solid #333; color: #888; clip-path: none; }
.btn-mit.secondary:hover { border-color: #555; color: #ccc; text-decoration: none; }

/* Table wrapper */
.mit-table-wrap {
    border: 1px solid #1e1e1e;
    overflow-x: auto;
}

/* Table */
.activity-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    min-width: 640px;
}
.activity-table thead tr { background: #111; }
.activity-table thead th {
    padding: 10px 14px;
    font-family: 'Rajdhani', sans-serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: #555;
    border-bottom: 2px solid #e8001c;
    text-align: left;
    white-space: nowrap;
}
.activity-table thead th:nth-child(1) { width: 14%; }
.activity-table thead th:nth-child(2) { width: 18%; }
.activity-table thead th:nth-child(3) { width: auto; }
.activity-table thead th:nth-child(4) { width: 16%; }

.activity-table tbody tr {
    border-bottom: 1px solid #161616;
    transition: background .1s;
}
.activity-table tbody tr:last-child { border-bottom: none; }
.activity-table tbody tr:hover { background: #111; }
.activity-table td {
    padding: 10px 14px;
    vertical-align: middle;
    color: #aaa;
}

/* User chip */
.user-chip {
    display: inline-flex;
    align-items: center;
    gap: 7px;
}
.user-avatar {
    width: 24px; height: 24px;
    background: #1a0005;
    border: 1px solid #3a0010;
    color: #e8001c;
    font-size: 9px;
    font-weight: 700;
    font-family: 'Rajdhani', sans-serif;
    letter-spacing: .05em;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.user-name { color: #ccc; font-size: 12px; white-space: nowrap; }

/* Action badge */
.action-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 2px 9px;
    font-family: 'Rajdhani', sans-serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    white-space: nowrap;
}
.action-badge::before {
    content: '';
    width: 5px; height: 5px;
    background: currentColor;
    clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
    flex-shrink: 0;
}
.badge-added     { color: #00c853; border: 1px solid #003318; }
.badge-updated   { color: #40c4ff; border: 1px solid #003344; }
.badge-deleted   { color: #e8001c; border: 1px solid #3a0008; }
.badge-login     { color: #ffa000; border: 1px solid #3d2700; }
.badge-default   { color: #888;    border: 1px solid #222;    }

/* Description */
.td-desc { color: #666; font-size: 11px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 300px; }

/* Timestamp */
.td-time {
    color: #444;
    font-size: 11px;
    font-family: 'Rajdhani', sans-serif;
    letter-spacing: .04em;
    white-space: nowrap;
}

/* Empty state */
.mit-empty {
    text-align: center;
    padding: 2.5rem 1rem;
    color: #2a2a2a;
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
}
.mit-empty i { display: block; font-size: 28px; margin-bottom: .5rem; color: #1e1e1e; }
</style>
</head>
<body>

<!-- CONTENT — Mitsubishi theme -->
<div class="content">

    <div class="mit-page-header">
        <div class="fas fa-history"></div>
        <h2 class="mit-page-title">Recent Activity</h2>
    </div>
    <div class="mit-red-bar"></div>

    <?php if (!$tableExists): ?>
        <div class="mit-alert warning">Activities table does not exist.</div>
    <?php elseif (isset($error)): ?>
        <div class="mit-alert danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Search bar -->
    <form method="GET">
    <div class="mit-search-bar">
        <input
            type="text"
            name="search"
            placeholder="Search user, action, description…"
            value="<?= htmlspecialchars($search) ?>"
        >
        <button type="submit" class="btn-mit primary">
            <i class="fas fa-search"></i> Search
        </button>
        <?php if (!empty($search)): ?>
            <a href="recent_activity.php" class="btn-mit secondary">
                <i class="fas fa-times"></i> Reset
            </a>
        <?php endif; ?>
    </div>
    </form>

    <!-- Table -->
    <div class="mit-table-wrap">
    <table class="activity-table">
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
            <td colspan="4">
                <div class="mit-empty">
                    <i class="fas fa-history"></i>
                    No activities found
                </div>
            </td>
        </tr>
    <?php else: ?>
        <?php foreach ($activities as $activity):

            /* Avatar initials */
            $parts    = explode(' ', trim($activity['user']));
            $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
            if (strlen($initials) < 2) $initials = strtoupper(substr($activity['user'], 0, 2));

            /* Action badge class */
            $actionLower  = strtolower($activity['action']);
            $badgeClass   = 'badge-default';
            if (str_contains($actionLower, 'add') || str_contains($actionLower, 'creat') || str_contains($actionLower, 'insert')) {
                $badgeClass = 'badge-added';
            } elseif (str_contains($actionLower, 'updat') || str_contains($actionLower, 'edit')) {
                $badgeClass = 'badge-updated';
            } elseif (str_contains($actionLower, 'delet') || str_contains($actionLower, 'remov')) {
                $badgeClass = 'badge-deleted';
            } elseif (str_contains($actionLower, 'login') || str_contains($actionLower, 'logout') || str_contains($actionLower, 'auth')) {
                $badgeClass = 'badge-login';
            }

            /* Format timestamp */
            $ts = !empty($activity['created_at'])
                ? date('M d, Y · H:i', strtotime($activity['created_at']))
                : 'N/A';
        ?>
        <tr>
            <td>
                <div class="user-chip">
                    <div class="user-avatar"><?= htmlspecialchars($initials) ?></div>
                    <span class="user-name"><?= htmlspecialchars($activity['user']) ?></span>
                </div>
            </td>
            <td>
                <span class="action-badge <?= $badgeClass ?>">
                    <?= htmlspecialchars($activity['action']) ?>
                </span>
            </td>
            <td>
                <div class="td-desc" title="<?= htmlspecialchars($activity['description'] ?? '') ?>">
                    <?= htmlspecialchars($activity['description'] ?? '—') ?>
                </div>
            </td>
            <td class="td-time"><?= $ts ?></td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>

    </tbody>
    </table>
    </div><!-- /.mit-table-wrap -->

</div><!-- /.content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>y