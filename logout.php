<?php
// logout.php - Handle user logout
session_start();
session_destroy();
header('Location: /~mznaien/login.php?logout=1');
exit();
?>