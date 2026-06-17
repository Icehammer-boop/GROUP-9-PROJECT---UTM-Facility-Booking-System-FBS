<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireStaff();

$pageTitle = 'Payment Management — FBS';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment'])) {
    $paymentId = intval($_POST['payment_id']);
    $newStatus = $_POST['payment_status'];
    $receiptNumber = trim($_POST['receipt_number'] ?? '');

    if ($newStatus === 'Paid' && !$receiptNumber) {
        $receiptNumber = 'RCP-' . date('Ymd') . '-' . $paymentId;
    }

    $stmt = $pdo->prepare("UPDATE payment SET PaymentStatus = ?, ReceiptNumber = COALESCE(NULLIF(?, ''), ReceiptNumber), PaymentDate = CASE WHEN ? = 'Paid' THEN NOW() ELSE PaymentDate END WHERE PaymentID = ?");
    $stmt->execute([$newStatus, $receiptNumber, $newStatus, $paymentId]);
    $_SESSION['flash'] = ['type' => 'success', 'msg' => "Payment #$paymentId updated to $newStatus."];
    header('Location: /DSPD47_GROUP_1/staff/payment.php');
    exit;
}

// Handle create payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_payment'])) {
    $bookingId = intval($_POST['booking_id']);
    $amount = floatval($_POST['amount']);
    $method = $_POST['payment_method'];

    $stmt = $pdo->prepare("INSERT INTO payment (PaymentAmount, PaymentMethod, PaymentStatus, BookingID) VALUES (?, ?, 'Pending', ?)");
    $stmt->execute([$amount, $method, $bookingId]);
    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Payment record created.'];
    header('Location: /DSPD47_GROUP_1/staff/payment.php');
    exit;
}

// Filter
$statusFilter = $_GET['status'] ?? '';

$sql = "
    SELECT p.*, b.BookingDate, b.BookingStatus,
           m.MemberName, f.FacilityName
    FROM payment p
    JOIN booking b ON p.BookingID = b.BookingID
    JOIN member m ON b.MemberID = m.MemberID
    JOIN facility f ON b.FacilityID = f.FacilityID
    WHERE 1=1
