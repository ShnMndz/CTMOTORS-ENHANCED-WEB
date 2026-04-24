<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . "/citimotorsweb/web/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// get notif id
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// safety check: only delete OWN notification
$stmt = $conn->prepare("
    DELETE FROM notifications 
    WHERE id = ? AND user_id = ?
");

$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();

// go back to previous page
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>