<?php
$conn = new mysqli("localhost", "root", "", "citimotors");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* =========================
   ACTIVITY LOGGER
========================= */
function logActivity($conn, $user, $action){
    $stmt = $conn->prepare("INSERT INTO activity_logs (user, action) VALUES (?, ?)");
    $stmt->bind_param("ss", $user, $action);
    $stmt->execute();
}
?>