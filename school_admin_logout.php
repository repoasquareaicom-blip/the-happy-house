<?php
session_start();
session_unset();
session_destroy();
header("Location: school_admin_login.php");
exit();
?>