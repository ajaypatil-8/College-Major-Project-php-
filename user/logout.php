<?php
session_start();

$_SESSION = [];

session_destroy();


header("Location: /CroudSpark-X/public/index.php");
exit;
?>
