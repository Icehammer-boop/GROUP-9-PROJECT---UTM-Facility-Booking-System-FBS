<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireMember();

$pageTitle = 'Receipt — FBS';
$memberId = $_SESSION['user_id'];

$paymentId = intval($_GET['payment_id'] ?? 0);
if (!$paymentId) {
    header('Location: /DSPD47_GROUP_1/member/mybookings.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, b.BookingID, b.BookingDate, b.StartTime, b.EndTime, b.Purpose, b.BookingStatus,
           f.FacilityName, f.RatePerHour,
           c.MemberName, c.MemberEmail, c.MemberContactNo
    FROM payment p
    JOIN booking b ON p.BookingID = b.BookingID
    JOIN facility f ON b.FacilityID = f.FacilityID
    JOIN member c ON b.MemberID = c.MemberID
    WHERE p.PaymentID = ? AND b.MemberID = ?
");
$stmt->execute([$paymentId, $memberId]);
$receipt = $stmt->fetch();

if (!$receipt) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Receipt not found.'];
    header('Location: /DSPD47_GROUP_1/member/mybookings.php');
    exit;
}

$hours = (strtotime($receipt['EndTime']) - strtotime($receipt['StartTime'])) / 3600;

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-wrapper">
  <div class="container">
    <div class="page-header flex justify-between items-center">
      <div>
        <h1 class="page-title">Receipt</h1>
        <p class="page-subtitle">Booking #<?= $receipt['BookingID'] ?></p>
      </div>
      <button onclick="window.print()" class="btn btn-outline">🖨️ Print</button>
    </div>

    <div class="card" style="max-width:640px;margin:0 auto;">
      <div class="card-body p-8">
        <!-- Header -->
        <div class="text-center mb-8">
          <div class="logo justify-center mb-2">
            <span class="logo-dot"></span>
            FBS
          </div>
          <h2 class="text-xl font-bold">Facility Booking System</h2>
          <p class="text-muted text-sm">Official Receipt</p>
        </div>

        <div class="flex justify-between mb-6 text-sm">
          <div>
            <p class="text-muted">Receipt No.</p>
            <p class="font-mono font-bold"><?= htmlspecialchars($receipt['ReceiptNumber']) ?></p>
          </div>
          <div class="text-right">
            <p class="text-muted">Payment Date</p>
            <p class="font-mono"><?= $receipt['PaymentDateTime'] ? date('d M Y, h:i A', strtotime($receipt['PaymentDateTime'])) : '—' ?></p>
          </div>
        </div>

        <div class="card-inner mb-6">
          <h4 class="font-semibold mb-2 text-sm">Member</h4>
          <p class="font-medium"><?= htmlspecialchars($receipt['MemberName']) ?></p>
          <p class="text-muted text-sm"><?= htmlspecialchars($receipt['MemberEmail']) ?> · <?= htmlspecialchars($receipt['MemberContactNo']) ?></p>
        </div>

        <table class="mb-6">
          <thead>
            <tr>
              <th>Description</th>
              <th class="text-right">Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <span class="font-medium"><?= htmlspecialchars($receipt['FacilityName']) ?></span>
                <br><span class="text-muted text-xs"><?= date('d M Y', strtotime($receipt['BookingDate'])) ?> · <?= date('h:i A', strtotime($receipt['StartTime'])) ?> — <?= date('h:i A', strtotime($receipt['EndTime'])) ?></span>
                <br><span class="text-muted text-xs"><?= htmlspecialchars($receipt['Purpose']) ?></span>
              </td>
              <td class="text-right font-mono">RM <?= number_format($receipt['PaymentAmount'], 2) ?></td>
            </tr>
          </tbody>
          <tfoot>
            <tr style="border-top:2px solid var(--text);">
              <td class="font-bold pt-3">Total</td>
              <td class="text-right font-mono font-bold text-lg pt-3 text-accent">RM <?= number_format($receipt['PaymentAmount'], 2) ?></td>
            </tr>
          </tfoot>
        </table>

        <div class="flex justify-between text-sm">
          <div>
            <p class="text-muted">Payment Method</p>
            <p class="font-medium"><?= htmlspecialchars($receipt['PaymentMethod']) ?></p>
          </div>
          <div class="text-right">
            <p class="text-muted">Status</p>
            <span class="badge badge-<?= strtolower($receipt['PaymentStatus']) ?>"><?= $receipt['PaymentStatus'] ?></span>
          </div>
        </div>

        <div class="text-center mt-8 pt-6" style="border-top:1px dashed var(--border);">
          <p class="text-muted text-xs">Thank you for using Facility Booking System</p>
          <p class="text-muted text-xs font-mono mt-1">DSPD1703 Project 2026</p>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
@media print {
  .site-header, .site-footer, .btn { display: none !important; }
  .page-wrapper { padding: 0; }
  .card { box-shadow: none; border: 1px solid #ddd; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
