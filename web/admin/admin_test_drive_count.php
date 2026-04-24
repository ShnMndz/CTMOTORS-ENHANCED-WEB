<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['count' => 0]);
    exit();
}

$res = $conn->query("
    SELECT COUNT(*) as total 
    FROM test_drives 
    WHERE status = 'pending'
");

$count = $res->fetch_assoc()['total'];

echo json_encode(['count' => $count]);