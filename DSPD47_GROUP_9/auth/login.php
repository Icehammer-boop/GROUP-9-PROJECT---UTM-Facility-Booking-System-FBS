<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
redirectIfLoggedIn();

$error = '';
$redirect = $_GET['redirect'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'member';

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        if ($role === 'member') {
            $stmt = $pdo->prepare("SELECT * FROM member WHERE MemberUsername = ? AND AccountStatus = 'Active'");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['MemberPassword'])) {
                $_SESSION['user_id'] = $user['MemberID'];
                $_SESSION['user_name'] = $user['MemberName'];
                $_SESSION['user_type'] = 'member';
                header('Location: /DSPD47_GROUP_9/member/dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $stmt = $pdo->prepare("SELECT * FROM staff WHERE StaffUsername = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['StaffPassword'])) {
                $_SESSION['user_id'] = $user['StaffID'];
                $_SESSION['user_name'] = $user['StaffName'];
                $_SESSION['user_type'] = 'staff';
                $_SESSION['staff_role'] = $user['StaffRole'];
                header('Location: /DSPD47_GROUP_9/staff/dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        }
    }
}

$pageTitle = 'Login — FBS';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <link rel="stylesheet" href="/DSPD47_GROUP_9/assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card animate-in">
    <a href="/DSPD47_GROUP_9/" class="logo">
      <span class="logo-dot"></span>
      UTM Facility Booking System
    </a>
    <h1 class="auth-title">Welcome back</h1>
    <p class="auth-subtitle">Sign in to your Facility Booking System account</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="" autocomplete="off">
      <!-- Fake fields to trick browser autofill -->
      <div style="position:absolute;opacity:0;pointer-events:none;">
        <input type="text" name="fake_username" tabindex="-1">
        <input type="password" name="fake_password" tabindex="-1">
      </div>
      <div class="form-group">
        <label class="form-label">Account Type</label>
        <select name="role" class="form-select" autocomplete="off">
          <option value="member">Member</option>
          <option value="staff">Staff</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-input" placeholder="Enter your username"
               value="" required autocomplete="username">
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-input" placeholder="Enter your password" required autocomplete="current-password">
      </div>

      <button type="submit" class="btn btn-primary btn-lg btn-block mt-4">Sign In</button>
    </form>

    <p class="text-center mt-6 text-muted text-sm">
      Don't have an account? <a href="/auth/register.php" class="text-accent font-medium">Register</a>
    </p>
  </div>
</div>
<script src="/assets/js/main.js"></script>
</body>
</html>
