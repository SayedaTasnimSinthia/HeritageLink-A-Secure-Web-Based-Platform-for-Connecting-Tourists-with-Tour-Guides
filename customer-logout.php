<?php
session_start();
unset($_SESSION['customer_logged_in']);
unset($_SESSION['customer_id']);
unset($_SESSION['customer_name']);
unset($_SESSION['customer_email']);

header("Location: customer-login.php");
exit;
?>