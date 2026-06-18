<?php
require_once __DIR__ . '/../includes/session.php';
session_destroy();
header('Location: /DSPD47_GROUP_9/auth/login.php');
exit;
