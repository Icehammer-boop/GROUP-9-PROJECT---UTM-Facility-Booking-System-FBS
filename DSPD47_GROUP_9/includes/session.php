<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isMember() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'member';
}

function isStaff() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'staff';
}

function isAdmin() {
    return isStaff() && isset($_SESSION['staff_role']) && $_SESSION['staff_role'] === 'Admin';
}

function isManager() {
    return isStaff() && isset($_SESSION['staff_role']) && $_SESSION['staff_role'] === 'Manager';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /DSPD47_GROUP_9/auth/login.php');
        exit;
    }
}

function requireMember() {
    requireLogin();
    if (!isMember()) {
        header('Location: /DSPD47_GROUP_9/staff/dashboard.php');
        exit;
    }
}

function requireStaff() {
    requireLogin();
    if (!isStaff()) {
        header('Location: /DSPD47_GROUP_9/member/dashboard.php');
        exit;
    }
}

function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        if (isMember()) {
            header('Location: /DSPD47_GROUP_9/member/dashboard.php');
        } else {
            header('Location: /DSPD47_GROUP_9/staff/dashboard.php');
        }
        exit;
    }
}
