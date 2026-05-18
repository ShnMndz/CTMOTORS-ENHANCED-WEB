<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

$currentPage = 'users';
include '../admin_sidebar/sidebar.php';

/* ---------------------
   ADD USER
--------------------- */
if(isset($_POST['add_user'])){
    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role'];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM users WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();

    if($check->get_result()->num_rows > 0){
        header("Location: admin_users.php?error=email_exists");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $fullname, $email, $hashedPassword, $role);
    $stmt->execute();

    logActivity($conn, $_SESSION['user'], 'Added User', "Added user: $fullname ($email) with role $role");

    header("Location: admin_users.php?success=added");
    exit();
}

/* ---------------------
   UPDATE USER
--------------------- */
if(isset($_POST['save_user'])){
    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    $role     = $_POST['role'];
    $id       = $_POST['id'];

    $check = $conn->prepare("SELECT id FROM users WHERE email=? AND id<>?");
    $check->bind_param("si", $email, $id);
    $check->execute();

    if($check->get_result()->num_rows > 0){
        header("Location: admin_users.php?error=email_exists");
        exit();
    }

    $oldStmt = $conn->prepare("SELECT fullname, email, role FROM users WHERE id=?");
    $oldStmt->bind_param("i", $id);
    $oldStmt->execute();
    $oldData = $oldStmt->get_result()->fetch_assoc();

    $stmt = $conn->prepare("UPDATE users SET fullname=?, email=?, role=? WHERE id=?");
    $stmt->bind_param("sssi", $fullname, $email, $role, $id);
    $stmt->execute();

    $changes = [];
    if ($oldData['fullname'] !== $fullname) $changes[] = "name from {$oldData['fullname']} to $fullname";
    if ($oldData['email']    !== $email)    $changes[] = "email from {$oldData['email']} to $email";
    if ($oldData['role']     !== $role)     $changes[] = "role from {$oldData['role']} to $role";

    $description = !empty($changes)
        ? "Updated user: {$fullname} ({$email}) - " . implode(', ', $changes)
        : "Updated user: {$fullname} ({$email})";

    logActivity($conn, $_SESSION['user'], 'Updated User', $description);

    header("Location: admin_users.php?success=updated");
    exit();
}

/* ---------------------
   DELETE USER
--------------------- */
if(isset($_GET['delete_user'])){
    if($_GET['delete_user'] == $_SESSION['user_id']){
        header("Location: admin_users.php?error=selfdelete");
        exit();
    }

    $deleteId = intval($_GET['delete_user']);
    $infoStmt = $conn->prepare("SELECT fullname, email, role FROM users WHERE id=?");
    $infoStmt->bind_param("i", $deleteId);
    $infoStmt->execute();
    $userInfo = $infoStmt->get_result()->fetch_assoc();

    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();

    if ($userInfo) {
        logActivity($conn, $_SESSION['user'], 'Deleted User', "Deleted user: {$userInfo['fullname']} ({$userInfo['email']}) role {$userInfo['role']}");
    }

    header("Location: admin_users.php?success=deleted");
    exit();
}

/* ---------------------
   FETCH DATA
   Online = last_active within 5 minutes
--------------------- */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchQuery = '';

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $searchQuery = " WHERE fullname LIKE '%$search%' OR email LIKE '%$search%'";
}

