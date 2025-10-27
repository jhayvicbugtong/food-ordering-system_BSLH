<?php
session_start();
session_destroy();
header("Location: /food-ordering-system_BSLH/auth/login.php");
exit();
?>
