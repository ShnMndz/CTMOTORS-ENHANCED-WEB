<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$id = $_SESSION['user_id'];

/* USER */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* REMOVE WISHLIST */
if (isset($_POST['remove'])) {
    $vehicle_id = intval($_POST['vehicle_id']);

    $del = $conn->prepare("
        DELETE FROM wishlist 
        WHERE user_id = ? AND vehicle_id = ?
    ");
    $del->bind_param("ii", $id, $vehicle_id);
    $del->execute();

    header("Location: saved_vehicles.php");
    exit();
}

/* WISHLIST DATA */
$wishlist = $conn->prepare("
    SELECT v.*
    FROM wishlist w
    JOIN vehicles v ON w.vehicle_id = v.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");

$wishlist->bind_param("i", $id);
$wishlist->execute();
$result = $wishlist->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Saved Vehicles</title>

<link rel="stylesheet" href="user_dashboard.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>

/* PAGE */
body {
    background: #f5f7fb;
    color: #222;
}

/* CARD (LIGHT THEME) */
.wishlist-card {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 14px;
    padding: 15px;
    transition: 0.2s ease;
    text-align: center;
}

.wishlist-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

/* IMAGE */
.wishlist-card img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 10px;
}

/* REMOVE BUTTON */
.remove-btn {
    margin-top: 8px;
    background: transparent;
    border: 1px solid #dc3545;
    color: #dc3545;
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 13px;
    width: 100%;
}

.remove-btn:hover {
    background: #dc3545;
    color: #fff;
}

/* VIEW BUTTON */
.btn-view {
    width: 100%;
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

            <a href="saved_vehicles.php" class="menu-btn active">
                <i class="fa-solid fa-heart"></i>
                Saved Vehicles
            </a>
        </nav>

    </aside>

    <!-- MAIN -->
    <main class="panel">

        <div class="top-bar">
            <div>
                <h3>Saved Vehicles</h3>
                <p class="text-muted">Your wishlist collection ❤️</p>
            </div>

            <a href="../home.php" class="btn btn-outline-dark">
                Return to Homepage
            </a>
        </div>

        <div class="grid">

            <div class="card full">

                <h4 class="mb-3">❤️ Your Saved Vehicles</h4>

                <div class="row g-3">

                    <?php if ($result->num_rows > 0): ?>

                        <?php while ($row = $result->fetch_assoc()): ?>

                            <div class="col-md-4">

                                <div class="wishlist-card">

                                    <img src="../img/<?= $row['image'] ?: 'default-car.png' ?>">

                                    <h6 class="mt-2">
                                        <?= htmlspecialchars($row['model_name']) ?>
                                    </h6>

                                    <small class="text-muted">
                                        ₱<?= number_format($row['price']) ?>
                                    </small>

                                    <!-- VIEW -->
                                    <div class="mt-2">
                                        <a href="product-details.php?id=<?= $row['id'] ?>"
                                           class="btn btn-sm btn-outline-dark btn-view">
                                            View
                                        </a>
                                    </div>

                                    <!-- REMOVE -->
                                    <form method="POST" onsubmit="return confirm('Remove this vehicle from wishlist?');">
                                        <input type="hidden" name="vehicle_id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="remove" class="remove-btn">
                                            Remove
                                        </button>
                                    </form>

                                </div>

                            </div>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <p class="text-muted">No saved vehicles yet.</p>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </main>

</div>

</body>
</html>