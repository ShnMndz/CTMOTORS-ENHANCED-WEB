<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'];

    $stmt = $conn->prepare("UPDATE users SET username=? WHERE id=?");
    $stmt->bind_param("si", $username, $user_id);
    $stmt->execute();

    // update session too (IMPORTANT)
    $_SESSION['user']['username'] = $username;

    header("Location: profile.php");
    exit();
}

$stmt = $conn->prepare("SELECT username FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5" style="max-width:500px;">
    <div class="card p-4 shadow-sm">

        <h3>Edit Profile</h3>
        <hr>

        <form method="POST">

            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" value="<?= $user['username']; ?>" class="form-control" required>
            </div>

            <button class="btn btn-success">Save Changes</button>

        </form>

    </div>
</div>

</body>
</html>