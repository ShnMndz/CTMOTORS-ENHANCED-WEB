<?php
// Prevent multiple connection
if (!isset($conn)) {
    $conn = new mysqli("localhost", "root", "", "citimotors");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Create activities table if not exists
    $conn->query("
        CREATE TABLE IF NOT EXISTS activities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user VARCHAR(255) NOT NULL,
            action VARCHAR(255) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    if (isset($_SESSION['user_id'])) {
    $conn->query("UPDATE users SET last_active = NOW() WHERE id = " . intval($_SESSION['user_id']));
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

