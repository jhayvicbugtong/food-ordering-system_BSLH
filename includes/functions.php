<?php
function sanitize($str) {
  return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}
?>
