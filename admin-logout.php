<?php
session_start();
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_user']);
session_destroy();
header("Location: admin-login.php");
exit;
?>