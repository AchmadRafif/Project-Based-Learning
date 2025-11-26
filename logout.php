<?php
session_start();

// Destroy semua session variables
$_SESSION = array();

// Destroy session cookie jika ada
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy session
session_destroy();

// Redirect ke halaman login
header("Location: index.php");
exit;
?>