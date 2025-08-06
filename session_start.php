<?php
session_start();
include 'conn.php';

function require_admin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: login.php");
        exit();
    }
}

function require_worker() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
        header("Location: login.php");
        exit();
    }
}
?>
