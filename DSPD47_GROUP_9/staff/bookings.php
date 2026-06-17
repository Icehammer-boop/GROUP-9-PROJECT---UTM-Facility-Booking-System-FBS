<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireStaff();

$pageTitle = 'Booking Management — FBS';

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $bookingId = intval($_GET['id']);
    $action = $_GET['action'];
    $staffId = $_SESSION['user_id'];

    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE booking SET BookingStatus = 'Approved', StaffID = ? WHERE BookingID = ? AND BookingStatus = 'Pending'");
        $stmt->execute([$staffId, $bookingId]);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => "Booking #$bookingId approved."];
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE booking SET BookingStatus = 'Rejected', StaffID = ? WHERE BookingID = ? AND BookingStatus = 'Pending'");
        $stmt->execute([$staffId, $bookingId]);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => "Booking #$bookingId rejected."];
    } elseif ($action === 'complete') {
        $stmt = $pdo->prepare("UPDATE booking SET BookingStatus = 'Completed', StaffID = ? WHERE BookingID = ? AND BookingStatus = 'Approved'");
        $stmt->execute([$staffId, $bookingId]);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => "Booking #$bookingId marked as completed."];
    }
    header('Location: /DSPD47_GROUP_1/staff/bookings.php' . (isset($_GET['status']) ? '?status=' . $_GET['status'] : ''));
    exit;
}

// Filters
$statusFilter = $_GET['status'] ?? '';
$facilityFilter = intval($_GET['facility'] ?? 0);

// Get facilities for filter
$facilities = $pdo->query("SELECT FacilityID, FacilityName FROM facility ORDER BY FacilityName")->fetchAll();

// Build query
$sql = "
    SELECT b.*, m.MemberName, m.MemberEmail, f.FacilityName,
           p.PaymentStatus, p.ReceiptNumber
    FROM booking b
    JOIN member m ON b.MemberID = m.MemberID
    JOIN facility f ON b.FacilityID = f.FacilityID
    LEFT JOIN payment p ON b.BookingID = p.BookingID
    WHERE 1=1
";
$params = [];

if ($statusFilter && in_array($statusFilter, ['Pending', 'Approved', 'Rejected', 'Completed', 'Cancelled'])) {
    $sql .= " AND b.BookingStatus = ?";
    $params[] = $statusFilter;
}
if ($facilityFilter) {
    $sql .= " AND b.FacilityID = ?";
    $params[] = $facilityFilter;
}

$sql .= " ORDER BY b.CreatedDateTime DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
$current = 'bookings';
?>

<div class="page-wrapper">
  <div class="container">
    <div class="page-header">
      <h1 class="page-title">Booking Management</h1>
      <p class="page-subtitle">Review and manage all reservations</p>
    </div>

    <div class="filter-bar">
      <a href="/DSPD47_GROUP_1/staff/bookings.php" class="btn <?= !$statusFilter ? 'btn-dark' : 'btn-outline' ?> btn-sm">All</a>
      <?php foreach (['Pending', 'Approved', 'Completed', 'Cancelled', 'Rejected'] as $s): ?>
        <a href="/DSPD47_GROUP_1/staff/bookings.php?status=<?= $s ?>" class="btn <?= $statusFilter === $s ? 'btn-dark' : 'btn-outline' ?> btn-sm"><?= $s ?></a>
      <?php endforeach; ?>
      <select name="facility" class="form-select" style="width:auto;min-width:160px;" onchange="window.location='?facility='+this.value<?= $statusFilter ? "+'&status=$statusFilter'" : '' ?>">
        <option value="0">All Facilities</option>
        <?php foreach ($facilities as $f): ?>
          <option value="<?= $f['FacilityID'] ?>" <?= $facilityFilter == $f['FacilityID'] ? 'selected' : '' ?>><?= htmlspecialchars($f['FacilityName']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <?php if (empty($bookings)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">📅</div>
        <h3>No bookings found</h3>
        <p>No bookings match the current filter.</p>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Member</th>
              <th>Facility</th>
              <th>Date</th>
              <th>Time</th>
              <th>Purpose</th>
              <th>Status</th>
              <th>Payment</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($bookings as $b): ?>
            <tr>
              <td class="font-mono text-xs">#<?= $b['BookingID'] ?></td>
              <td class="font-medium"><?= htmlspecialchars($b['MemberName']) ?></td>
              <td><?= htmlspecialchars($b['FacilityName']) ?></td>
              <td><?= date('d M Y', strtotime($b['BookingDate'])) ?></td>
              <td><?= date('h:i A', strtotime($b['StartTime'])) ?> — <?= date('h:i A', strtotime($b['EndTime'])) ?></td>
              <td class="text-muted text-xs max-w-32" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($b['Purpose'] ?? '—') ?></td>
              <td><span class="badge badge-<?= strtolower($b['BookingStatus']) ?>"><?= $b['BookingStatus'] ?></span></td>
              <td>
                <?php if ($b['PaymentStatus'] === 'Paid'): ?>
                  <span class="badge badge-paid">Paid</span>
                <?php elseif ($b['PaymentStatus']): ?>
                  <span class="badge badge-<?= strtolower($b['PaymentStatus']) ?>"><?= $b['PaymentStatus'] ?></span>
                <?php else: ?>
                  <span class="text-muted text-xs">—</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="action-btns">
                  <?php if ($b['BookingStatus'] === 'Pending'): ?>
                    <a href="/DSPD47_GROUP_1/staff/bookings.php?action=approve&id=<?= $b['BookingID'] ?><?= $statusFilter ? '&status='.$statusFilter : '' ?>" class="action-btn approve" data-confirm="Approve booking #<?= $b['BookingID'] ?>?">Approve</a>
                    <a href="/DSPD47_GROUP_1/staff/bookings.php?action=reject&id=<?= $b['BookingID'] ?><?= $statusFilter ? '&status='.$statusFilter : '' ?>" class="action-btn reject" data-confirm="Reject booking #<?= $b['BookingID'] ?>?">Reject</a>
                  <?php endif; ?>
                  <?php if ($b['BookingStatus'] === 'Approved'): ?>
                    <a href="/DSPD47_GROUP_1/staff/bookings.php?action=complete&id=<?= $b['BookingID'] ?><?= $statusFilter ? '&status='.$statusFilter : '' ?>" class="action-btn approve" data-confirm="Mark booking #<?= $b['BookingID'] ?> as completed?">Complete</a>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
