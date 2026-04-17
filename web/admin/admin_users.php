<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

$currentPage = 'users';

// ---------------------
// ADD USER
// ---------------------
if(isset($_POST['add_user'])){

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM users WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $res = $check->get_result();

    if($res->num_rows > 0){
        header("Location: admin_users.php?error=email_exists");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $fullname, $email, $hashedPassword, $role);
    $stmt->execute();

    header("Location: admin_users.php?success=added");
    exit();
}

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

$total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$total_admins = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='admin'")->fetch_assoc()['total'];
$total_regular = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='user'")->fetch_assoc()['total'];

// NEW: USERS THIS MONTH
$users_this_month = $conn->query("
    SELECT COUNT(*) as total 
    FROM users 
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
    AND YEAR(created_at) = YEAR(CURRENT_DATE())
")->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Users</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<link rel="stylesheet" href="users.css">
<link rel="stylesheet" href="dashboard.css">
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

    <a href="admin_posts.php">
        <i class="fas fa-newspaper"></i> Posts
    </a>

    <a href="../logout.php">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<!-- CONTENT -->
<div class="content">

<h2>Users</h2>

<?php if(isset($_GET['success'])): ?>
<div class="alert alert-success">User added successfully!</div>
<?php endif; ?>

<?php if(isset($_GET['error']) && $_GET['error']=='email_exists'): ?>
<div class="alert alert-danger">Email already exists!</div>
<?php endif; ?>

<?php if(isset($_GET['error']) && $_GET['error']=='selfdelete'): ?>
<div class="alert alert-warning">You cannot delete your own account!</div>
<?php endif; ?>

<!-- ADD USER BUTTON -->
<button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
    + Add New User
</button>

<!-- STATS -->
<div class="row mb-4">

    <div class="col-md-3">
        <div class="card stat-card">
            <h2><?= $total_users ?></h2>
            <small>Total Users</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card">
            <h2><?= $total_admins ?></h2>
            <small>Admins</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card">
            <h2><?= $total_regular ?></h2>
            <small>Users</small>
        </div>
    </div>

    <!-- NEW CARD -->
    <div class="col-md-3">
        <div class="card stat-card">
            <h2><?= $users_this_month ?></h2>
            <small>Users This Month</small>
        </div>
    </div>

</div>

<!-- SEARCH -->
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

<!-- TABLE -->
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
<tr data-id="<?= $u['id'] ?>"
    data-name="<?= htmlspecialchars($u['fullname']) ?>"
    data-email="<?= htmlspecialchars($u['email']) ?>"
    data-role="<?= $u['role'] ?>">

<td><?= $u['id'] ?></td>
<td><?= htmlspecialchars($u['fullname']) ?></td>
<td><?= htmlspecialchars($u['email']) ?></td>
<td>
<?php if($u['role'] == 'admin'): ?>
    <span class="badge bg-danger px-3 py-2">
        <i class="fas fa-shield-alt"></i> Admin
    </span>
<?php else: ?>
    <span class="badge bg-primary px-3 py-2">
        <i class="fas fa-user"></i> User
    </span>
<?php endif; ?>
</td>

<td>
<a class="btn btn-danger btn-sm" href="?delete_user=<?= $u['id'] ?>">Delete</a>
<button class="btn btn-warning btn-sm edit-btn">Edit</button>
</td>

</tr>
<?php endwhile; ?>
</tbody>
</table>

<!-- EDIT MODAL -->
<div class="modal fade" id="modal">
<div class="modal-dialog">
<div class="modal-content">

<form method="POST">
<div class="modal-body">

<input type="hidden" name="id" id="user-id">

<div class="mb-2">
<label>Full Name</label>
<input type="text" id="user-name" class="form-control" readonly>
</div>

<div class="mb-2">
<label>Email</label>
<input type="email" id="user-email" class="form-control" readonly>
</div>

<div class="mb-2">
<label>Role</label>
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

<!-- ADD USER MODAL -->
<div class="modal fade" id="addUserModal">
<div class="modal-dialog">
<div class="modal-content">

<form method="POST">

<div class="modal-header">
<h5>Add New User</h5>
</div>

<div class="modal-body">

<input type="text" name="fullname" class="form-control mb-2" placeholder="Full Name" required>
<input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
<input type="password" name="password" class="form-control mb-2" placeholder="Password" required>

<select name="role" class="form-select">
<option value="user">User</option>
<option value="admin">Admin</option>
</select>

</div>

<div class="modal-footer">
<button name="add_user" class="btn btn-success">Add User</button>
</div>

</form>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function openModal(id,name,email,role){
    document.getElementById('user-id').value = id;
    document.getElementById('user-name').value = name;
    document.getElementById('user-email').value = email;
    document.getElementById('user-role').value = role;
    new bootstrap.Modal(document.getElementById('modal')).show();
}

document.querySelectorAll('.edit-btn').forEach(btn=>{
    btn.onclick = function(){
        let tr = this.closest('tr');
        openModal(tr.dataset.id, tr.dataset.name, tr.dataset.email, tr.dataset.role);
    }
});

const searchInput = document.getElementById('searchName');
const roleFilter = document.getElementById('filterRole');
const table = document.getElementById('usersTable').getElementsByTagName('tbody')[0];

function filterTable(){
    const search = searchInput.value.toLowerCase();
    const role = roleFilter.value;

    Array.from(table.rows).forEach(row=>{
        const name = row.cells[1].textContent.toLowerCase();
        const rowRole = row.dataset.role;

        row.style.display = (name.includes(search) && (!role || rowRole === role)) ? '' : 'none';
    });
}

searchInput.addEventListener('input', filterTable);
roleFilter.addEventListener('change', filterTable);
</script>

</body>
</html>