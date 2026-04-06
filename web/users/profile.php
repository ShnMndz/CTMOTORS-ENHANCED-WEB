<?php
session_start();
include 'db.php';

// 🔒 Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user data
$result = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile</title>

<style>
body {
    font-family: Arial;
    background: #f4f6f9;
}

.container {
    max-width: 500px;
    margin: 50px auto;
    background: white;
    padding: 25px;
    border-radius: 10px;
    text-align: center;
}

img {
    border-radius: 50%;
    object-fit: cover;
}
</style>
</head>

<body>

<div class="container">

    <h2>My Profile</h2>

    <img src="uploads/<?php echo $user['profile_image']; ?>" width="120" height="120">

    <p><b>Username:</b> <?php echo $user['username']; ?></p>
    <p><b>Email:</b> <?php echo $user['email']; ?></p>

    <p><b>Bio:</b></p>
    <p><?php echo !empty($user['bio']) ? $user['bio'] : 'No bio yet'; ?></p>

    <a href="edit_profile.php">Edit Profile</a><br><br>
    <a href="logout.php">Logout</a>

</div>

</body>
</html>