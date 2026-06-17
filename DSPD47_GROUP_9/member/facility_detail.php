<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireMember();

$pageTitle = 'Facility Details — FBS';

$facilityId = intval($_GET['id'] ?? 0);
if (!$facilityId) {
    header('Location: /DSPD47_GROUP_1/member/facilities.php');
    exit;
}

// Get facility
$stmt = $pdo->prepare("SELECT * FROM facility WHERE FacilityID = ?");
$stmt->execute([$facilityId]);
$facility = $stmt->fetch();

if (!$facility) {
    header('Location: /DSPD47_GROUP_1/member/facilities.php');
    exit;
}

// Get schedule
$stmt = $pdo->prepare("SELECT * FROM facility_schedule WHERE FacilityID = ? ORDER BY AvailableTime_Start");
$stmt->execute([$facilityId]);
$schedules = $stmt->fetchAll();

// Get member's existing bookings for this facility
$memberId = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT b.*, p.PaymentStatus, p.PaymentID
    FROM booking b
    LEFT JOIN payment p ON b.BookingID = p.BookingID
    WHERE b.MemberID = ? AND b.FacilityID = ?
    ORDER BY b.BookingDate DESC
");
$stmt->execute([$memberId, $facilityId]);
$myFacilityBookings = $stmt->fetchAll();


require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-wrapper">
  <div class="container">
    <div class="page-header">
      <a href="/DSPD47_GROUP_1/member/facilities.php" class="btn btn-ghost btn-sm mb-4">← Back to Facilities</a>
    </div>

    <div class="detail-grid">
      <div>
        <div class="detail-hero">
          <span class="facility-card-icon"><?= htmlspecialchars($facility['FacilityCategory'][0]) ?></span>
        </div>

        <h1 class="page-title"><?= htmlspecialchars($facility['FacilityName']) ?></h1>
        <p class="text-muted mb-4"><?= htmlspecialchars($facility['FacilityCategory']) ?></p>

        <div class="card mb-6">
          <div class="card-body">
            <h3 class="font-semibold mb-4">Facility Information</h3>
            <div class="detail-info-row">
              <span class="detail-info-label">Category</span>
              <span><?= htmlspecialchars($facility['FacilityCategory']) ?></span>
            </div>
            <div class="detail-info-row">
              <span class="detail-info-label">Capacity</span>
              <span><?= $facility['FacilityCapacity'] ?> people</span>
            </div>
            <div class="detail-info-row">
              <span class="detail-info-label">Rate</span>
              <span class="font-mono font-bold text-accent">RM <?= number_format($facility['RatePerHour'], 2) ?> / hour</span>
            </div>
            <div class="detail-info-row">
              <span class="detail-info-label">Status</span>
              <span class="badge badge-<?= strtolower($facility['FacilityStatus']) ?>"><?= $facility['FacilityStatus'] ?></span>
            </div>
            <div class="detail-info-row" style="border:none">
              <span class="detail-info-label">Description</span>
            </div>
            <p class="text-muted text-sm"><?= htmlspecialchars($facility['FacilityDetail'] ?? 'No description available.') ?></p>
          </div>
        </div>

        <?php if (!empty($schedules)): ?>
        <div class="card mb-6">
          <div class="card-header">
            <h3 class="font-semibold">Available Schedule</h3>
          </div>
          <div class="card-body">
            <div class="table-wrap">
              <table>
                <thead>
                  <tr><th>Time Slot</th><th>Status</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($schedules as $s): ?>
                  <tr>
                    <td><?= date('h:i A', strtotime($s['AvailableTime_Start'])) ?> — <?= date('h:i A', strtotime($s['AvailableTime_End'])) ?></td>
                    <td><span class="badge badge-<?= $s['ScheduleStatus'] === 'Available' ? 'available' : ($s['ScheduleStatus'] === 'Booked' ? 'unavailable' : 'maintenance') ?>"><?= $s['ScheduleStatus'] ?></span></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($myFacilityBookings)): ?>
        <div class="card">
          <div class="card-header">
            <h3 class="font-semibold">My Bookings for This Facility</h3>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr><th>Date</th><th>Time</th><th>Status</th><th>Payment</th></tr>
              </thead>
              <tbody>
                <?php foreach ($myFacilityBookings as $b): ?>
                <tr>
                  <td><?= date('d M Y', strtotime($b['BookingDate'])) ?></td>
                  <td><?= date('h:i A', strtotime($b['StartTime'])) ?> — <?= date('h:i A', strtotime($b['EndTime'])) ?></td>
                  <td><span class="badge badge-<?= strtolower($b['BookingStatus']) ?>"><?= $b['BookingStatus'] ?></span></td>
                  <td>
                    <?php if ($b['PaymentStatus'] === 'Paid'): ?>
                      <span class="badge badge-paid">Paid</span>
                    <?php elseif ($b['PaymentStatus'] === 'Pending'): ?>
                      <a href="/DSPD47_GROUP_1/member/payment.php?booking_id=<?= $b['BookingID'] ?>" class="btn btn-sm btn-primary">Pay Now</a>
                    <?php else: ?>
                      <span class="badge badge-<?= strtolower($b['PaymentStatus'] ?? 'pending') ?>"><?= $b['PaymentStatus'] ?? 'Pending' ?></span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <div>
        <div class="card" style="position:sticky;top:calc(var(--header-h) + 2rem);">
          <div class="card-header">
            <h3 class="font-semibold">Book This Facility</h3>
          </div>
          <div class="card-body">
            <?php if ($facility['FacilityStatus'] !== 'Available'): ?>
              <div class="alert alert-warning">This facility is currently not available for booking.</div>
            <?php else: ?>
              <form method="POST" action="/DSPD47_GROUP_1/member/booking.php">
                <input type="hidden" name="facility_id" value="<?= $facilityId ?>">

                <div class="form-group">
                  <label class="form-label">Booking Date</label>
                  <input type="date" name="booking_date" class="form-input" required>
                </div>

                <div class="form-group">
                  <label class="form-label">Start Time</label>
                  <input type="time" name="start_time" class="form-input" required>
                </div>

                <div class="form-group">
                  <label class="form-label">End Time</label>
                  <input type="time" name="end_time" class="form-input" required>
                </div>

                <div class="form-group">
                  <label class="form-label">Purpose</label>
                  <textarea name="purpose" class="form-textarea" placeholder="Describe the purpose of your booking..." required></textarea>
                </div>

                <div class="card-inner mb-4">
                  <div class="flex justify-between text-sm">
                    <span class="text-muted">Rate</span>
                    <span class="font-mono font-bold">RM <?= number_format($facility['RatePerHour'], 2) ?> / hr</span>
                  </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">Submit Booking</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
