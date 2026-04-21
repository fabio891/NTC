<?php
/**
 * FlowIn - Logout
 */
session_start();
session_destroy();
header('Location: Regist.php');
exit;
?>
