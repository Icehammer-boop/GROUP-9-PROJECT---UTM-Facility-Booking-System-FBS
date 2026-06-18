<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireMember();

$pageTitle = 'Edit Profile — FBS';
$memberId = $_SESSION['user_id'];
$errors = [];
$success = '';

// Fetch current member data
$stmt = $pdo->prepare("SELECT * FROM member WHERE MemberID = ?");
$stmt->execute([$memberId]);
$member = $stmt->fetch();

if (!$member) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Member not found.'];
    header('Location: /DSPD47_GROUP_9/member/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['account_name'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name)) {
        $errors[] = 'Full name is required.';
    }
    if (empty($contact)) {
        $errors[] = 'Contact number is required.';
    }
    if (empty($email)) {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (empty($username)) {
        $errors[] = 'Username is required.';
    }

    // Check duplicate username/email (exclude self)
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM member WHERE (MemberUsername = ? OR MemberEmail = ?) AND MemberID != ?");
        $stmt->execute([$username, $email, $memberId]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Username or email already taken by another account.';
        }
    }

    // Password change logic
    $updatePassword = false;
    if (!empty($newPassword) || !empty($currentPassword)) {
        if (empty($currentPassword)) {
            $errors[] = 'Current password is required to change password.';
        } elseif (!password_verify($currentPassword, $member['MemberPassword'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'New passwords do not match.';
        } else {
            $updatePassword = true;
        }
    }

    if (empty($errors)) {
        if ($updatePassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE member SET MemberName = ?, MemberContactNo = ?, MemberEmail = ?, MemberUsername = ?, MemberPassword = ? WHERE MemberID = ?");
            $stmt->execute([$name, $contact, $email, $username, $hashedPassword, $memberId]);
        } else {
            $stmt = $pdo->prepare("UPDATE member SET MemberName = ?, MemberContactNo = ?, MemberEmail = ?, MemberUsername = ? WHERE MemberID = ?");
            $stmt->execute([$name, $contact, $email, $username, $memberId]);
        }

        // Update session
        $_SESSION['user_name'] = $name;

        $success = 'Profile updated successfully.';

        // Re-fetch updated data so form shows fresh values
        $stmt = $pdo->prepare("SELECT * FROM member WHERE MemberID = ?");
        $stmt->execute([$memberId]);
        $member = $stmt->fetch();
    }
}

require_once __DIR__ . '/../includes/header.php';
$current = 'edit';
?>

<div class="page-wrapper">
  <div class="container">
    <div class="page-header">
      <h1 class="page-title">Edit Profile</h1>
      <p class="page-subtitle">Update your personal information and account settings</p>
    </div>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error" style="flex-direction:column;align-items:flex-start;gap:0.25rem;">
        <?php foreach ($errors as $err): ?>
          <div><?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="detail-grid">
      <!-- Profile Form -->
      <div class="card">
        <div class="card-header">
          <h3 class="font-semibold">Personal Information</h3>
        </div>
        <div class="card-body">
          <form method="POST" action="" autocomplete="off">
            <div class="form-group">
              <label class="form-label">Full Name</label>
              <input type="text" name="name" class="form-input" placeholder="Enter your full name"
                     value="<?= htmlspecialchars($name ?? $member['MemberName']) ?>" required autocomplete="off">
            </div>

            <div class="form-group">
              <label class="form-label">Contact Number</label>
              <input type="text" name="contact" class="form-input" placeholder="+60 12-345 6789"
                     value="<?= htmlspecialchars($contact ?? $member['MemberContactNo']) ?>" required autocomplete="off">
            </div>

            <div class="form-group">
              <label class="form-label">Email Address</label>
              <input type="email" name="email" class="form-input" placeholder="you@example.com"
                     value="<?= htmlspecialchars($email ?? $member['MemberEmail']) ?>" required autocomplete="off">
            </div>

            <div class="form-group">
              <label class="form-label">Username</label>
              <input type="text" name="account_name" id="account_name" class="form-input" placeholder="Choose a username"
                     value="" required autocomplete="off">
            </div>

            <button type="submit" class="btn btn-primary mt-4">Save Changes</button>
          </form>
        </div>
      </div>

      <!-- Password Change -->
      <div>
        <div class="card mb-4">
          <div class="card-header">
            <h3 class="font-semibold">Change Password</h3>
          </div>
          <div class="card-body">
            <form method="POST" action="" autocomplete="off">
              <!-- Preserve profile fields -->
              <input type="hidden" name="name" value="<?= htmlspecialchars($name ?? $member['MemberName']) ?>">
              <input type="hidden" name="contact" value="<?= htmlspecialchars($contact ?? $member['MemberContactNo']) ?>">
              <input type="hidden" name="email" value="<?= htmlspecialchars($email ?? $member['MemberEmail']) ?>">
              <input type="hidden" name="account_name" value="<?= htmlspecialchars($username ?? $member['MemberUsername']) ?>">

              <div class="form-group">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-input" placeholder="Enter current password" autocomplete="current-password">
                <p class="form-hint">Required only if changing password</p>
              </div>

              <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-input" placeholder="At least 6 characters" autocomplete="new-password">
                <p class="form-hint">Minimum 6 characters</p>
              </div>

              <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-input" placeholder="Re-enter new password" autocomplete="new-password">
              </div>

              <button type="submit" class="btn btn-dark">Update Password</button>
            </form>
          </div>
        </div>

        <!-- Account Info -->
        <div class="card-inner">
          <h4 class="text-sm font-semibold mb-2 text-muted text-xs uppercase tracking-wider">Account Details</h4>
          <div class="detail-info-row">
            <span class="detail-info-label">Member ID</span>
            <span class="font-mono text-sm">#<?= $member['MemberID'] ?></span>
          </div>
          <div class="detail-info-row">
            <span class="detail-info-label">Status</span>
            <span class="badge badge-available"><?= htmlspecialchars($member['AccountStatus']) ?></span>
          </div>
          <div class="detail-info-row">
            <span class="detail-info-label">Password</span>
            <span class="text-muted text-sm">••••••••</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Prevent browser autofill: fill username via JS after load
(function() {
  var usernameField = document.getElementById('account_name');
  if (usernameField) {
    usernameField.value = '<?= htmlspecialchars($username ?? $member['MemberUsername'], ENT_QUOTES) ?>';
  }
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
