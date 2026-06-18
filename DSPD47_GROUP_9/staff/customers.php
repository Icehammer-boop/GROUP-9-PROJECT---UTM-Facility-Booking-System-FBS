<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireStaff();

$pageTitle = 'Member Management — FBS';

// Handle DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM member WHERE MemberID = ?");
    $stmt->execute([$id]);
    $_SESSION['flash'] = ['type' => 'success', 'msg' => "Member #$id deleted."];
    header('Location: /DSPD47_GROUP_9/staff/customers.php');
    exit;
}

// Handle UPDATE status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_member'])) {
    $id = intval($_POST['member_id']);
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $email = trim($_POST['email']);
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE member SET MemberName=?, MemberContactNo=?, MemberEmail=?, AccountStatus=? WHERE MemberID=?");
    $stmt->execute([$name, $contact, $email, $status, $id]);
    $_SESSION['flash'] = ['type' => 'success', 'msg' => "Member updated."];
    header('Location: /DSPD47_GROUP_9/staff/customers.php');
    exit;
}

// Handle ADD member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member'])) {
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM member WHERE MemberUsername = ? OR MemberEmail = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetchColumn() > 0) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Username or email already exists.'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO member (MemberName, MemberContactNo, MemberEmail, MemberUsername, MemberPassword) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $contact, $email, $username, $password]);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Member added successfully.'];
    }
    header('Location: /DSPD47_GROUP_9/staff/customers.php');
    exit;
}

// Search
$search = trim($_GET['search'] ?? '');
$sql = "
    SELECT c.*,
           COUNT(DISTINCT b.BookingID) as TotalBookings,
           COALESCE(SUM(p.PaymentAmount), 0) as TotalSpent
    FROM member c
    LEFT JOIN booking b ON c.MemberID = b.MemberID AND b.BookingStatus != 'Cancelled'
    LEFT JOIN payment p ON b.BookingID = p.BookingID AND p.PaymentStatus = 'Paid'
    WHERE 1=1
