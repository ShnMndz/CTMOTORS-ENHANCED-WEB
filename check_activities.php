<?php
include 'web/db.php';

$result = $conn->query("SHOW TABLES LIKE 'activities'");
if ($result && $result->num_rows > 0) {
    echo "Table exists\n";
    // Show structure
    $struct = $conn->query("DESCRIBE activities");
    if ($struct) {
        echo "Table structure:\n";
        while ($row = $struct->fetch_assoc()) {
            echo $row['Field'] . " - " . $row['Type'] . "\n";
        }
    }
} else {
    echo "Table does not exist\n";
    $conn->query("CREATE TABLE IF NOT EXISTS activities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user VARCHAR(255) NOT NULL,
        action VARCHAR(255) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    $result2 = $conn->query("SHOW TABLES LIKE 'activities'");
    if ($result2 && $result2->num_rows > 0) {
        echo "Table created\n";
    } else {
        echo "Failed to create table\n";
    }
}

// Test insert
$stmt = $conn->prepare("INSERT INTO activities (user, action, description, created_at) VALUES (?, ?, ?, NOW())");
if ($stmt) {
    $stmt->bind_param("sss", $user, $action, $desc);
    $user = "Test User";
    $action = "Test Action";
    $desc = "Test Description";
    if ($stmt->execute()) {
        echo "Test insert successful\n";
    } else {
        echo "Test insert failed: " . $stmt->error . "\n";
    }
    $stmt->close();
} else {
    echo "Prepare failed: " . $conn->error . "\n";
}

// Fetch
$result3 = $conn->query("SELECT * FROM activities ORDER BY created_at DESC LIMIT 5");
if ($result3) {
    echo "Recent activities:\n";
    while ($row = $result3->fetch_assoc()) {
        echo $row['user'] . " - " . $row['action'] . " - " . $row['description'] . " - " . $row['created_at'] . "\n";
    }
} else {
    echo "Fetch failed: " . $conn->error . "\n";
}
?>