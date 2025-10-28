<?php
// logout.php

// Start session to destroy it
session_start();
session_unset();  // Unset all session variables
session_destroy(); // Destroy session

// Redirect the user to the login page
header('Location: /food-ordering-system_BSLH/auth/login.php');
exit();
?>
