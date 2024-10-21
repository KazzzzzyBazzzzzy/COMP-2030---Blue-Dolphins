<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkUserRole($requiredRole) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != $requiredRole) {
        header("Location: ../home/login.php");
        exit();
    }
}
?>
