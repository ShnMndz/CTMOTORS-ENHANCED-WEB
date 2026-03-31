<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

// Update role
if(isset($_POST['update_role'])){
    $id = intval($_POST['id']);
    $role = $conn->real_escape_string($_POST['role']);
    $conn->query("UPDATE users SET role='$role' WHERE id=$id");
    header("Location: users.php");
    exit();
}

// Delete user
if(isset($_GET['delete_user'])){
    $id = intval($_GET['delete_user']);
    if(isset($_SESSION['user_id']) && $id == $_SESSION['user_id']){
        echo "<script>alert('You cannot delete your own account'); window.location='users.php';</script>";
        exit();
    } else {
        $conn->query("DELETE FROM users WHERE id=$id");
        header("Location: users.php");
        exit();
    }
}

// Fetch users
$result_users = $conn->query("SELECT id, fullname, email, role, created_at FROM users ORDER BY id DESC");
$total_users = $result_users->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Users - Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family:'Poppins',sans-serif; background:#f8f9fa; }
.sidebar { height:100vh;width:240px;position:fixed;top:0;left:0;background:#dc3545;color:#fff;padding:20px; }
.sidebar h4{text-align:center;margin-bottom:15px;}
.sidebar a{display:block;color:#fff;text-decoration:none;padding:10px;margin-bottom:5px;border-radius:6px;}
.sidebar a:hover{background:#c82333;}
.content{margin-left:260px;padding:20px;}
.form-control,.btn{border-radius:6px;}
</style>
</head>
<body>
<div class="sidebar">
    <h4>Admin Panel</h4>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="users.php">Users</a>
    <a href="vehicles.php">Vehicles</a>
    <a href="../logout.php">Logout</a>
</div>
<div class="content">
    <h2>User Management</h2>
    <p>Total Users: <?php echo $total_users; ?></p>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-danger">
                <tr>
                    <th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Registered</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if($total_users>0): while($user=$result_users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                            <select name="role" class="form-select form-select-sm">
                                <option value="user" <?php if($user['role']=='user') echo 'selected'; ?>>User</option>
                                <option value="admin" <?php if($user['role']=='admin') echo 'selected'; ?>>Admin</option>
                            </select>
                            <button name="update_role" class="btn btn-sm btn-primary">Save</button>
                        </form>
                    </td>
                    <td><?php echo $user['created_at']; ?></td>
                    <td>
                        <?php if(!isset($_SESSION['user_id']) || $user['id'] != $_SESSION['user_id']): ?>
                        <a href="users.php?delete_user=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?')">Delete</a>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="6" class="text-center">No users found</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>