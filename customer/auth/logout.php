<?php
// customer/auth/logout.php
require_once __DIR__ . '/../../includes/db_connect.php'; // Provides $BASE_URL and starts session

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
$default = $BASE_URL . '/index.php'; // FIXED
$to = isset($_GET['next']) && $_GET['next'] !== '' ? $_GET['next'] : $default;

header('Location: ' . $to);
exit;