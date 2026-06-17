<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireStaff();

$pageTitle = 'Staff Dashboard — FBS';

// Stats
$totalBookings = $pdo->query("SELECT COUNT(*) FROM booking")->fetchColumn();
$pendingBookings = $pdo->query("SELECT COUNT(*) FROM booking WHERE BookingStatus = 'Pending'")->fetchColumn();
$todayApproved = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE BookingStatus = 'Approved' AND BookingDate = CURDATE()");
$todayApproved->execute();
$todayApprovedCount = $todayApproved->fetchColumn();
$totalMembers = $pdo->query("SELECT COUNT(*) FROM member")->fetchColumn();
$totalFacilities = $pdo->query("SELECT COUNT(*) FROM facility")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(PaymentAmount), 0) FROM payment WHERE PaymentStatus = 'Paid'")->fetchColumn();

// Recent bookings
$recentBookings = $pdo->query("
    SELECT b.BookingID, b.BookingDate, b.StartTime, b.EndTime, b.BookingStatus,
           m.MemberName, f.FacilityName
    FROM booking b
    JOIN member m ON b.MemberID = m.MemberID
    JOIN facility f ON b.FacilityID = f.FacilityID
    ORDER BY b.CreatedDateTime DESC
    LIMIT 10
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
$current = 'dashboard';
?>

<div class="page-wrapper">
  <div class="container">
    <div class="page-header">
      <h1 class="page-title">Staff Dashboard</h1>
      <p class="page-subtitle">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?> · <?= htmlspecialchars($_SESSION['staff_role']) ?></p>
    </div>

    <div class="stats-grid">
      <div class="stat-card animate-in">
        <div class="stat-label">Total Bookings</div>
        <div class="stat-value"><?= $totalBookings ?></div>
      </div>
      <div class="stat-card animate-in delay-1">
        <div class="stat-label">Pending Approval</div>
        <div class="stat-value" style="color:var(--warning)"><?= $pendingBookings ?></div>
      </div>
      <div class="stat-card animate-in delay-2">
        <div class="stat-label">Approved Today</div>
        <div class="stat-value text-accent"><?= $todayApprovedCount ?></div>
      </div>
      <div class="stat-card animate-in delay-3">
        <div class="stat-label">Total Members</div>
        <div class="stat-value"><?= $totalMembers ?></div>
      </div>
      <div class="stat-card animate-in delay-4">
        <div class="stat-label">Facilities</div>
        <div class="stat-value"><?= $totalFacilities ?></div>
      </div>
      <div class="stat-card animate-in delay-4">
        <div class="stat-label">Revenue (Paid)</div>
        <div class="stat-value text-accent" style="font-size:1.5rem">RM <?= number_format($totalRevenue, 2) ?></div>
      </div>
    </div>

    <div class="card animate-in delay-3">
      <div class="card-header">
        <h2 class="font-semibold">Recent Bookings</h2>
        <a href="/DSPD47_GROUP_1/staff/bookings.php" class="btn btn-outline btn-sm">View All</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Member</th>
              <th>Facility</th>
              <th>Date</th>
              <th>Time</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentBookings as $b): ?>
            <tr>
              <td class="font-mono text-xs">#<?= $b['BookingID'] ?></td>
              <td class="font-medium"><?= htmlspecialchars($b['MemberName']) ?></td>
              <td><?= htmlspecialchars($b['FacilityName']) ?></td>
              <td><?= date('d M Y', strtotime($b['BookingDate'])) ?></td>
              <td><?= date('h:i A', strtotime($b['StartTime'])) ?> — <?= date('h:i A', strtotime($b['EndTime'])) ?></td>
              <td><span class="badge badge-<?= strtolower($b['BookingStatus']) ?>"><?= $b['BookingStatus'] ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