";
$params = [];
if ($statusFilter && in_array($statusFilter, ['Pending', 'Paid', 'Failed', 'Refunded'])) {
    $sql .= " AND p.PaymentStatus = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY p.PaymentDate DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$payments = $stmt->fetchAll();

// Stats
$totalPaid = $pdo->query("SELECT COALESCE(SUM(PaymentAmount), 0) FROM payment WHERE PaymentStatus = 'Paid'")->fetchColumn();
$totalPending = $pdo->query("SELECT COALESCE(SUM(PaymentAmount), 0) FROM payment WHERE PaymentStatus = 'Pending'")->fetchColumn();

// Bookings without payments
$unpaidBookings = $pdo->query("
    SELECT b.BookingID, b.BookingDate, m.MemberName, f.FacilityName, f.RatePerHour,
           (TIMESTAMPDIFF(HOUR, b.StartTime, b.EndTime)) as Hours
    FROM booking b
    JOIN member m ON b.MemberID = m.MemberID
    JOIN facility f ON b.FacilityID = f.FacilityID
    LEFT JOIN payment p ON b.BookingID = p.BookingID
    WHERE p.PaymentID IS NULL AND b.BookingStatus NOT IN ('Cancelled', 'Rejected')
    ORDER BY b.BookingDate DESC
    LIMIT 10
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
$current = 'payment';
?>

<div class="page-wrapper">
  <div class="container">
    <div class="page-header">
      <h1 class="page-title">Payment Management</h1>
      <p class="page-subtitle">Track and manage all payments</p>
    </div>

    <div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr));">
      <div class="stat-card">
        <div class="stat-label">Total Paid</div>
        <div class="stat-value text-accent" style="font-size:1.5rem">RM <?= number_format($totalPaid, 2) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Total Pending</div>
        <div class="stat-value" style="color:var(--warning);font-size:1.5rem">RM <?= number_format($totalPending, 2) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Unpaid Bookings</div>
        <div class="stat-value"><?= count($unpaidBookings) ?></div>
      </div>
    </div>

    <!-- Unpaid Bookings -->
    <?php if (!empty($unpaidBookings)): ?>
    <div class="card mb-8">
      <div class="card-header">
        <h3 class="font-semibold">Bookings Without Payment Records</h3>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Booking ID</th><th>Member</th><th>Facility</th><th>Date</th><th>Amount</th><th>Create Payment</th></tr>
          </thead>
          <tbody>
            <?php foreach ($unpaidBookings as $ub): ?>
            <tr>
              <td class="font-mono text-xs">#<?= $ub['BookingID'] ?></td>
              <td><?= htmlspecialchars($ub['MemberName']) ?></td>
              <td><?= htmlspecialchars($ub['FacilityName']) ?></td>
              <td><?= date('d M Y', strtotime($ub['BookingDate'])) ?></td>
              <td class="font-mono">RM <?= number_format($ub['Hours'] * $ub['RatePerHour'], 2) ?></td>
              <td>
                <form method="POST" class="flex gap-2">
                  <input type="hidden" name="booking_id" value="<?= $ub['BookingID'] ?>">
                  <input type="hidden" name="amount" value="<?= $ub['Hours'] * $ub['RatePerHour'] ?>">
                  <select name="payment_method" class="form-select" style="width:auto;">
                    <option value="Cash">Cash</option>
                    <option value="Online Banking">Online Banking</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="Debit Card">Debit Card</option>
                    <option value="E-Wallet">E-Wallet</option>
                  </select>
                  <button type="submit" name="create_payment" class="btn btn-sm btn-primary">Create</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- Filter -->
    <div class="filter-bar">
      <a href="/DSPD47_GROUP_1/staff/payment.php" class="btn <?= !$statusFilter ? 'btn-dark' : 'btn-outline' ?> btn-sm">All</a>
      <?php foreach (['Pending', 'Paid', 'Failed', 'Refunded'] as $s): ?>
        <a href="/DSPD47_GROUP_1/staff/payment.php?status=<?= $s ?>" class="btn <?= $statusFilter === $s ? 'btn-dark' : 'btn-outline' ?> btn-sm"><?= $s ?></a>
      <?php endforeach; ?>
    </div>

    <!-- Payments Table -->
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Payment ID</th>
              <th>Booking</th>
              <th>Member</th>
              <th>Facility</th>
              <th>Amount</th>
              <th>Method</th>
              <th>Status</th>
              <th>Receipt</th>
              <th>Date</th>
              <th>Update</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($payments as $p): ?>
            <tr>
              <td class="font-mono text-xs">#<?= $p['PaymentID'] ?></td>
              <td class="font-mono text-xs">#<?= $p['BookingID'] ?></td>
              <td class="font-medium"><?= htmlspecialchars($p['MemberName']) ?></td>
              <td><?= htmlspecialchars($p['FacilityName']) ?></td>
              <td class="font-mono font-bold">RM <?= number_format($p['PaymentAmount'], 2) ?></td>
              <td><?= htmlspecialchars($p['PaymentMethod']) ?></td>
              <td><span class="badge badge-<?= strtolower($p['PaymentStatus']) ?>"><?= $p['PaymentStatus'] ?></span></td>
              <td class="font-mono text-xs"><?= htmlspecialchars($p['ReceiptNumber'] ?? '—') ?></td>
              <td class="text-sm"><?= $p['PaymentDate'] ? date('d M Y', strtotime($p['PaymentDate'])) : '—' ?></td>
              <td>
                <form method="POST" class="flex gap-1">
                  <input type="hidden" name="payment_id" value="<?= $p['PaymentID'] ?>">
                  <select name="payment_status" class="form-select" style="width:auto;padding:0.25rem 0.5rem;font-size:0.75rem;">
                    <?php foreach (['Pending', 'Paid', 'Failed', 'Refunded'] as $s): ?>
                      <option value="<?= $s ?>" <?= $p['PaymentStatus'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button type="submit" name="update_payment" class="btn btn-sm btn-outline">Update</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
