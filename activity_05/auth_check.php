<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['fullname'])) {
    header("Location: login.html");
    exit();
}
?>
