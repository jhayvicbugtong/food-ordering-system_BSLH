<?php
// customer/auth/logout.php
session_start();

/*
  Blow away the customer session.
  (Safe even if the session is already empty.)
*/
$_SESSION['customer_id']    = null;
$_SESSION['customer_name']  = null;
$_SESSION['customer_email'] = null;
$_SESSION['customer_role']  = null;

session_unset();
session_destroy();

/*
  Redirect target:
  - If ?next= is provided, go there.
  - Otherwise, compute the site base and send them to /index.php
    (works whether the project folder is /food-ordering-system_BSLH or something else)
*/
$base = rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/'); // up 2 dirs from /customer/auth/
$default = $base . '/index.php';
$to = isset($_GET['next']) && $_GET['next'] !== '' ? $_GET['next'] : $default;

header('Location: ' . $to);
exit;
