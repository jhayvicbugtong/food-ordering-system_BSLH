<?php
session_start();

function require_login() {
  if (!isset($_SESSION['user_id'])) {
    header("Location: /food-ordering-system_BSLH/auth/login.php");
    exit();
  }
}

function require_role($role) {
  require_login();
  if ($_SESSION['role'] !== $role) {
    header("HTTP/1.1 403 Forbidden");
    echo "403 Forbidden — Access denied.";
    exit();
  }
}
?>
