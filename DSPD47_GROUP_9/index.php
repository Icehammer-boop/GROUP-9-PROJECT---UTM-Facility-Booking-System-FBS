<?php
require_once __DIR__ . '/includes/session.php';
if (isLoggedIn()) {
    if (isMember()) {
        header('Location: /DSPD47_GROUP_9/member/dashboard.php');
    } else {
        header('Location: /DSPD47_GROUP_9/staff/dashboard.php');
    }
    exit;
}
header('Location: /DSPD47_GROUP_9/auth/login.php');
exit;
