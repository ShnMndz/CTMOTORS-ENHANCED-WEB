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

/* =========================
   UPDATE PROFILE
========================= */
if (isset($_POST['save_all'])) {

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);

    $updated = false;

    if ($fullname !== $user['fullname'] || $email !== $user['email']) {
        if (!empty($fullname) && !empty($email)) {
            $update = $conn->prepare("UPDATE users SET fullname=?, email=? WHERE id=?");
            $update->bind_param("ssi", $fullname, $email, $id);
            $update->execute();
            $updated = true;
        }
    }

    if (!empty($_FILES['profile_pic']['name'])) {

        $fileName = time() . "_" . basename($_FILES['profile_pic']['name']);
        $target = "../uploads/" . $fileName;

        $allowed = ['jpg','jpeg','png'];
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
            header("Location: profile.php");
            exit();
        }
    }

    $_SESSION['message'] = $updated ? "Profile updated successfully!" : "No changes made.";
    $_SESSION['type'] = $updated ? "success" : "warning";

    header("Location: profile.php");
    exit();
}

/* =========================
   CHANGE PASSWORD
========================= */
if (isset($_POST['change_password'])) {

    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (!password_verify($current, $user['password'])) {
        $_SESSION['message'] = "Current password is incorrect.";
        $_SESSION['type'] = "danger";
        header("Location: profile.php");
        exit();
    }

    if ($new !== $confirm) {
        $_SESSION['message'] = "New passwords do not match.";
        $_SESSION['type'] = "danger";
        header("Location: profile.php");
        exit();
    }

    if (strlen($new) < 6) {
        $_SESSION['message'] = "Password must be at least 6 characters.";
        $_SESSION['type'] = "warning";
        header("Location: profile.php");
        exit();
    }

    $hashed = password_hash($new, PASSWORD_DEFAULT);

    $update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $update->bind_param("si", $hashed, $id);
    $update->execute();

    $_SESSION['message'] = "Password changed successfully!";
    $_SESSION['type'] = "success";

    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile</title>

<link rel="stylesheet" href="user_dashboard.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
body {
    background: #f5f7fb;
    color: #222;
}

.panel {
    padding: 30px;
    overflow-y: auto;
}

.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.profile-card {
    max-width: 500px;
    background: #fff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    margin-bottom: 24px;
}

.profile-pic {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #ff4d4d;
    cursor: pointer;
}

.form-control {
    background: #f1f3f6;
    border: none;
    color: #222;
}

.form-control:focus {
    background: #e8eaf0;
    color: #222;
    box-shadow: none;
}

.btn-main {
    background: #ff4d4d;
    border: none;
    color: #fff;
}

.btn-main:hover {
    background: #e03e3e;
    color: #fff;
}
</style>
</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <div class="profile-box">
            <img src="../uploads/<?= $user['profile_pic'] ?: 'default.png' ?>" class="avatar">

            <div class="username">
                <?= htmlspecialchars($user['fullname']) ?>
            </div>

            <div class="small">
                <?= htmlspecialchars($user['email']) ?>
            </div>

            <div class="small">
                Member since: <?= date("Y") ?>
            </div>

            <a href="profile.php">
                <button class="btn-edit">Edit Profile</button>
            </a>
        </div>

        <nav class="menu">
            <a href="user_dashboard.php" class="menu-btn">
                <i class="fa-solid fa-user"></i>
                Profile Status
            </a>

            <a href="my_testdrives.php" class="menu-btn">
                <i class="fa-solid fa-calendar-check"></i>
                Test Drive Request
            </a>

            <a href="saved_vehicles.php" class="menu-btn">
                <i class="fa-solid fa-heart"></i>
                Saved Vehicles
            </a>
        </nav>

    </aside>

    <!-- MAIN PANEL -->
    <main class="panel">

        <div class="top-bar">
            <div>
                <h3>Edit Profile 👤</h3>
                <p class="text-muted mb-0">Manage your account details and password</p>
            </div>

            <a href="../home.php" class="btn btn-outline-dark">
                Return to Homepage
            </a>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['type'] ?> mt-3 mb-4" style="max-width: 500px;">
            <?= $_SESSION['message']; ?>
        </div>
        <?php unset($_SESSION['message']); unset($_SESSION['type']); endif; ?>

        <!-- PROFILE FORM -->
        <div class="profile-card">

            <h4 class="mb-3">Personal Info</h4>

            <form method="POST" enctype="multipart/form-data">

                <div class="text-center mb-3">
                    <label for="fileInput">
                        <img id="preview"
                             src="../uploads/<?= $user['profile_pic'] ?: 'default.png' ?>"
                             class="profile-pic">
                    </label>
                    <input type="file" name="profile_pic" id="fileInput" hidden>
                    <div class="small text-muted mt-1">Click photo to change</div>
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

        <!-- CHANGE PASSWORD -->
        <div class="profile-card">

            <h5 class="mb-3">Change Password</h5>

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

    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById("fileInput").addEventListener("change", function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById("preview").src = e.target.result;
        reader.readAsDataURL(file);
    }
});
</script>

</body>
</html>