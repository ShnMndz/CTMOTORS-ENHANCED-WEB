<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

$currentPage = 'users';

// ---------------------
// UPDATE USER ROLE
// ---------------------
if(isset($_POST['save_user'])){
    $stmt = $conn->prepare("UPDATE users SET role=? WHERE id=?");
    $stmt->bind_param("si", $_POST['role'], $_POST['id']);
    $stmt->execute();
    header("Location: admin_users.php");
    exit();
}

// ---------------------
// DELETE USER
// ---------------------
if(isset($_GET['delete_user'])){
    if($_GET['delete_user'] == $_SESSION['user_id']){
        header("Location: admin_users.php?error=selfdelete");
        exit();
    }
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $_GET['delete_user']);
    $stmt->execute();
    header("Location: admin_users.php");
    exit();
}

// ---------------------
// FETCH USERS & STATS
// ---------------------
$result_users = $conn->query("SELECT * FROM users ORDER BY id DESC");

// User stats
$total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$total_admins = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='admin'")->fetch_assoc()['total'];
$total_regular = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='user'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Users</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="users.css">
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h4>Admin Panel</h4>
    <a href="admin_dashboard.php" class="<?= $currentPage=='dashboard'?'active':'' ?>">
        <i class="fas fa-chart-line"></i> Dashboard
    </a>
    <a href="admin_users.php" class="<?= $currentPage=='users'?'active':'' ?>">
        <i class="fas fa-users"></i> Manage Users
    </a>
    <a href="admin_vehicles.php" class="<?= $currentPage=='vehicles'?'active':'' ?>">
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

<h2>Users</h2>

<!-- USER STAT CARDS -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stat-card">
            <h2><?= $total_users ?></h2>
            <small>Total Users</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <h2><?= $total_admins ?></h2>
            <small>Admins</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <h2><?= $total_regular ?></h2>
            <small>Users</small>
        </div>
    </div>
</div>

<!-- SEARCH & FILTER -->
<div class="row mb-3">
    <div class="col-md-4">
        <input type="text" id="searchName" class="form-control" placeholder="Search by Name">
    </div>
    <div class="col-md-3">
        <select id="filterRole" class="form-select">
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="user">User</option>
        </select>
    </div>
</div>

<!-- USER TABLE -->
<table class="table table-bordered" id="usersTable">
<thead class="table-success">
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php while($u=$result_users->fetch_assoc()): ?>
<tr 
data-id="<?= $u['id'] ?>"
data-name="<?= htmlspecialchars($u['fullname'], ENT_QUOTES) ?>"
data-email="<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>"
data-role="<?= $u['role'] ?>"
>
<td><?= $u['id'] ?></td>
<td><?= htmlspecialchars($u['fullname']) ?></td>
<td><?= htmlspecialchars($u['email']) ?></td>
<td><?= ucfirst($u['role']) ?></td>
<td>
<a class="btn btn-danger btn-sm" href="?delete_user=<?= $u['id'] ?>">Delete</a>
<button class="btn btn-warning btn-sm edit-btn">Edit</button>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<!-- MODAL -->
<div class="modal fade" id="modal">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">
<div class="modal-body">
<input type="hidden" name="id" id="user-id">
<div class="mb-2">
<label for="user-name" class="form-label">Full Name</label>
<input type="text" class="form-control" id="user-name" readonly>
</div>
<div class="mb-2">
<label for="user-email" class="form-label">Email</label>
<input type="email" class="form-control" id="user-email" readonly>
</div>
<div class="mb-2">
<label for="user-role" class="form-label">Role</label>
<select name="role" id="user-role" class="form-select">
<option value="user">User</option>
<option value="admin">Admin</option>
</select>
</div>
</div>
<div class="modal-footer">
<button name="save_user" class="btn btn-primary">Save</button>
</div>
</form>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Edit modal
function openModal(id='', name='', email='', role=''){
    document.getElementById('user-id').value = id;
    document.getElementById('user-name').value = name;
    document.getElementById('user-email').value = email;
    document.getElementById('user-role').value = role;
    new bootstrap.Modal(document.getElementById('modal')).show();
}

document.querySelectorAll('.edit-btn').forEach(btn=>{
    btn.onclick = function(){
        let tr = this.closest('tr');
        openModal(
            tr.dataset.id,
            tr.dataset.name,
            tr.dataset.email,
            tr.dataset.role
        );
    }
});

// Search & Filter
const searchInput = document.getElementById('searchName');
const roleFilter = document.getElementById('filterRole');
const table = document.getElementById('usersTable').getElementsByTagName('tbody')[0];

function filterTable(){
    const search = searchInput.value.toLowerCase();
    const role = roleFilter.value;

    Array.from(table.rows).forEach(row=>{
        const name = row.cells[1].textContent.toLowerCase();
        const rowRole = row.dataset.role;
        const matchName = name.includes(search);
        const matchRole = !role || rowRole === role;
        row.style.display = (matchName && matchRole) ? '' : 'none';
    });
}

searchInput.addEventListener('input', filterTable);
roleFilter.addEventListener('change', filterTable);
</script>

</body>
</html>