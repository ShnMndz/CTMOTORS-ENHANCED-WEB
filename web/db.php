<?php
$conn = new mysqli("localhost", "root", "", "citimotors");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* =========================
   ACTIVITY LOGGER
========================= */
function logActivity($conn, $user, $action, $description = null){
    $stmt = $conn->prepare("
        INSERT INTO activity_logs (user, action, description, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->bind_param("sss", $user, $action, $description);
    $stmt->execute();
}
?>