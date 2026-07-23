<?php
require_once __DIR__ . '/auth.php'; require_login();
header('Location: receipt_list.php');
exit;
?>
