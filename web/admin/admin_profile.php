<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$currentPage = 'profile';

$id = $_SESSION['user_id'];

/* FETCH USER */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: ../login.php");
    exit();
}

$currentPage = 'profile';

$pending_test_drives = $conn->query("
    SELECT COUNT(*) as total 
    FROM test_drives 
    WHERE status = 'pending'
")->fetch_assoc()['total'];

/* =========================
   UPDATE PROFILE
========================= */
if (isset($_POST['save_all'])) {

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);

    $updated = false;

    /* UPDATE NAME + EMAIL */
    if ($fullname !== $user['fullname'] || $email !== $user['email']) {
        if (!empty($fullname) && !empty($email)) {
            $update = $conn->prepare("UPDATE users SET fullname=?, email=? WHERE id=?");
            $update->bind_param("ssi", $fullname, $email, $id);
            $update->execute();
            $_SESSION['user'] = $fullname; // Update session
            $updated = true;
        }
    }

    /* PROFILE PICTURE */
    if (!empty($_FILES['profile_pic']['name'])) {

        $fileName = time() . "_" . basename($_FILES['profile_pic']['name']);
        $target = "../uploads/" . $fileName;

        $allowed = ['jpg','jpeg','png','webp'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {

            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target)) {

                if (!empty($user['profile_pic']) && file_exists("../uploads/" . $user['profile_pic'])) {
                    unlink("../uploads/" . $user['profile_pic']);
                }

                $update = $conn->prepare("UPDATE users SET profile_pic=? WHERE id=?");
                $update->bind_param("si", $fileName, $id);
                $update->execute();

                $updated = true;
            }

        } else {
            $_SESSION['message'] = "Invalid image format.";
            $_SESSION['type'] = "danger";
            header("Location: admin_profile.php");
            exit();
        }
    }

    $_SESSION['message'] = $updated ? "Profile updated successfully!" : "No changes made.";
    $_SESSION['type'] = $updated ? "success" : "warning";

    if ($updated) {
        logActivity($conn, $_SESSION['user'], 'Updated Profile', "Updated profile information");
    }

    header("Location: admin_profile.php");
    exit();
}

/* =========================
   CHANGE PASSWORD (ADMIN)
========================= */
if (isset($_POST['change_password'])) {

    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (!password_verify($current, $user['password'])) {
        $_SESSION['message'] = "Current password is incorrect.";
        $_SESSION['type'] = "danger";
        header("Location: admin_profile.php");
        exit();
    }

    if ($new !== $confirm) {
        $_SESSION['message'] = "Passwords do not match.";
        $_SESSION['type'] = "danger";
        header("Location: admin_profile.php");
        exit();
    }

    if (strlen($new) < 6) {
        $_SESSION['message'] = "Password must be at least 6 characters.";
        $_SESSION['type'] = "warning";
        header("Location: admin_profile.php");
        exit();
    }

    $hashed = password_hash($new, PASSWORD_DEFAULT);

    $update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $update->bind_param("si", $hashed, $id);
    $update->execute();

    logActivity($conn, $_SESSION['user'], 'Changed Password', "Changed admin password");

    $_SESSION['message'] = "Password changed successfully!";
    $_SESSION['type'] = "success";

    header("Location: admin_profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<link rel="stylesheet" href="users.css">
<link rel="stylesheet" href="admin_dashboard.css">

<style>
body{
    background:#121212;
    color:#fff;
}

.card{
    max-width:500px;
    margin:auto;
    margin-top:40px;
    background:#1a1a1a;
    padding:25px;
    border-radius:15px;
}

.profile-pic{
    width:120px;
    height:120px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid #ff4d4d;
    cursor:pointer;
}

.form-control{
    background:#222;
    border:none;
    color:#fff;
}

.btn-main{
    background:#ff4d4d;
    border:none;
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

    <a href="admin_profile.php" class="<?= $currentPage=='profile'?'active':'' ?>"><i class="fas fa-user"></i>Your Profile</a>

    <a href="admin_users.php" class="<?= $currentPage=='users'?'active':'' ?>">
        <i class="fas fa-users"></i> Manage Users
    </a>

    <a href="admin_vehicles.php">
        <i class="fas fa-car"></i> Manage Vehicles
    </a>

    <a href="admin_posts.php">
        <i class="fas fa-newspaper"></i> Posts
    </a>

    <a href="recent_activity.php">
        <i class="fas fa-history"></i> Recent Activity
    </a>

    <a href="admin_test_drives.php">
        <i class="fas fa-key"></i> Test Drive
        <?php if($pending_test_drives > 0): ?>
            <span class="badge bg-danger badge-notif"><?= $pending_test_drives ?></span>
        <?php endif; ?>
    </a>

    <a href="../logout.php">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<!-- CONTENT -->
<div class="content">

<div class="container">

<?php if (isset($_SESSION['message'])): ?>
<div class="alert alert-<?= $_SESSION['type'] ?> mt-3">
    <?= $_SESSION['message']; ?>
</div>
<?php unset($_SESSION['message']); unset($_SESSION['type']); endif; ?>

<!-- PROFILE UPDATE -->
<div class="card">

<h4 class="text-center mb-3">Admin Profile</h4>

<form method="POST" enctype="multipart/form-data">

<div class="text-center mb-3">
<label for="fileInput">
<img id="preview"
     src="../uploads/<?= $user['profile_pic'] ?: 'default.png' ?>"
     class="profile-pic">
</label>
<input type="file" name="profile_pic" id="fileInput" hidden>
</div>

<label>Full Name</label>
<input type="text" name="fullname" class="form-control mb-3"
       value="<?= htmlspecialchars($user['fullname']) ?>" required>

<label>Email</label>
<input type="email" name="email" class="form-control mb-3"
       value="<?= htmlspecialchars($user['email']) ?>" required>

<button type="submit" name="save_all" class="btn btn-main w-100">
Save Changes
</button>

</form>

</div>

<!-- PASSWORD CHANGE -->
<div class="card mt-4">

<h5 class="text-center mb-3">Change Password</h5>

<form method="POST">

<label>Current Password</label>
<input type="password" name="current_password" class="form-control mb-2" required>

<label>New Password</label>
<input type="password" name="new_password" class="form-control mb-2" required>

<label>Confirm Password</label>
<input type="password" name="confirm_password" class="form-control mb-3" required>

<button type="submit" name="change_password" class="btn btn-main w-100">
Update Password
</button>

</form>

</div>

</div>

</div>

<script>
document.getElementById("fileInput").addEventListener("change", function(event){
    const file = event.target.files[0];

    if(file){
        const reader = new FileReader();
        reader.onload = e => document.getElementById("preview").src = e.target.result;
        reader.readAsDataURL(file);
    }
});
</script>

</body>
</html>