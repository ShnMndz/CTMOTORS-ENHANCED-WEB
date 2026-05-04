<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

$currentPage = 'users';

/* ---------------------
   ADD USER
--------------------- */
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

/* ---------------------
   UPDATE USER
--------------------- */
if(isset($_POST['save_user'])){

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $id = $_POST['id'];

    $check = $conn->prepare("SELECT id FROM users WHERE email=? AND id<>?");
    $check->bind_param("si", $email, $id);
    $check->execute();
    $res = $check->get_result();

    if($res->num_rows > 0){
        header("Location: admin_users.php?error=email_exists");
        exit();
    }

    $stmt = $conn->prepare("UPDATE users SET fullname=?, email=?, role=? WHERE id=?");
    $stmt->bind_param("sssi", $fullname, $email, $role, $id);
    $stmt->execute();

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

    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $_GET['delete_user']);
    $stmt->execute();

    header("Location: admin_users.php?success=deleted");
    exit();
}

/* ---------------------
   FETCH DATA
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

<link rel="stylesheet" href="users.css">
<link rel="stylesheet" href="admin_dashboard.css">

<style>
.alert{transition:0.5s ease;}
.stat-card{
    padding:20px;
    border-radius:12px;
    text-align:center;
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

    <a href="admin_profile.php"><i class="fas fa-user"></i>Your Profile</a>

    <a href="admin_users.php" class="<?= $currentPage=='users'?'active':'' ?>">
        <i class="fas fa-users"></i> Manage Users
    </a>

    <a href="admin_vehicles.php">
        <i class="fas fa-car"></i> Manage Vehicles
    </a>

    <a href="admin_posts.php">
        <i class="fas fa-newspaper"></i> Posts
    </a>

    <a href="admin_test_drives.php">
        <i class="fas fa-key"></i> Test Drive
    </a>

    <a href="../logout.php">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<!-- CONTENT -->
<div class="content">

<h2>Users</h2>

<!-- ALERTS -->
<?php if(isset($_GET['success']) && $_GET['success']=='added'): ?>
<div class="alert alert-success auto-fade">User added successfully!</div>
<?php endif; ?>

<?php if(isset($_GET['success']) && $_GET['success']=='updated'): ?>
<div class="alert alert-success auto-fade">User updated successfully!</div>
<?php endif; ?>

<?php if(isset($_GET['success']) && $_GET['success']=='deleted'): ?>
<div class="alert alert-success auto-fade">User deleted successfully!</div>
<?php endif; ?>

<?php if(isset($_GET['error']) && $_GET['error']=='email_exists'): ?>
<div class="alert alert-danger auto-fade">Email already exists!</div>
<?php endif; ?>

<?php if(isset($_GET['error']) && $_GET['error']=='selfdelete'): ?>
<div class="alert alert-warning auto-fade">You cannot delete your own account!</div>
<?php endif; ?>

<button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
+ Add New User
</button>

<!-- SEARCH FILTER -->
<div class="mb-3">
<form method="GET" class="d-flex gap-2">
<input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?= htmlspecialchars($search) ?>">
<button type="submit" class="btn btn-primary">
<i class="fas fa-search"></i> Search
</button>
<?php if (!empty($search)): ?>
<a href="admin_users.php" class="btn btn-secondary">
<i class="fas fa-times"></i> Clear
</a>
<?php endif; ?>
</form>
</div>

<!-- TABLE -->
<table class="table table-bordered">
<thead class="table-success">
<tr>
<th>ID</th>
<th>Avatar</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Created At</th>
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
<td>
<?php if(!empty($u['profile_pic'])): ?>
<img src="../uploads/<?= $u['profile_pic'] ?>" width="50" height="50" style="border-radius:50%;">
<?php else: ?>
<i class="fas fa-user-circle" style="font-size:50px; color:#ccc;"></i>
<?php endif; ?>
</td>
<td><?= htmlspecialchars($u['fullname']) ?></td>
<td><?= htmlspecialchars($u['email']) ?></td>

<td>
<?php if($u['role']=='admin'): ?>
<span class="badge bg-danger">Admin</span>
<?php else: ?>
<span class="badge bg-primary">User</span>
<?php endif; ?>
</td>

<td>
<?php if(!empty($u['created_at'])): ?>
<?= date('Y-m-d H:i', strtotime($u['created_at'])) ?>
<?php else: ?>
N/A
<?php endif; ?>
</td>

<td>
<a class="btn btn-sm btn-danger"
href="?delete_user=<?= $u['id'] ?>"
onclick="return confirm('Delete this user?')"
title="Delete">
<i class="fas fa-trash"></i>
</a>

<button class="btn btn-sm btn-warning edit-btn" title="Edit">
<i class="fa-solid fa-gear"></i>
</button>
</td>

</tr>
<?php endwhile; ?>
</tbody>
</table>

</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="modal">
<div class="modal-dialog">
<div class="modal-content">

<form method="POST">
<div class="modal-header">
<h5>Edit User</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<input type="hidden" name="id" id="user-id">

<div class="form-floating mb-2">
<input type="text" id="user-name" name="fullname" class="form-control" required>
<label>Name</label>
</div>

<div class="form-floating mb-2">
<input type="email" id="user-email" name="email" class="form-control" required>
<label>Email</label>
</div>

<select name="role" id="user-role" class="form-select">
<option value="user">User</option>
<option value="admin">Admin</option>
</select>

</div>

<div class="modal-footer">
<button class="btn btn-primary w-100" name="save_user">Save</button>
</div>

</form>

</div>
</div>
</div>

<!-- ADD MODAL -->
<div class="modal fade" id="addUserModal">
<div class="modal-dialog">
<div class="modal-content">

<form method="POST">

<div class="modal-header">
<h5>Add User</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<input type="text" name="fullname" class="form-control mb-2" placeholder="Full Name" required>
<input type="email" name="email" class="form-control mb-2" placeholder="Email" required>

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
<button class="btn btn-success w-100" name="add_user">Create</button>
</div>

</form>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function togglePass(id){
    let input = document.getElementById(id);
    input.type = input.type === "password" ? "text" : "password";
}

document.querySelectorAll('.edit-btn').forEach(btn=>{
    btn.onclick = function(){
        let tr = this.closest('tr');
        document.getElementById('user-id').value = tr.dataset.id;
        document.getElementById('user-name').value = tr.dataset.name;
        document.getElementById('user-email').value = tr.dataset.email;
        document.getElementById('user-role').value = tr.dataset.role;
        new bootstrap.Modal(document.getElementById('modal')).show();
    }
});

// AUTO FADE ALERTS
document.querySelectorAll(".auto-fade").forEach(alert=>{
    setTimeout(()=>{
        alert.style.opacity="0";
        setTimeout(()=>alert.remove(),500);
    },3000);
});
</script>

</body>
</html>