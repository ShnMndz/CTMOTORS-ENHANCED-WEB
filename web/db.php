<?php
// Prevent multiple connection
if (!isset($conn)) {
    $conn = new mysqli("localhost", "root", "", "citimotors");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}

/* =========================
   ACTIVITY LOGGER
========================= */
if (!function_exists('logActivity')) {
    function logActivity($conn, $user, $action, $description = null) {

        $stmt = $conn->prepare("
            INSERT INTO activities (user, action, description, created_at)
            VALUES (?, ?, ?, NOW())
        ");

        if ($stmt) {
            $stmt->bind_param("sss", $user, $action, $description);
            $stmt->execute();
            $stmt->close();
        } else {
            error_log("Activity Log Error: " . $conn->error);
        }
    }
}
?>