";
$params = [];
if ($search) {
    $sql .= " AND (c.MemberName LIKE ? OR c.MemberEmail LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " GROUP BY c.MemberID ORDER BY c.MemberName";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$members = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
$current = 'customers';
?>

<div class="page-wrapper">
  <div class="container">
    <div class="page-header">
      <h1 class="page-title">Member Management</h1>
      <p class="page-subtitle">View and manage registered members</p>
    </div>

    <div class="grid grid-3 gap-6">
      <!-- Left: Member List -->
      <div class="col-span-2">
        <div class="card">
          <div class="card-header">
            <form method="GET" class="search-bar mb-0" style="margin-bottom:0;flex:1;">
              <input type="text" name="search" class="form-input" placeholder="Search members..."
                     value="<?= htmlspecialchars($search) ?>">
              <button type="submit" class="btn btn-primary btn-sm">Search</button>
              <?php if ($search): ?>
                <a href="/DSPD47_GROUP_9/staff/customers.php" class="btn btn-ghost btn-sm">Clear</a>
              <?php endif; ?>
            </form>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Status</th>
                  <th>Bookings</th>
                  <th>Spent</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($members as $c): ?>
                <tr>
                  <td class="font-mono text-xs">#<?= $c['MemberID'] ?></td>
                  <td class="font-medium"><?= htmlspecialchars($c['MemberName']) ?></td>
                  <td class="text-sm"><?= htmlspecialchars($c['MemberEmail']) ?></td>
                  <td class="text-sm"><?= htmlspecialchars($c['MemberContactNo']) ?></td>
                  <td>
                    <span class="badge <?= $c['AccountStatus'] === 'Active' ? 'badge-available' : ($c['AccountStatus'] === 'Suspended' ? 'badge-unavailable' : 'badge-cancelled') ?>">
                      <?= $c['AccountStatus'] ?>
                    </span>
                  </td>
                  <td class="font-mono text-sm"><?= $c['TotalBookings'] ?></td>
                  <td class="font-mono text-sm">RM <?= number_format($c['TotalSpent'], 2) ?></td>
                  <td>
                    <div class="action-btns">
                      <button class="action-btn view" onclick="editMember(<?= $c['MemberID'] ?>, '<?= htmlspecialchars(addslashes($c['MemberName'])) ?>', '<?= htmlspecialchars(addslashes($c['MemberContactNo'])) ?>', '<?= htmlspecialchars(addslashes($c['MemberEmail'])) ?>', '<?= $c['AccountStatus'] ?>')">Edit</button>
                      <a href="/DSPD47_GROUP_9/staff/customers.php?delete=<?= $c['MemberID'] ?>" class="action-btn reject" data-confirm="Delete <?= htmlspecialchars($c['MemberName']) ?>? All their bookings will be deleted.">Delete</a>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Right: Add/Edit Form -->
      <div>
        <div class="card" style="position:sticky;top:calc(var(--header-h) + 2rem);">
          <div class="card-header">
            <h3 class="font-semibold" id="formTitle">Add Member</h3>
          </div>
          <div class="card-body">
            <form method="POST" id="memberForm">
              <input type="hidden" name="member_id" id="editMemberId" value="">
              <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" id="editName" class="form-input" required>
              </div>
              <div class="form-group">
                <label class="form-label">Contact No</label>
                <input type="text" name="contact" id="editContact" class="form-input" required>
              </div>
              <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" id="editEmail" class="form-input" required>
              </div>
              <div class="form-group" id="usernameGroup">
                <label class="form-label">Username</label>
                <input type="text" name="username" id="editUsername" class="form-input">
              </div>
              <div class="form-group" id="passwordGroup">
                <label class="form-label">Password</label>
                <input type="password" name="password" id="editPassword" class="form-input">
              </div>
              <div class="form-group" id="statusGroup" style="display:none;">
                <label class="form-label">Account Status</label>
                <select name="status" id="editStatus" class="form-select">
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                  <option value="Suspended">Suspended</option>
                </select>
              </div>
              <div class="flex gap-2">
                <button type="submit" name="add_member" id="addBtn" class="btn btn-primary btn-block">Add Member</button>
                <button type="submit" name="update_member" id="updateBtn" class="btn btn-primary btn-block" style="display:none;">Update</button>
                <button type="button" id="cancelBtn" class="btn btn-ghost" style="display:none;" onclick="resetForm()">Cancel</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
@media (max-width:768px) { .col-span-2 { grid-column: span 1; } }
</style>

<script>
function editMember(id, name, contact, email, status) {
  document.getElementById('formTitle').textContent = 'Edit Member';
  document.getElementById('editMemberId').value = id;
  document.getElementById('editName').value = name;
  document.getElementById('editContact').value = contact;
  document.getElementById('editEmail').value = email;
  document.getElementById('editStatus').value = status;
  document.getElementById('usernameGroup').style.display = 'none';
  document.getElementById('passwordGroup').style.display = 'none';
  document.getElementById('statusGroup').style.display = 'block';
  document.getElementById('addBtn').style.display = 'none';
  document.getElementById('updateBtn').style.display = 'block';
  document.getElementById('cancelBtn').style.display = 'block';
  document.getElementById('memberForm').scrollIntoView({ behavior: 'smooth' });
}
function resetForm() {
  document.getElementById('formTitle').textContent = 'Add Member';
  document.getElementById('memberForm').reset();
  document.getElementById('editMemberId').value = '';
  document.getElementById('usernameGroup').style.display = 'block';
  document.getElementById('passwordGroup').style.display = 'block';
  document.getElementById('statusGroup').style.display = 'none';
  document.getElementById('addBtn').style.display = 'block';
  document.getElementById('updateBtn').style.display = 'none';
  document.getElementById('cancelBtn').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
