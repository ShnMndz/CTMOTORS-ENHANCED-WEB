<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$id = $_SESSION['user_id'];

/* GET USER */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

/* UPDATE */
if (isset($_POST['update'])) {

    $fullname = $_POST['fullname'];
    $image = $user['profile_pic'];

    if (!empty($_FILES['profile_pic']['name'])) {

        $path = "../uploads/";
        if (!is_dir($path)) mkdir($path, 0777, true);

        $image = time() . "_" . $_FILES['profile_pic']['name'];
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $path . $image);
    }

    $stmt = $conn->prepare("UPDATE users SET fullname=?, profile_pic=? WHERE id=?");
    $stmt->bind_param("ssi", $fullname, $image, $id);
    $stmt->execute();

    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background:#0f0f0f; color:#fff; }
.box {
    max-width:500px;
    margin:50px auto;
    background:#1a1a1a;
    padding:25px;
    border-radius:12px;
}
img {
    width:100px;height:100px;border-radius:50%;
    display:block;margin:auto;
}
input {
    background:#111 !important;
    color:#fff !important;
    border:1px solid #333 !important;
}
</style>
</head>

<body>

<div class="box">

<h3 class="text-center">Edit Profile</h3>

<img src="../uploads/<?= $user['profile_pic'] ?? 'default.png' ?>">

<form method="POST" enctype="multipart/form-data" class="mt-3">

    <label>Full Name</label>
    <input type="text" name="fullname" class="form-control"
           value="<?= htmlspecialchars($user['fullname']) ?>" required>

    <label class="mt-2">Profile Picture</label>
    <input type="file" name="profile_pic" class="form-control">

    <button class="btn btn-danger w-100 mt-3" name="update">
        Save Changes
    </button>

</form>

<a href="profile.php" class="btn btn-secondary w-100 mt-2">Back</a>

</div>

</body>
</html>