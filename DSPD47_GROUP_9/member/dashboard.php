<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireMember();

$pageTitle = 'Dashboard — FBS';
$memberId = $_SESSION['user_id'];

// Stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE MemberID = ?");
$stmt->execute([$memberId]);
$totalBookings = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE MemberID = ? AND BookingStatus = 'Pending'");
$stmt->execute([$memberId]);
$pendingBookings = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE MemberID = ? AND BookingStatus = 'Approved'");
$stmt->execute([$memberId]);
$approvedBookings = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE MemberID = ? AND BookingStatus = 'Completed'");
$stmt->execute([$memberId]);
$completedBookings = $stmt->fetchColumn();

// Recent bookings
$stmt = $pdo->prepare("
    SELECT b.BookingID, b.BookingDate, b.StartTime, b.EndTime, b.BookingStatus, b.Purpose,
           f.FacilityName
    FROM booking b
    JOIN facility f ON b.FacilityID = f.FacilityID
    WHERE b.MemberID = ?
    ORDER BY b.CreatedDateTime DESC
    LIMIT 5
");
$stmt->execute([$memberId]);
$recentBookings = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-wrapper">
  <div class="container">
    <div class="page-header">
      <h1 class="page-title">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
      <p class="page-subtitle">Here's an overview of your facility bookings</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card animate-in">
        <div class="stat-label">Total Bookings</div>
        <div class="stat-value"><?= $totalBookings ?></div>
      </div>
      <div class="stat-card animate-in delay-1">
        <div class="stat-label">Pending</div>
        <div class="stat-value" style="color:var(--warning)"><?= $pendingBookings ?></div>
      </div>
      <div class="stat-card animate-in delay-2">
        <div class="stat-label">Approved</div>
        <div class="stat-value text-accent"><?= $approvedBookings ?></div>
      </div>
      <div class="stat-card animate-in delay-3">
        <div class="stat-label">Completed</div>
        <div class="stat-value" style="color:#1e40af"><?= $completedBookings ?></div>
      </div>
    </div>

    <div class="card animate-in delay-4">
      <div class="card-header">
        <h2 class="font-semibold">Recent Bookings</h2>
        <a href="/DSPD47_GROUP_1/member/facilities.php" class="btn btn-primary btn-sm">Browse Facilities</a>
      </div>
      <?php if (empty($recentBookings)): ?>
        <div class="empty-state">
          <div class="empty-state-icon">📅</div>
          <h3>No bookings yet</h3>
          <p>Start by browsing available facilities and make your first booking.</p>
          <a href="/DSPD47_GROUP_1/member/facilities.php" class="btn btn-primary mt-4">Browse Facilities</a>
        </div>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Facility</th>
                <th>Date</th>
                <th>Time</th>
                <th>Purpose</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentBookings as $b): ?>
              <tr>
                <td class="font-medium"><?= htmlspecialchars($b['FacilityName']) ?></td>
                <td><?= date('d M Y', strtotime($b['BookingDate'])) ?></td>
                <td><?= date('h:i A', strtotime($b['StartTime'])) ?> — <?= date('h:i A', strtotime($b['EndTime'])) ?></td>
                <td class="text-muted"><?= htmlspecialchars($b['Purpose'] ?? '—') ?></td>
                <td><span class="badge badge-<?= strtolower($b['BookingStatus']) ?>"><?= $b['BookingStatus'] ?></span></td>
                <td>
                  <div class="action-btns">
                    <a href="/DSPD47_GROUP_1/member/facility_detail.php?id=<?= $b['BookingID'] ?>" class="action-btn view">View</a>
                    <?php if ($b['BookingStatus'] === 'Pending'): ?>
                      <a href="/DSPD47_GROUP_1/member/mybookings.php?cancel=<?= $b['BookingID'] ?>" class="action-btn reject" data-confirm="Cancel this booking?">Cancel</a>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
