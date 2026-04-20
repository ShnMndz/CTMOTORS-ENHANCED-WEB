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

/* SAVE ALL */
if (isset($_POST['save_all'])) {

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);

    $updated = false;

    // CHECK NAME + EMAIL CHANGES
    if ($fullname !== $user['fullname'] || $email !== $user['email']) {

        if (!empty($fullname) && !empty($email)) {
            $update = $conn->prepare("UPDATE users SET fullname=?, email=? WHERE id=?");
            $update->bind_param("ssi", $fullname, $email, $id);
            $update->execute();
            $updated = true;
        }
    }

    // CHECK IMAGE CHANGE
    if (!empty($_FILES['profile_pic']['name'])) {

        $fileName = time() . "_" . basename($_FILES['profile_pic']['name']);
        $target = "../uploads/" . $fileName;

        $allowed = ['jpg','jpeg','png'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {

            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target)) {

                // DELETE OLD IMAGE
                if (!empty($user['profile_pic']) && file_exists("../uploads/" . $user['profile_pic'])) {
                    unlink("../uploads/" . $user['profile_pic']);
                }

                $update = $conn->prepare("UPDATE users SET profile_pic=? WHERE id=?");
                $update->bind_param("si", $fileName, $id);
                $update->execute();

                $updated = true;
            }

        } else {
            $_SESSION['message'] = "Invalid image format (JPG, JPEG, PNG only).";
            $_SESSION['type'] = "danger";
            header("Location: profile.php");
            exit();
        }
    }

    // FINAL MESSAGE
    if ($updated) {
        $_SESSION['message'] = "Profile updated successfully!";
        $_SESSION['type'] = "success";
    } else {
        $_SESSION['message'] = "No changes made.";
        $_SESSION['type'] = "warning";
    }

    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background:#121212;
    color:#fff;
    font-family:'Segoe UI', sans-serif;
}

.sidebar{
    background:#1a1a1a;
    min-height:100vh;
    padding:30px;
    border-right:1px solid #2a2a2a;
}

.profile-pic{
    width:130px;
    height:130px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid #ff4d4d;
    cursor:pointer;
    transition:0.3s;
}

.profile-pic:hover{
    transform:scale(1.05);
}

.form-control{
    background:#222;
    border:none;
    color:#fff;
}

.form-control:focus{
    background:#222;
    color:#fff;
}

.btn-save{
    background:#ff4d4d;
    border:none;
}

.btn-save:hover{
    background:#e60023;
}

.content{
    background:#f4f4f4;
    color:#000;
    min-height:100vh;
    padding:40px;
}

.small-text{
    font-size:12px;
    color:#aaa;
}
</style>
</head>

<body>

<div class="container-fluid">
<div class="row">

<!-- SIDEBAR -->
<div class="col-md-3 sidebar">

    <h5 class="mb-4 text-center">My Profile</h5>

    <!-- ALERT MESSAGE -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['type'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php 
        unset($_SESSION['message']);
        unset($_SESSION['type']);
        ?>
    <?php endif; ?>

    <!-- FORM -->
    <form method="POST" enctype="multipart/form-data">

        <!-- PROFILE PIC -->
        <div class="text-center mb-4">
            <label for="fileInput">
                <img id="preview"
                     src="../uploads/<?= $user['profile_pic'] ?: 'default.png' ?>"
                     class="profile-pic mb-2">
            </label>

            <input type="file" name="profile_pic" id="fileInput" hidden>
            <div class="small-text">Click image to change</div>
        </div>

        <!-- INFO -->
        <label>Full Name</label>
        <input type="text" name="fullname" class="form-control mb-3"
               value="<?= htmlspecialchars($user['fullname']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" class="form-control mb-3"
               value="<?= htmlspecialchars($user['email']) ?>" required>

        <button type="submit" name="save_all" class="btn btn-save w-100">
            Save All Changes
        </button>

    </form>

    <a href="../home.php" class="btn btn-outline-light mt-4 w-100">
        Return
    </a>

</div>

<!-- CONTENT -->
<div class="col-md-9 content">
    <h3>Dashboard</h3>
    <p>This is your main content area (Test Drive Booking, etc).</p>
</div>

</div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// IMAGE PREVIEW
document.getElementById("fileInput").addEventListener("change", function(event){
    const file = event.target.files[0];

    if(file){
        const reader = new FileReader();

        reader.onload = function(e){
            document.getElementById("preview").src = e.target.result;
        }

        reader.readAsDataURL(file);
    }
});

// AUTO HIDE ALERT
setTimeout(() => {
    let alert = document.querySelector('.alert');
    if(alert){
        alert.classList.remove('show');
        alert.classList.add('fade');
    }
}, 3000);
</script>

</body>
</html>