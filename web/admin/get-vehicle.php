<?php
include '../db.php';
$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM vehicles WHERE id=$id");
echo json_encode($result->fetch_assoc());
?>