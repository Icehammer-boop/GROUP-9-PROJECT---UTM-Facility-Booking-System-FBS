<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireMember();

$pageTitle = 'Payment — FBS';
$memberId = $_SESSION['user_id'];

$bookingId = intval($_GET['booking_id'] ?? ($_POST['booking_id'] ?? 0));
if (!$bookingId) {
    header('Location: /DSPD47_GROUP_9/member/mybookings.php');
    exit;
}

// Verify booking belongs to member
$stmt = $pdo->prepare("
    SELECT b.*, f.FacilityName, f.RatePerHour,
           p.PaymentID, p.PaymentAmount, p.PaymentMethod, p.PaymentStatus, p.ReceiptNumber
    FROM booking b
    JOIN facility f ON b.FacilityID = f.FacilityID
    LEFT JOIN payment p ON b.BookingID = p.BookingID
    WHERE b.BookingID = ? AND b.MemberID = ?
");
$stmt->execute([$bookingId, $memberId]);
$booking = $stmt->fetch();

if (!$booking) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Booking not found.'];
    header('Location: /DSPD47_GROUP_9/member/mybookings.php');
    exit;
}

// Process payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $booking['PaymentStatus'] !== 'Paid') {
    $method = $_POST['payment_method'] ?? 'Cash';

    if ($booking['PaymentID']) {
        // Update existing payment
        $receiptNumber = 'RCP-' . date('Ymd') . '-' . $booking['PaymentID'];
        $stmt = $pdo->prepare("
            UPDATE payment SET PaymentMethod = ?, PaymentStatus = 'Paid', PaymentDateTime = NOW(), ReceiptNumber = ?
            WHERE PaymentID = ?
        ");
        $stmt->execute([$method, $receiptNumber, $booking['PaymentID']]);
    } else {
        $hours = (strtotime($booking['EndTime']) - strtotime($booking['StartTime'])) / 3600;
        $amount = $hours * $booking['RatePerHour'];
        $receiptNumber = 'RCP-' . date('Ymd') . '-NEW';
        $stmt = $pdo->prepare("
            INSERT INTO payment (PaymentAmount, PaymentMethod, PaymentStatus, ReceiptNumber, BookingID)
            VALUES (?, ?, 'Paid', ?, ?)
        ");
        $stmt->execute([$amount, $method, $receiptNumber, $bookingId]);
    }

    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Payment successful! Receipt: ' . $receiptNumber];
    header('Location: /DSPD47_GROUP_9/member/mybookings.php');
    exit;
}

$hours = (strtotime($booking['EndTime']) - strtotime($booking['StartTime'])) / 3600;
$amount = $hours * $booking['RatePerHour'];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-wrapper">
  <div class="container">
    <div class="page-header">
      <h1 class="page-title">Payment</h1>
      <p class="page-subtitle">Complete payment for your booking</p>
    </div>

    <div class="detail-grid">
      <div class="card">
        <div class="card-header">
          <h3 class="font-semibold">Booking Details</h3>
        </div>
        <div class="card-body">
          <div class="detail-info-row">
            <span class="detail-info-label">Booking ID</span>
            <span class="font-mono">#<?= $booking['BookingID'] ?></span>
          </div>
          <div class="detail-info-row">
            <span class="detail-info-label">Facility</span>
            <span class="font-medium"><?= htmlspecialchars($booking['FacilityName']) ?></span>
          </div>
          <div class="detail-info-row">
            <span class="detail-info-label">Date</span>
            <span><?= date('d M Y', strtotime($booking['BookingDate'])) ?></span>
          </div>
          <div class="detail-info-row">
            <span class="detail-info-label">Time</span>
            <span><?= date('h:i A', strtotime($booking['StartTime'])) ?> — <?= date('h:i A', strtotime($booking['EndTime'])) ?></span>
          </div>
          <div class="detail-info-row">
            <span class="detail-info-label">Duration</span>
            <span><?= number_format($hours, 1) ?> hour(s)</span>
          </div>
          <div class="detail-info-row">
            <span class="detail-info-label">Booking Status</span>
            <span class="badge badge-<?= strtolower($booking['BookingStatus']) ?>"><?= $booking['BookingStatus'] ?></span>
          </div>
        </div>
      </div>

      <div>
        <div class="card mb-6">
          <div class="card-header">
            <h3 class="font-semibold">Amount Due</h3>
          </div>
          <div class="card-body">
            <div class="flex justify-between items-center mb-4">
              <span class="text-muted">Rate</span>
              <span>RM <?= number_format($booking['RatePerHour'], 2) ?> × <?= number_format($hours, 1) ?> hr</span>
            </div>
            <div class="flex justify-between items-center" style="padding-top:1rem;border-top:2px solid var(--border);">
              <span class="font-semibold text-lg">Total</span>
              <span class="font-mono font-bold text-2xl text-accent">RM <?= number_format($amount, 2) ?></span>
            </div>
          </div>
        </div>

        <?php if ($booking['PaymentStatus'] === 'Paid'): ?>
          <div class="alert alert-success">
            ✅ Payment completed — Receipt: <strong class="font-mono"><?= htmlspecialchars($booking['ReceiptNumber']) ?></strong>
            <a href="/DSPD47_GROUP_9/member/receipt.php?payment_id=<?= $booking['PaymentID'] ?>" class="btn btn-sm btn-outline mt-2">View Receipt</a>
          </div>
        <?php else: ?>
          <div class="card">
            <div class="card-header">
              <h3 class="font-semibold">Select Payment Method</h3>
            </div>
            <div class="card-body">
              <form method="POST">
                <input type="hidden" name="booking_id" value="<?= $bookingId ?>">
                <div class="payment-methods mb-4">
                  <?php foreach (['Cash', 'Online Banking', 'Credit Card', 'Debit Card', 'E-Wallet'] as $m): ?>
                  <label class="payment-method" onclick="document.querySelectorAll('.payment-method').forEach(e=>e.classList.remove('selected'));this.classList.add('selected')">
                    <input type="radio" name="payment_method" value="<?= $m ?>" style="display:none;" <?= $m === 'Cash' ? 'checked' : '' ?>>
                    <div class="font-medium"><?= $m ?></div>
                  </label>
                  <?php endforeach; ?>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block" data-confirm="Confirm payment of RM <?= number_format($amount, 2) ?>?">Pay RM <?= number_format($amount, 2) ?></button>
              </form>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
