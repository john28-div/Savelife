<?php
require_once 'includes/auth.php';
$_SESSION = [];
session_destroy();
header('Location: ' . BASE_URL . '/login.php');
exit;