$result_users = $conn->query("SELECT * FROM users $searchQuery ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Users</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">

<link rel="stylesheet" href="users.css">
<link rel="stylesheet" href="admin_dashboard.css">

<style>
/* ── Mitsubishi theme — content area only ── */
.content {
    background: #080808;
    font-family: 'Inter', sans-serif;
    color: #ccc;
}

.mit-page-header { display:flex; align-items:center; gap:14px; margin-bottom:6px; }
.mit-diamond { width:30px; height:30px; background:#e8001c; clip-path:polygon(50% 0%,100% 50%,50% 100%,0% 50%); flex-shrink:0; }
.mit-page-title { font-family:'Rajdhani',sans-serif; font-size:22px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#fff; margin:0; }
.mit-red-bar { height:2px; background:#e8001c; margin:12px 0 20px; }

.mit-alert { padding:10px 14px; font-size:13px; font-family:'Rajdhani',sans-serif; font-weight:600; letter-spacing:.05em; text-transform:uppercase; margin-bottom:14px; border-left:3px solid; transition:opacity .5s ease; }
.mit-alert.success { background:#0a1f0a; border-color:#00c853; color:#00c853; }
.mit-alert.danger  { background:#1a0005; border-color:#e8001c; color:#e8001c; }
.mit-alert.warning { background:#1a0e00; border-color:#ffa000; color:#ffa000; }

.btn-mit { height:36px; padding:0 18px; background:#e8001c; color:#fff; border:none; font-family:'Rajdhani',sans-serif; font-size:13px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; cursor:pointer; display:inline-flex; align-items:center; gap:7px; clip-path:polygon(0 0,calc(100% - 8px) 0,100% 8px,100% 100%,8px 100%,0 calc(100% - 8px)); text-decoration:none; margin-bottom:16px; }
.btn-mit:hover { background:#ff1a33; color:#fff; text-decoration:none; }

.mit-search-bar { display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; align-items:center; }
.mit-search-bar input[type="text"] { flex:1; min-width:160px; height:36px; padding:0 12px; background:#111; border:1px solid #222; color:#ccc; font-size:12px; font-family:'Inter',sans-serif; outline:none; border-radius:0; }
.mit-search-bar input::placeholder { color:#444; }
.mit-search-bar input:focus { border-color:#e8001c; }

.btn-mit-outline { height:36px; padding:0 14px; background:transparent; border:1px solid #333; color:#888; font-family:'Rajdhani',sans-serif; font-size:12px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; cursor:pointer; display:inline-flex; align-items:center; gap:6px; text-decoration:none; transition:all .12s; }
.btn-mit-outline:hover { border-color:#555; color:#ccc; text-decoration:none; }
.btn-mit-outline.search { border-color:#e8001c; color:#e8001c; }
.btn-mit-outline.search:hover { background:#1a0005; color:#ff4444; }

.mit-table-wrap { border:1px solid #1e1e1e; overflow:hidden; }

.users-table { width:100%; border-collapse:collapse; font-size:12px; table-layout:fixed; }
.users-table thead tr { background:#111; }
.users-table thead th { padding:10px 12px; font-family:'Rajdhani',sans-serif; font-size:11px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:#555; border-bottom:2px solid #e8001c; text-align:left; white-space:nowrap; overflow:hidden; }

.users-table thead th:nth-child(1) { width:50px;  }
.users-table thead th:nth-child(2) { width:60px;  }
.users-table thead th:nth-child(3) { width:18%;   }
.users-table thead th:nth-child(4) { width:22%;   }
.users-table thead th:nth-child(5) { width:10%;   }
.users-table thead th:nth-child(6) { width:11%;   }
.users-table thead th:nth-child(7) { width:13%;   }
.users-table thead th:nth-child(8) { width:10%;   }

.users-table tbody tr { border-bottom:1px solid #161616; transition:background .1s; }
.users-table tbody tr:last-child { border-bottom:none; }
.users-table tbody tr:hover { background:#111; }
.users-table td { padding:10px 12px; vertical-align:middle; color:#aaa; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

.id-cell { font-family:'Rajdhani',sans-serif; color:#444; font-size:12px; letter-spacing:.05em; }

.mit-avatar-wrap { width:38px; height:38px; border-radius:50%; overflow:hidden; background:#1a0005; border:1px solid #3a0010; display:flex; align-items:center; justify-content:center; }
.mit-avatar-wrap img { width:100%; height:100%; object-fit:cover; }
.mit-avatar-wrap .fa-user-circle { font-size:20px; color:#e8001c; }

.user-name  { font-weight:500; color:#e0e0e0; }
.user-email { color:#555; font-size:11px; }

.role-badge { display:inline-flex; align-items:center; gap:5px; padding:2px 9px; font-family:'Rajdhani',sans-serif; font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; white-space:nowrap; }
.role-badge::before { content:''; width:5px; height:5px; background:currentColor; clip-path:polygon(50% 0%,100% 50%,50% 100%,0% 50%); flex-shrink:0; }
.role-admin { color:#e8001c; border:1px solid #3a0008; }
.role-user  { color:#40c4ff; border:1px solid #003344; }

/* ── Online / Offline status ── */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 9px;
    font-family: 'Rajdhani', sans-serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    white-space: nowrap;
}
.status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}
.status-online  { color: #00c853; border: 1px solid #003318; }
.status-offline { color: #333;    border: 1px solid #1e1e1e; }
.status-online  .status-dot { background: #00c853; box-shadow: 0 0 5px #00c85366; animation: pulse 2s infinite; }
.status-offline .status-dot { background: #333; }

@keyframes pulse {
    0%   { box-shadow: 0 0 0 0 #00c85366; }
    70%  { box-shadow: 0 0 0 5px transparent; }
    100% { box-shadow: 0 0 0 0 transparent; }
}

.date-cell { color:#444; font-size:11px; font-family:'Rajdhani',sans-serif; letter-spacing:.04em; }

.action-btn { width:28px; height:28px; background:transparent; border:1px solid #222; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; color:#555; font-size:12px; text-decoration:none; transition:all .12s; clip-path:polygon(0 0,calc(100% - 5px) 0,100% 5px,100% 100%,5px 100%,0 calc(100% - 5px)); }
.action-btn:hover { background:#1a1a1a; color:#e0e0e0; border-color:#444; }
.action-btn.danger:hover  { background:#1a0005; color:#e8001c; border-color:#3a0010; }
.action-btn.warning:hover { background:#1a1000; color:#ffa000; border-color:#3a2800; }

.mit-empty { text-align:center; padding:2.5rem 1rem; color:#2a2a2a; font-family:'Rajdhani',sans-serif; font-size:13px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; }
.mit-empty i { display:block; font-size:28px; margin-bottom:.5rem; color:#1e1e1e; }

/* Modals */
.mit-modal .modal-content { background:#111; border:1px solid #1e1e1e; border-top:2px solid #e8001c; border-radius:0; }
.mit-modal .modal-header { background:#111; border-bottom:1px solid #1e1e1e; padding:14px 18px; }
.mit-modal .modal-title { font-family:'Rajdhani',sans-serif; font-size:16px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#fff; }
.mit-modal .btn-close { filter:invert(1) brightness(.5); }
.mit-modal .modal-body { background:#111; padding:18px; }
.mit-modal .modal-footer { background:#111; border-top:1px solid #1e1e1e; padding:14px 18px; }
.mit-modal .form-control, .mit-modal .form-select, .mit-modal .form-floating .form-control { background:#0a0a0a; border:1px solid #222; color:#ccc; border-radius:0; font-size:13px; font-family:'Inter',sans-serif; }
.mit-modal .form-control:focus, .mit-modal .form-select:focus, .mit-modal .form-floating .form-control:focus { background:#0a0a0a; border-color:#e8001c; color:#fff; box-shadow:none; }
.mit-modal .form-control::placeholder { color:#444; }
.mit-modal .form-floating label { color:#555; font-size:13px; }
.mit-modal .form-select option { background:#111; }
.mit-modal .input-group .btn-outline-secondary { background:#0a0a0a; border:1px solid #222; color:#555; border-radius:0; }
.mit-modal .input-group .btn-outline-secondary:hover { background:#1a1a1a; color:#ccc; border-color:#444; }

.btn-mit-submit { width:100%; height:38px; background:#e8001c; color:#fff; border:none; font-family:'Rajdhani',sans-serif; font-size:14px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; cursor:pointer; clip-path:polygon(0 0,calc(100% - 8px) 0,100% 8px,100% 100%,8px 100%,0 calc(100% - 8px)); transition:background .12s; }
.btn-mit-submit:hover { background:#ff1a33; }
.btn-mit-submit.add { background:#006b2b; }
.btn-mit-submit.add:hover { background:#00a040; }
</style>
</head>
<body>


<!-- CONTENT -->
<div class="content">

    <div class="mit-page-header">
        <div class="mit-diamond"></div>
        <h2 class="mit-page-title">Manage Users</h2>
    </div>
    <div class="mit-red-bar"></div>

    <?php if(isset($_GET['success']) && $_GET['success']=='added'): ?>
        <div class="mit-alert success auto-fade">User added successfully!</div>
    <?php endif; ?>
    <?php if(isset($_GET['success']) && $_GET['success']=='updated'): ?>
        <div class="mit-alert success auto-fade">User updated successfully!</div>
    <?php endif; ?>
    <?php if(isset($_GET['success']) && $_GET['success']=='deleted'): ?>
        <div class="mit-alert success auto-fade">User deleted successfully!</div>
    <?php endif; ?>
    <?php if(isset($_GET['error']) && $_GET['error']=='email_exists'): ?>
        <div class="mit-alert danger auto-fade">Email already exists!</div>
    <?php endif; ?>
    <?php if(isset($_GET['error']) && $_GET['error']=='selfdelete'): ?>
        <div class="mit-alert warning auto-fade">You cannot delete your own account!</div>
    <?php endif; ?>

    <button class="btn-mit" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fas fa-plus"></i> Add New User
    </button>

    <form method="GET">
    <div class="mit-search-bar">
        <input type="text" name="search" placeholder="Search by name or email…" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn-mit-outline search">
            <i class="fas fa-search"></i> Search
        </button>
        <?php if (!empty($search)): ?>
        <a href="admin_users.php" class="btn-mit-outline">
            <i class="fas fa-times"></i> Clear
        </a>
        <?php endif; ?>
    </div>
    </form>

    <!-- Table -->
    <div class="mit-table-wrap">
    <table class="users-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Avatar</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

    <?php
    $hasRows = false;
    while($u = $result_users->fetch_assoc()):
        $hasRows = true;

        /* ── Online check: last_active within 5 minutes ── */
        $isOnline = false;
        if (!empty($u['last_active'])) {
            $lastActive = strtotime($u['last_active']);
            $isOnline   = (time() - $lastActive) <= 300; // 5 minutes
        }
    ?>
    <tr data-id="<?= $u['id'] ?>"
        data-name="<?= htmlspecialchars($u['fullname']) ?>"
        data-email="<?= htmlspecialchars($u['email']) ?>"
        data-role="<?= $u['role'] ?>">

        <td class="id-cell">#<?= $u['id'] ?></td>

        <!-- Avatar with online indicator -->
        <td>
            <div style="position:relative; display:inline-block;">
                <div class="mit-avatar-wrap">
                    <?php if(!empty($u['profile_pic'])): ?>
                        <img src="../uploads/<?= $u['profile_pic'] ?>" alt="">
                    <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                    <?php endif; ?>
                </div>
                <!-- Small dot on avatar corner -->
                <span style="
                    position:absolute; bottom:1px; right:1px;
                    width:9px; height:9px; border-radius:50%;
                    background:<?= $isOnline ? '#00c853' : '#333' ?>;
                    border:1.5px solid #080808;
                    <?= $isOnline ? 'box-shadow:0 0 4px #00c85399;' : '' ?>
                "></span>
            </div>
        </td>

        <td><div class="user-name"><?= htmlspecialchars($u['fullname']) ?></div></td>
        <td><div class="user-email"><?= htmlspecialchars($u['email']) ?></div></td>

        <td>
            <?php if($u['role'] == 'admin'): ?>
                <span class="role-badge role-admin">Admin</span>
            <?php else: ?>
                <span class="role-badge role-user">User</span>
            <?php endif; ?>
        </td>

        <!-- Status column -->
        <td>
            <?php if($isOnline): ?>
                <span class="status-badge status-online">
                    <span class="status-dot"></span> Online
                </span>
            <?php else: ?>
                <span class="status-badge status-offline">
                    <span class="status-dot"></span>
                    <?php if(!empty($u['last_active'])): ?>
                        <?php
                            $diff = time() - strtotime($u['last_active']);
                            if ($diff < 3600)      echo floor($diff/60) . 'm ago';
                            elseif ($diff < 86400) echo floor($diff/3600) . 'h ago';
                            else                   echo date('M d', strtotime($u['last_active']));
                        ?>
                    <?php else: ?>
                        Offline
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </td>

        <td class="date-cell">
            <?= !empty($u['created_at']) ? date('M d, Y', strtotime($u['created_at'])) : 'N/A' ?>
        </td>

        <td>
            <div class="d-flex gap-1">
                <button class="action-btn warning edit-btn" title="Edit">
                    <i class="fas fa-pen"></i>
                </button>
                <a href="?delete_user=<?= $u['id'] ?>"
                   class="action-btn danger"
                   title="Delete"
                   onclick="return confirm('Delete this user?')">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
        </td>

    </tr>
    <?php endwhile; ?>

    <?php if(!$hasRows): ?>
    <tr>
        <td colspan="8">
            <div class="mit-empty">
                <i class="fas fa-users"></i>
                No users found
            </div>
        </td>
    </tr>
    <?php endif; ?>

    </tbody>
    </table>
    </div>

</div><!-- /.content -->

<!-- EDIT MODAL -->
<div class="modal fade mit-modal" id="modal">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">
    <div class="modal-header">
        <h5 class="modal-title">Edit User</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <input type="hidden" name="id" id="user-id">
        <div class="form-floating mb-2">
            <input type="text" id="user-name" name="fullname" class="form-control" placeholder="Name" required>
            <label>Name</label>
        </div>
        <div class="form-floating mb-2">
            <input type="email" id="user-email" name="email" class="form-control" placeholder="Email" required>
            <label>Email</label>
        </div>
        <select name="role" id="user-role" class="form-select">
            <option value="user">User</option>
            <option value="admin">Admin</option>
        </select>
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn-mit-submit" name="save_user">Save Changes</button>
    </div>
</form>
</div>
</div>
</div>

<!-- ADD MODAL -->
<div class="modal fade mit-modal" id="addUserModal">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">
    <div class="modal-header">
        <h5 class="modal-title">Add New User</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <input type="text"  name="fullname" class="form-control mb-2" placeholder="Full Name" required>
        <input type="email" name="email"    class="form-control mb-2" placeholder="Email" required>
        <div class="input-group mb-2">
            <input type="password" name="password" id="addPass" class="form-control" placeholder="Password" required>
            <button type="button" class="btn btn-outline-secondary" onclick="togglePass('addPass')">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        <select name="role" class="form-select">
            <option value="user">User</option>
            <option value="admin">Admin</option>
        </select>
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn-mit-submit add" name="add_user">Create User</button>
    </div>
</form>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePass(id){
    const input = document.getElementById(id);
    input.type = input.type === "password" ? "text" : "password";
}

document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.onclick = function(){
        const tr = this.closest('tr');
        document.getElementById('user-id').value    = tr.dataset.id;
        document.getElementById('user-name').value  = tr.dataset.name;
        document.getElementById('user-email').value = tr.dataset.email;
        document.getElementById('user-role').value  = tr.dataset.role;
        new bootstrap.Modal(document.getElementById('modal')).show();
    }
});

document.querySelectorAll(".auto-fade").forEach(el => {
    setTimeout(() => {
        el.style.opacity = "0";
        setTimeout(() => el.remove(), 500);
    }, 3000);
});
</script>

</body>
</html>