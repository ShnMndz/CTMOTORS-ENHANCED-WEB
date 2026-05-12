<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

$currentPage = 'test_drives';

// ==========================
// FILTERS
// ==========================
$search       = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
$filterDate   = isset($_GET['date'])   ? trim($_GET['date'])   : '';

$where = "1=1";

if (!empty($search)) {
    $safeSearch = $conn->real_escape_string($search);
    $where .= " AND (
        td.fullname LIKE '%$safeSearch%'
        OR td.email LIKE '%$safeSearch%'
        OR v.model_name LIKE '%$safeSearch%'
        OR v.model_variant LIKE '%$safeSearch%'
    )";
}

if (!empty($statusFilter)) {
    $safeStatus = $conn->real_escape_string($statusFilter);
    $where .= " AND td.status = '$safeStatus'";
}

if (!empty($filterDate)) {
    $where .= " AND td.date = '$filterDate'";
}

// ==========================
// UPDATE STATUS + NOTIF
// ==========================
if (isset($_POST['update_status'])) {
    $id            = $_POST['id'];
    $status        = $_POST['status'];
    $admin_notes   = isset($_POST['admin_notes'])   ? trim($_POST['admin_notes'])   : null;
    $admin_message = isset($_POST['admin_message']) ? trim($_POST['admin_message']) : null;

    $stmt = $conn->prepare("SELECT user_id FROM test_drives WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if ($data) {
        $user_id = $data['user_id'];

        $stmt = $conn->prepare("
            UPDATE test_drives
            SET status=?, admin_notes=?, admin_message=?
            WHERE id=?
        ");
        $stmt->bind_param("sssi", $status, $admin_notes, $admin_message, $id);
        $stmt->execute();

        logActivity($conn, $_SESSION['user'], 'Updated Test Drive Status', "Updated test drive ID: $id to status: $status");

        $title = $message = "";

        if ($status == 'approved') {
            $title   = "Test Drive Approved";
            $message = "Your test drive request has been approved.";
        } elseif ($status == 'rejected') {
            $title   = "Test Drive Rejected";
            $message = "Your test drive request was rejected.";
        } elseif ($status == 'completed') {
            $title   = "Test Drive Completed";
            $message = "Your test drive has been completed.";
        }

        if (!empty($title)) {
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, title, message, type, reference_id)
                VALUES (?, ?, ?, 'test_drive', ?)
            ");
            $stmt->bind_param("issi", $user_id, $title, $message, $id);
            $stmt->execute();
        }
    }

    header("Location: admin_test_drives.php");
    exit();
}

