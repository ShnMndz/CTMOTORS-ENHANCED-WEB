<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$id = $_SESSION['user_id'];

/* FETCH USER */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || $user['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

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

    header("Location: admin_dashboard.php");
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