<?php
session_start();
include '../db.php';

// 🔒 SECURITY CHECK
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$id = $_SESSION['user_id'];

// FETCH USER
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$user = $result->fetch_assoc();

// 🧯 SAFETY CHECK (important)
if (!$user) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: #f4f6f9;
    font-family: 'Poppins', sans-serif;
}
.card {
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
</style>

</head>

<body>

<div class="container mt-5">

    <div class="card p-4 mx-auto" style="max-width:500px;">

        <h3 class="text-center mb-4">My Profile</h3>

        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['fullname']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>

        <a href="../home.php" class="btn btn-secondary w-100 mt-3">Back</a>
        <a href="../logout.php" class="btn btn-danger w-100 mt-2">Logout</a>

    </div>

</div>

</body>
</html>