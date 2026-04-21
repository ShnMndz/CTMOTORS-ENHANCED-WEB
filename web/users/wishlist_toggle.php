<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user'])) {
    echo "not_logged_in";
    exit();
}

$user_id = $_SESSION['user']['id'];
$vehicle_id = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : 0;

// Check if already saved
$stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id=? AND vehicle_id=?");
$stmt->bind_param("ii", $user_id, $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    // 🔴 REMOVE from wishlist
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id=? AND vehicle_id=?");
    $stmt->bind_param("ii", $user_id, $vehicle_id);
    $stmt->execute();

    echo "removed";

} else {

    // 🟢 ADD to wishlist
    $stmt = $conn->prepare("INSERT INTO wishlist (user_id, vehicle_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $vehicle_id);
    $stmt->execute();

    echo "added";
}
?>