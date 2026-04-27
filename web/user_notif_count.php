<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/citimotorsweb/web/db.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM notifications 
    WHERE user_id = ? AND is_read = 0
");
$stmt->bind_param("i", $user_id);
$stmt->execute();

$count = $stmt->get_result()->fetch_assoc()['total'];

echo json_encode(['count' => $count]);