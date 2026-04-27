<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/citimotorsweb/web/db.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$user_id = $_SESSION['user_id'];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("
    DELETE FROM notifications 
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();

echo json_encode(['success' => true]);