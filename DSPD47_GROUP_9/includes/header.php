<?php
require_once __DIR__ . '/session.php';
$current = basename($_SERVER['PHP_SELF'], '.php');

// Flash message handling
$flash = null;
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?>Facility Booking System</title>
  <link rel="stylesheet" href="/DSPD47_GROUP_1/assets/css/style.css">
</head>
<body>
<header class="site-header">
  <div class="container">
    <a href="/DSPD47_GROUP_1/" class="logo">
      <span class="logo-dot"></span>
      FBS
    </a>

    <?php if (isLoggedIn()): ?>
    <nav class="nav-links" id="navLinks">
      <?php if (isMember()): ?>
        <a href="/DSPD47_GROUP_1/member/dashboard.php" class="<?= $current === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
        <a href="/DSPD47_GROUP_1/member/facilities.php" class="<?= $current === 'facilities' ? 'active' : '' ?>">Facilities</a>
        <a href="/DSPD47_GROUP_1/member/mybookings.php" class="<?= $current === 'mybookings' ? 'active' : '' ?>">My Bookings</a>
      <?php elseif (isStaff()): ?>
        <a href="/DSPD47_GROUP_1/staff/dashboard.php" class="<?= $current === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
        <a href="/DSPD47_GROUP_1/staff/bookings.php" class="<?= $current === 'bookings' ? 'active' : '' ?>">Bookings</a>
        <a href="/DSPD47_GROUP_1/staff/facilities.php" class="<?= $current === 'facilities' ? 'active' : '' ?>">Facilities</a>
        <a href="/DSPD47_GROUP_1/staff/customers.php" class="<?= $current === 'customers' ? 'active' : '' ?>">Members</a>
      <?php endif; ?>
    </nav>
    <div class="nav-user">
      <?php if (isMember()): ?>
        <a href="/DSPD47_GROUP_1/member/edit.php" class="nav-avatar" title="Edit Profile"><?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?></a>
      <?php elseif (isStaff()): ?>
        <a href="/DSPD47_GROUP_1/staff/edit.php" class="nav-avatar" title="Edit Profile"><?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?></a>
      <?php else: ?>
        <div class="nav-avatar"><?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?></div>
      <?php endif; ?>
      <a href="/DSPD47_GROUP_1/auth/logout.php" class="btn btn-ghost btn-sm">Logout</a>
    </div>
    <button class="mobile-menu-btn" onclick="document.getElementById('navLinks').classList.toggle('open')">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <?php else: ?>
    <nav class="nav-links">
      <a href="/DSPD47_GROUP_1/auth/login.php">Login</a>
      <a href="/DSPD47_GROUP_1/auth/register.php" class="btn btn-primary btn-sm">Register</a>
    </nav>
    <?php endif; ?>
  </div>
</header>

<?php if ($flash): ?>
<div class="container" style="padding-top:1rem;">
  <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" id="flashAlert">
    <?= htmlspecialchars($flash['msg']) ?>
  </div>
</div>
<?php endif; ?>
