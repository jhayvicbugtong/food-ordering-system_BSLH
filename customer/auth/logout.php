<?php
// customer/auth/logout.php
session_start();

/*
  Blow away the customer session.
  (Safe even if the session is already empty.)
*/
// --- MODIFIED: Unset new session keys ---
$_SESSION['user_id'] = null;
$_SESSION['name']    = null;
$_SESSION['email']   = null;
$_SESSION['role']    = null;

session_unset();
session_destroy();

/*
  Redirect target:
  - If ?next= is provided, go there.
  - Otherwise, compute the site base and send them to /index.php
    (works whether the project folder is /food-ordering-system_BSLH or something else)
*/
// --- MODIFIED: Use new base URL logic ---
$base = rtrim(dirname(dirname(dirname($_SERVER['PHP_SELF']))), '/'); // up 3 dirs from /customer/auth/
if ($base === '/') $base = '';
$default = $base . '/index.php';
$to = isset($_GET['next']) && $_GET['next'] !== '' ? $_GET['next'] : $default;

header('Location: ' . $to);
exit;