<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireMember();

$pageTitle = 'My Bookings — FBS';
$memberId = $_SESSION['user_id'];

// Handle cancel
if (isset($_GET['cancel'])) {
    $bookingId = intval($_GET['cancel']);
    // Verify ownership and pending status
    $stmt = $pdo->prepare("SELECT * FROM booking WHERE BookingID = ? AND MemberID = ? AND BookingStatus = 'Pending'");
    $stmt->execute([$bookingId, $memberId]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE booking SET BookingStatus = 'Cancelled' WHERE BookingID = ?");
        $stmt->execute([$bookingId]);
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Booking #'.$bookingId.' has been cancelled.'];
    }
    header('Location: /DSPD47_GROUP_1/member/mybookings.php');
    exit;
}

// Filter
$statusFilter = $_GET['status'] ?? '';

$sql = "
    SELECT b.*, f.FacilityName, f.RatePerHour,
           p.PaymentStatus, p.PaymentID, p.PaymentMethod, p.ReceiptNumber
    FROM booking b
    JOIN facility f ON b.FacilityID = f.FacilityID
    LEFT JOIN payment p ON b.BookingID = p.BookingID
    WHERE b.MemberID = ?
";
$params = [$memberId];

if ($statusFilter && in_array($statusFilter, ['Pending', 'Approved', 'Rejected', 'Completed', 'Cancelled'])) {
    $sql .= " AND b.BookingStatus = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY b.CreatedDateTime DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-wrapper">
  <div class="container">
    <div class="page-header">
      <h1 class="page-title">My Bookings</h1>
      <p class="page-subtitle">Track and manage your reservations</p>
    </div>

    <div class="filter-bar">
      <a href="/DSPD47_GROUP_1/member/mybookings.php" class="btn <?= !$statusFilter ? 'btn-dark' : 'btn-outline' ?> btn-sm">All</a>
      <?php foreach (['Pending', 'Approved', 'Completed', 'Cancelled', 'Rejected'] as $s): ?>
        <a href="/DSPD47_GROUP_1/member/mybookings.php?status=<?= $s ?>" class="btn <?= $statusFilter === $s ? 'btn-dark' : 'btn-outline' ?> btn-sm"><?= $s ?></a>
      <?php endforeach; ?>
      <div style="flex:1"></div>
      <a href="/DSPD47_GROUP_1/member/facilities.php" class="btn btn-primary btn-sm">+ New Booking</a>
    </div>

    <?php if (empty($bookings)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">📅</div>
        <h3>No bookings found</h3>
        <p><?= $statusFilter ? "You don't have any $statusFilter bookings." : "You haven't made any bookings yet." ?></p>
        <a href="/DSPD47_GROUP_1/member/facilities.php" class="btn btn-primary mt-4">Browse Facilities</a>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Facility</th>
              <th>Date</th>
              <th>Time</th>
              <th>Purpose</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Payment</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($bookings as $b): ?>
            <tr>
              <td class="font-mono text-xs">#<?= $b['BookingID'] ?></td>
              <td class="font-medium"><?= htmlspecialchars($b['FacilityName']) ?></td>
              <td><?= date('d M Y', strtotime($b['BookingDate'])) ?></td>
              <td><?= date('h:i A', strtotime($b['StartTime'])) ?> — <?= date('h:i A', strtotime($b['EndTime'])) ?></td>
              <td class="text-muted text-xs"><?= htmlspecialchars($b['Purpose'] ?? '—') ?></td>
              <td class="font-mono text-sm">
                <?php
                $hours = (strtotime($b['EndTime']) - strtotime($b['StartTime'])) / 3600;
                $amount = $hours * $b['RatePerHour'];
                echo 'RM ' . number_format($amount, 2);
                ?>
              </td>
              <td><span class="badge badge-<?= strtolower($b['BookingStatus']) ?>"><?= $b['BookingStatus'] ?></span></td>
              <td>
                <?php if ($b['PaymentStatus'] === 'Paid'): ?>
                  <span class="badge badge-paid">Paid</span>
                <?php elseif ($b['PaymentStatus'] === 'Pending' || $b['PaymentStatus'] === null): ?>
                  <?php if ($b['BookingStatus'] === 'Approved' || $b['BookingStatus'] === 'Completed'): ?>
                    <a href="/DSPD47_GROUP_1/member/payment.php?booking_id=<?= $b['BookingID'] ?>" class="btn btn-sm btn-primary">Pay</a>
                  <?php else: ?>
                    <span class="text-muted text-xs">—</span>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="badge badge-<?= strtolower($b['PaymentStatus']) ?>"><?= $b['PaymentStatus'] ?></span>
                <?php endif; ?>
              </td>
              <td>
                <div class="action-btns">
                  <?php if ($b['BookingStatus'] === 'Pending'): ?>
                    <a href="/DSPD47_GROUP_1/member/mybookings.php?cancel=<?= $b['BookingID'] ?>" class="action-btn reject" data-confirm="Cancel booking #<?= $b['BookingID'] ?>?">Cancel</a>
                  <?php endif; ?>
                  <?php if ($b['PaymentStatus'] === 'Paid' && $b['ReceiptNumber']): ?>
                    <a href="/DSPD47_GROUP_1/member/receipt.php?payment_id=<?= $b['PaymentID'] ?>" class="action-btn view">Receipt</a>
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
