<?php
session_start();
include 'db.php';

if(isset($_SESSION['user_id'])){
    $id = $_SESSION['user_id'];
    $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $id");
}