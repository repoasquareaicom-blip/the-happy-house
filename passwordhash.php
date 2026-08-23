<?php
$hashed_password = password_hash($_GET['pass'], PASSWORD_BCRYPT);
echo $hashed_password;
?>