// ==========================
// DATA FETCH
// ==========================
$result = $conn->query("
    SELECT td.*, v.model_name, v.model_variant
    FROM test_drives td
    JOIN vehicles v ON td.vehicle_id = v.id
    WHERE $where
    ORDER BY td.created_at DESC
");

$result2 = $conn->query("
    SELECT td.*, v.model_name, v.model_variant
    FROM test_drives td
    JOIN vehicles v ON td.vehicle_id = v.id
    WHERE $where
    ORDER BY td.created_at DESC
");

$pendingCount = $conn->query("
    SELECT COUNT(*) as total FROM test_drives WHERE status='pending'
")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Test Drive Requests</title>

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

/* Pending badge in header */
.mit-pending-badge {
    display: inline-flex;
    align-items: center;
    padding: 2px 10px;
    background: #1a0e00;
    border: 1px solid #3a2800;
    color: #ffa000;
    font-family: 'Rajdhani', sans-serif;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-left: 10px;
    vertical-align: middle;
}

/* Filter bar */
.mit-filter-bar {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 18px;
    align-items: center;
}
.mit-filter-bar input[type="text"],
.mit-filter-bar input[type="date"],
.mit-filter-bar select {
    background: #111;
    border: 1px solid #222;
    color: #ccc;
    height: 36px;
    padding: 0 12px;
    font-size: 12px;
    font-family: 'Inter', sans-serif;
    outline: none;
    border-radius: 0;
}
.mit-filter-bar input[type="text"]  { flex: 2; min-width: 160px; }
.mit-filter-bar input[type="date"]  { flex: 1; min-width: 130px; }
.mit-filter-bar select              { flex: 1; min-width: 130px; }
.mit-filter-bar input::placeholder  { color: #444; }
.mit-filter-bar input:focus,
.mit-filter-bar select:focus        { border-color: #e8001c; }
.mit-filter-bar select option       { background: #111; }

.btn-mit-icon {
    height: 36px;
    padding: 0 14px;
    background: #e8001c;
    color: #fff;
    border: none;
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .08em;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    clip-path: polygon(0 0, calc(100% - 6px) 0, 100% 6px, 100% 100%, 6px 100%, 0 calc(100% - 6px));
    text-decoration: none;
    white-space: nowrap;
}
.btn-mit-icon:hover { background: #ff1a33; color: #fff; text-decoration: none; }
.btn-mit-icon.secondary {
    background: transparent;
    border: 1px solid #333;
    color: #888;
}
.btn-mit-icon.secondary:hover { border-color: #555; color: #ccc; }

/* Table wrapper */
.mit-table-wrap {
    border: 1px solid #1e1e1e;
    overflow-x: auto;
}

/* Table */
.drives-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    min-width: 860px;
}
.drives-table thead tr { background: #111; }
.drives-table thead th {
    padding: 10px 12px;
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
.drives-table tbody tr {
    border-bottom: 1px solid #161616;
    transition: background .1s;
}
.drives-table tbody tr:last-child { border-bottom: none; }
.drives-table tbody tr:hover { background: #111; }
.drives-table td {
    padding: 10px 12px;
    vertical-align: middle;
    color: #aaa;
    white-space: nowrap;
}

/* Name / email cells */
.td-name  { font-weight: 500; color: #e0e0e0; }
.td-email { color: #555; font-size: 11px; }
.td-vehicle-main { color: #e0e0e0; font-weight: 500; }
.td-vehicle-sub  { color: #555; font-size: 11px; }
.td-date { font-family: 'Rajdhani', sans-serif; color: #666; letter-spacing: .04em; }

/* Status badges */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 2px 9px;
    font-family: 'Rajdhani', sans-serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    white-space: nowrap;
}
.status-badge::before {
    content: '';
    width: 5px; height: 5px;
    background: currentColor;
    clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
    flex-shrink: 0;
}
.status-pending   { color: #ffa000; border: 1px solid #3d2700; }
.status-approved  { color: #00c853; border: 1px solid #003318; }
.status-rejected  { color: #e8001c; border: 1px solid #3a0008; }
.status-completed { color: #40c4ff; border: 1px solid #003344; }

/* Action buttons */
.action-btn {
    width: 28px; height: 28px;
    background: transparent;
    border: 1px solid #222;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #555;
    font-size: 12px;
    text-decoration: none;
    transition: all .12s;
    clip-path: polygon(0 0, calc(100% - 5px) 0, 100% 5px, 100% 100%, 5px 100%, 0 calc(100% - 5px));
}
.action-btn:hover             { background: #1a1a1a; color: #e0e0e0; border-color: #444; }
.action-btn.approve:hover     { background: #001a0a; color: #00c853; border-color: #003318; }
.action-btn.reject:hover      { background: #1a0005; color: #e8001c; border-color: #3a0010; }
.action-btn.info:hover        { background: #001a2a; color: #40c4ff; border-color: #003344; }

.btn-mit-done {
    height: 26px;
    padding: 0 12px;
    background: transparent;
    border: 1px solid #003344;
    color: #40c4ff;
    font-family: 'Rajdhani', sans-serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    cursor: pointer;
    clip-path: polygon(0 0, calc(100% - 5px) 0, 100% 5px, 100% 100%, 5px 100%, 0 calc(100% - 5px));
    transition: all .12s;
}
.btn-mit-done:hover { background: #001a2a; color: #80d8ff; }

.no-action { color: #2a2a2a; font-size: 11px; font-family: 'Rajdhani', sans-serif; letter-spacing: .06em; }

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

/* ── Modals ── */
.mit-modal .modal-content {
    background: #111;
    border: 1px solid #1e1e1e;
    border-top: 2px solid #e8001c;
    border-radius: 0;
}
.mit-modal .modal-header {
    background: #111;
    border-bottom: 1px solid #1e1e1e;
    padding: 14px 18px;
}
.mit-modal .modal-title {
    font-family: 'Rajdhani', sans-serif;
    font-size: 16px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #fff;
}
.mit-modal .btn-close { filter: invert(1) brightness(.5); }
.mit-modal .modal-body {
    background: #111;
    padding: 18px;
}
.mit-modal .modal-footer {
    background: #111;
    border-top: 1px solid #1e1e1e;
    padding: 14px 18px;
}
.mit-modal label {
    font-family: 'Rajdhani', sans-serif;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #555;
    margin-bottom: 6px;
    display: block;
}
.mit-modal .form-control {
    background: #0a0a0a;
    border: 1px solid #222;
    color: #ccc;
    border-radius: 0;
    font-size: 13px;
    font-family: 'Inter', sans-serif;
}
.mit-modal .form-control:focus {
    background: #0a0a0a;
    border-color: #e8001c;
    color: #fff;
    box-shadow: none;
}

/* Message box */
.mit-message-box {
    background: #0a0a0a;
    border: 1px solid #1e1e1e;
    border-left: 3px solid #ffa000;
    padding: 14px 16px;
    font-size: 13px;
    line-height: 1.7;
    color: #ccc;
    white-space: pre-line;
    font-family: 'Inter', sans-serif;
}

.btn-mit-submit {
    height: 36px;
    padding: 0 20px;
    border: none;
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    cursor: pointer;
    clip-path: polygon(0 0, calc(100% - 7px) 0, 100% 7px, 100% 100%, 7px 100%, 0 calc(100% - 7px));
    transition: background .12s;
}
.btn-mit-submit.close-btn  { background: #1a1a1a; color: #888; }
.btn-mit-submit.close-btn:hover  { background: #222; color: #ccc; }
.btn-mit-submit.approve-btn { background: #006b2b; color: #fff; }
.btn-mit-submit.approve-btn:hover { background: #00a040; }
.btn-mit-submit.reject-btn  { background: #e8001c; color: #fff; }
.btn-mit-submit.reject-btn:hover  { background: #ff1a33; }
</style>
</head>

<body>

<!-- SIDEBAR — untouched, styled by admin_dashboard.css -->
<div class="sidebar">
    <h4>Admin Panel</h4>
    <a href="admin_dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
    <a href="admin_profile.php"><i class="fas fa-user"></i> Your Profile</a>
    <a href="admin_users.php"><i class="fas fa-users"></i> Manage Users</a>
    <a href="admin_vehicles.php"><i class="fas fa-car"></i> Manage Vehicles</a>
    <a href="admin_posts.php"><i class="fas fa-newspaper"></i> Posts</a>
    <a href="recent_activity.php"><i class="fas fa-history"></i> Recent Activity</a>
    <a href="admin_test_drives.php" class="active">
        <i class="fas fa-key"></i> Test Drive
        <?php if ($pendingCount > 0): ?>
            <span class="badge bg-warning text-dark"><?= $pendingCount ?></span>
        <?php endif; ?>
    </a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<!-- CONTENT — Mitsubishi theme -->
<div class="content">

    <div class="mit-page-header">
        <div class="mit-diamond"></div>
        <h2 class="mit-page-title">
            Test Drive Requests
            <?php if ($pendingCount > 0): ?>
                <span class="mit-pending-badge">&#9670; <?= $pendingCount ?> Pending</span>
            <?php endif; ?>
        </h2>
    </div>
    <div class="mit-red-bar"></div>

    <!-- Filters -->
    <form method="GET">
    <div class="mit-filter-bar">
        <input type="text" name="search"
               placeholder="Search name, email, vehicle…"
               value="<?= htmlspecialchars($search) ?>">

        <select name="status">
            <option value="">All Status</option>
            <option value="pending"   <?= $statusFilter=='pending'   ?'selected':'' ?>>Pending</option>
            <option value="approved"  <?= $statusFilter=='approved'  ?'selected':'' ?>>Approved</option>
            <option value="rejected"  <?= $statusFilter=='rejected'  ?'selected':'' ?>>Rejected</option>
            <option value="completed" <?= $statusFilter=='completed' ?'selected':'' ?>>Completed</option>
        </select>

        <input type="date" name="date" value="<?= htmlspecialchars($filterDate) ?>">

        <button type="submit" class="btn-mit-icon">
            <i class="fas fa-search"></i> Filter
        </button>

        <a href="admin_test_drives.php" class="btn-mit-icon secondary">
            <i class="fas fa-times"></i> Reset
        </a>
    </div>
    </form>

    <!-- Table -->
    <div class="mit-table-wrap">
    <table class="drives-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Contact</th>
            <th>Vehicle</th>
            <th>Date</th>
            <th>Time</th>
            <th>Message</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

    <?php
    $hasRows = false;
    while ($row = $result->fetch_assoc()):
        $hasRows = true;
        $statusClass = 'status-' . $row['status'];
    ?>
    <tr>
        <td class="td-name"><?= htmlspecialchars($row['fullname']) ?></td>
        <td class="td-email"><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['contact']) ?></td>

        <td>
            <div class="td-vehicle-main"><?= htmlspecialchars($row['model_name']) ?></div>
            <div class="td-vehicle-sub"><?= htmlspecialchars($row['model_variant']) ?></div>
        </td>

        <td class="td-date"><?= date('M d, Y', strtotime($row['date'])) ?></td>
        <td class="td-date"><?= $row['time'] ?></td>

        <!-- View message -->
        <td>
            <button class="action-btn info"
                    data-bs-toggle="modal"
                    data-bs-target="#msgModal<?= $row['id'] ?>"
                    title="View message">
                <i class="fas fa-eye"></i>
            </button>
        </td>

        <!-- Status -->
        <td>
            <span class="status-badge <?= $statusClass ?>">
                <?= ucfirst($row['status']) ?>
            </span>
        </td>

        <!-- Actions -->
        <td>
            <div class="d-flex gap-1 align-items-center">
            <?php if ($row['status'] == 'pending'): ?>

                <button class="action-btn approve"
                        data-bs-toggle="modal"
                        data-bs-target="#approveModal<?= $row['id'] ?>"
                        title="Approve">
                    <i class="fas fa-check"></i>
                </button>

                <button class="action-btn reject"
                        data-bs-toggle="modal"
                        data-bs-target="#rejectModal<?= $row['id'] ?>"
                        title="Reject">
                    <i class="fas fa-times"></i>
                </button>

            <?php elseif ($row['status'] == 'approved'): ?>

                <form method="POST" class="d-inline">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="btn-mit-done" name="update_status">
                        Done
                    </button>
                </form>

            <?php else: ?>
                <span class="no-action">— No action</span>
            <?php endif; ?>
            </div>
        </td>
    </tr>
    <?php endwhile; ?>

    <?php if (!$hasRows): ?>
    <tr>
        <td colspan="9">
            <div class="mit-empty">
                <i class="fas fa-car"></i>
                No test drive requests found
            </div>
        </td>
    </tr>
    <?php endif; ?>

    </tbody>
    </table>
    </div><!-- /.mit-table-wrap -->

</div><!-- /.content -->

<!-- ================= MODALS ================= -->
<?php while($row = $result2->fetch_assoc()): ?>

<!-- MESSAGE MODAL -->
<div class="modal fade mit-modal" id="msgModal<?= $row['id'] ?>" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Test Drive Message</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <div class="mit-message-box">
            <?= nl2br(htmlspecialchars($row['message'])) ?>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn-mit-submit close-btn" data-bs-dismiss="modal">Close</button>
    </div>
</div>
</div>
</div>

<!-- APPROVE MODAL -->
<div class="modal fade mit-modal" id="approveModal<?= $row['id'] ?>" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">
    <div class="modal-header">
        <h5 class="modal-title">Approve Request</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <input type="hidden" name="status" value="approved">
        <label>Admin Message</label>
        <textarea name="admin_message" class="form-control" rows="3" placeholder="Optional message to the user…"></textarea>
    </div>
    <div class="modal-footer">
        <button class="btn-mit-submit close-btn" type="button" data-bs-dismiss="modal">Cancel</button>
        <button class="btn-mit-submit approve-btn" name="update_status">Approve</button>
    </div>
</form>
</div>
</div>
</div>

<!-- REJECT MODAL -->
<div class="modal fade mit-modal" id="rejectModal<?= $row['id'] ?>" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">
    <div class="modal-header">
        <h5 class="modal-title">Reject Request</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <input type="hidden" name="id" value="<?= $row['id'] ?>">
        <input type="hidden" name="status" value="rejected">
        <label>Reason for Rejection</label>
        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Provide a reason…" required></textarea>
    </div>
    <div class="modal-footer">
        <button class="btn-mit-submit close-btn" type="button" data-bs-dismiss="modal">Cancel</button>
        <button class="btn-mit-submit reject-btn" name="update_status">Reject</button>
    </div>
</form>
</div>
</div>
</div>

<?php endwhile; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>