<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
redirectIfLoggedIn();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($contact) || empty($email) || empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check if username or email exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM member WHERE MemberUsername = ? OR MemberEmail = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Username or email already exists.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO member (MemberName, MemberContactNo, MemberEmail, MemberUsername, MemberPassword) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $contact, $email, $username, $hashedPassword]);
            $success = 'Registration successful! You can now log in.';
        }
    }
}

$pageTitle = 'Register — FBS';
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
  <div class="auth-card animate-in" >
    <a href="/DSPD47_GROUP_9/" class="logo">
      <span class="logo-dot"></span>
      UTM Facility Booking System
    </a>
    <h1 class="auth-title">Create account</h1>
    <p class="auth-subtitle">Register as a member to start booking facilities</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success">
        <?= htmlspecialchars($success) ?>
        <a href="/DSPD47_GROUP_9/auth/login.php" class="text-accent font-medium" style="margin-left:0.5rem;">Sign in →</a>
      </div>
    <?php endif; ?>

    <form method="POST" action="" autocomplete="off">
      <!-- Fake fields to trick browser autofill -->
      <div style="position:absolute;opacity:0;pointer-events:none;height:0;overflow:hidden;">
        <input type="text" name="fake_user" tabindex="-1" aria-hidden="true">
        <input type="password" name="fake_pass" tabindex="-1" aria-hidden="true">
        <input type="email" name="fake_email" tabindex="-1" aria-hidden="true">
      </div>
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-input" placeholder="Enter your full name"
               value="<?= htmlspecialchars($name ?? '') ?>" required autocomplete="name">
      </div>

      <div class="form-group">
        <label class="form-label">Contact Number</label>
        <input type="text" name="contact" class="form-input" placeholder="+60 12-345 6789"
               value="<?= htmlspecialchars($contact ?? '') ?>" required autocomplete="tel">
      </div>

      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-input" placeholder="you@example.com"
               value="<?= htmlspecialchars($email ?? '') ?>" required autocomplete="email">
      </div>

      <div class="form-group">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-input" placeholder="Choose a username"
               value="" required autocomplete="username">
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-input" placeholder="At least 6 characters" required autocomplete="new-password">
        <p class="form-hint">Minimum 6 characters</p>
      </div>

      <div class="form-group">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="confirm_password" class="form-input" placeholder="Re-enter password" required autocomplete="new-password">
      </div>

      <button type="submit" class="btn btn-primary btn-lg btn-block mt-4">Create Account</button>
    </form>

    <p class="text-center mt-6 text-muted text-sm">
      Already have an account? <a href="/DSPD47_GROUP_9/auth/login.php" class="text-accent font-medium">Sign in</a>
    </p>
  </div>
</div>
<script src="/DSPD47_GROUP_9/assets/js/main.js"></script>
</body>
</html>
