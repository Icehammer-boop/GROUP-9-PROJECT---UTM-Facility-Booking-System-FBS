<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireMember();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /DSPD47_GROUP_1/member/facilities.php');
    exit;
}

$facilityId = intval($_POST['facility_id'] ?? 0);
$bookingDate = $_POST['booking_date'] ?? '';
$startTime = $_POST['start_time'] ?? '';
$endTime = $_POST['end_time'] ?? '';
$purpose = trim($_POST['purpose'] ?? '');
$memberId = $_SESSION['user_id'];

// Validation
if (!$facilityId || !$bookingDate || !$startTime || !$endTime || !$purpose) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Please fill in all booking fields.'];
    header('Location: /DSPD47_GROUP_1/member/facility_detail.php?id=' . $facilityId);
    exit;
}

if ($bookingDate < date('Y-m-d')) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Booking date cannot be in the past.'];
    header('Location: /DSPD47_GROUP_1/member/facility_detail.php?id=' . $facilityId);
    exit;
}

if ($endTime <= $startTime) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'End time must be after start time.'];
    header('Location: /DSPD47_GROUP_1/member/facility_detail.php?id=' . $facilityId);
    exit;
}

// Check facility exists and is available
$stmt = $pdo->prepare("SELECT * FROM facility WHERE FacilityID = ? AND FacilityStatus = 'Available'");
$stmt->execute([$facilityId]);
$facility = $stmt->fetch();

if (!$facility) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'This facility is not available for booking.'];
    header('Location: /DSPD47_GROUP_1/member/facilities.php');
    exit;
}

// Check for overlapping bookings
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM booking
    WHERE FacilityID = ? AND BookingDate = ?
    AND BookingStatus NOT IN ('Cancelled', 'Rejected')
    AND (
        (StartTime < ? AND EndTime > ?) OR
        (StartTime < ? AND EndTime > ?) OR
        (StartTime >= ? AND EndTime <= ?)
    )
");
$stmt->execute([$facilityId, $bookingDate, $endTime, $startTime, $endTime, $startTime, $startTime, $endTime]);
if ($stmt->fetchColumn() > 0) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'This time slot is already booked. Please choose a different time.'];
    header('Location: /DSPD47_GROUP_1/member/facility_detail.php?id=' . $facilityId);
    exit;
}

try {
    $pdo->beginTransaction();

    // Insert booking
    $stmt = $pdo->prepare("
        INSERT INTO booking (BookingDate, StartTime, EndTime, BookingStatus, Purpose, MemberID, FacilityID)
        VALUES (?, ?, ?, 'Pending', ?, ?, ?)
    ");
    $stmt->execute([$bookingDate, $startTime, $endTime, $purpose, $memberId, $facilityId]);
    $bookingId = $pdo->lastInsertId();

    // Calculate amount
    $hours = (strtotime($endTime) - strtotime($startTime)) / 3600;
    $amount = $hours * $facility['RatePerHour'];

    // Insert payment
    $stmt = $pdo->prepare("
        INSERT INTO payment (PaymentAmount, PaymentMethod, PaymentStatus, BookingID)
        VALUES (?, 'Cash', 'Pending', ?)
    ");
    $stmt->execute([$amount, $bookingId]);

    $pdo->commit();

    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Booking submitted successfully! Your booking is pending approval.'];
    header('Location: /DSPD47_GROUP_1/member/mybookings.php');
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'An error occurred. Please try again.'];
    header('Location: /DSPD47_GROUP_1/member/facility_detail.php?id=' . $facilityId);
    exit;
}
