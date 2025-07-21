<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit();
}

$role = $_SESSION['user']['role'];
if ($role == 'manager') {
    header("Location: manager/dashboard.php");
} else {
    header("Location: cashier/checkout.php");
}
exit();
