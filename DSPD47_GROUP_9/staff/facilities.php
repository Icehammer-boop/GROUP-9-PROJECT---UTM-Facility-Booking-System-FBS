<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireStaff();

$pageTitle = 'Facility Management — FBS';
$actionMsg = '';

// Handle DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM facility WHERE FacilityID = ?");
    $stmt->execute([$id]);
    $_SESSION['flash'] = ['type' => 'success', 'msg' => "Facility #$id deleted."];
    header('Location: /DSPD47_GROUP_1/staff/facilities.php');
    exit;
}

// Handle UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_facility'])) {
    $id = intval($_POST['facility_id']);
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $capacity = intval($_POST['capacity']);
    $detail = trim($_POST['detail']);
    $rate = floatval($_POST['rate']);
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE facility SET FacilityName=?, FacilityCategory=?, FacilityCapacity=?, FacilityDetail=?, RatePerHour=?, FacilityStatus=? WHERE FacilityID=?");
    $stmt->execute([$name, $category, $capacity, $detail, $rate, $status, $id]);
    $_SESSION['flash'] = ['type' => 'success', 'msg' => "Facility updated successfully."];
    header('Location: /DSPD47_GROUP_1/staff/facilities.php');
    exit;
}

// Handle CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_facility'])) {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $capacity = intval($_POST['capacity']);
    $detail = trim($_POST['detail']);
    $rate = floatval($_POST['rate']);
    $status = $_POST['status'];

    $stmt = $pdo->prepare("INSERT INTO facility (FacilityName, FacilityCategory, FacilityCapacity, FacilityDetail, RatePerHour, FacilityStatus) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $category, $capacity, $detail, $rate, $status]);
    $newId = $pdo->lastInsertId();

    // Handle schedule additions
    if (isset($_POST['sched_start']) && is_array($_POST['sched_start'])) {
        foreach ($_POST['sched_start'] as $i => $start) {
            $end = $_POST['sched_end'][$i] ?? '';
            if ($start && $end) {
                $stmt = $pdo->prepare("INSERT INTO facility_schedule (AvailableTime_Start, AvailableTime_End, ScheduleStatus, FacilityID) VALUES (?, ?, 'Available', ?)");
                $stmt->execute([$start, $end, $newId]);
            }
        }
    }

    $_SESSION['flash'] = ['type' => 'success', 'msg' => "Facility added successfully."];
    header('Location: /DSPD47_GROUP_1/staff/facilities.php');
    exit;
}

// Search and filter
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');

// Get distinct categories for filter
$categories = $pdo->query("SELECT DISTINCT FacilityCategory FROM facility ORDER BY FacilityCategory")->fetchAll(PDO::FETCH_COLUMN);

// Build query
$sql = "
    SELECT f.*, COUNT(s.ScheduleID) as ScheduleCount
    FROM facility f
    LEFT JOIN facility_schedule s ON f.FacilityID = s.FacilityID
    WHERE 1=1
";
$params = [];

if ($search) {
    $sql .= " AND f.FacilityName LIKE ?";
    $params[] = "%$search%";
}
if ($statusFilter) {
    $sql .= " AND f.FacilityStatus = ?";
    $params[] = $statusFilter;
}
if ($categoryFilter) {
    $sql .= " AND f.FacilityCategory = ?";
    $params[] = $categoryFilter;
}

$sql .= " GROUP BY f.FacilityID ORDER BY f.FacilityCategory, f.FacilityName";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$facilities = $stmt->fetchAll();

// Get schedules for each facility
$allSchedules = [];
foreach ($facilities as $f) {
    $stmt = $pdo->prepare("SELECT * FROM facility_schedule WHERE FacilityID = ? ORDER BY AvailableTime_Start");
    $stmt->execute([$f['FacilityID']]);
    $allSchedules[$f['FacilityID']] = $stmt->fetchAll();
}

require_once __DIR__ . '/../includes/header.php';
$current = 'facilities';
?>

<div class="page-wrapper">
  <div class="container">
    <div class="page-header">
      <h1 class="page-title">Facility Management</h1>
      <p class="page-subtitle">Add, edit, and manage facilities</p>
    </div>

    <!-- Add Facility Form -->
    <div class="card mb-8">
      <div class="card-header">
        <h3 class="font-semibold">Add New Facility</h3>
      </div>
      <div class="card-body">
        <form method="POST">
          <div class="grid grid-2 gap-4">
            <div class="form-group mb-0">
              <label class="form-label">Facility Name</label>
              <input type="text" name="name" class="form-input" placeholder="e.g. Grand Badminton Court A" required>
            </div>
            <div class="form-group mb-0">
              <label class="form-label">Category</label>
              <input type="text" name="category" class="form-input" placeholder="e.g. Badminton" required>
            </div>
            <div class="form-group mb-0">
              <label class="form-label">Capacity (people)</label>
              <input type="number" name="capacity" class="form-input" min="1" required>
            </div>
            <div class="form-group mb-0">
              <label class="form-label">Rate Per Hour (RM)</label>
              <input type="number" name="rate" class="form-input" step="0.01" min="0" required>
            </div>
            <div class="form-group mb-0">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="Available">Available</option>
                <option value="Unavailable">Unavailable</option>
                <option value="Maintenance">Maintenance</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="detail" class="form-textarea" placeholder="Facility description..."></textarea>
          </div>

          <h4 class="font-semibold mt-4 mb-2">Schedule Slots (Optional)</h4>
          <div id="schedules">
            <div class="flex gap-2 mb-2">
              <input type="time" name="sched_start[]" class="form-input" style="width:auto;">
              <span class="flex items-center text-muted">to</span>
              <input type="time" name="sched_end[]" class="form-input" style="width:auto;">
            </div>
          </div>
          <button type="button" class="btn btn-ghost btn-sm mb-4" onclick="document.getElementById('schedules').insertAdjacentHTML('beforeend','<div class=\'flex gap-2 mb-2\'><input type=\'time\' name=\'sched_start[]\' class=\'form-input\' style=\'width:auto;\'><span class=\'flex items-center text-muted\'>to</span><input type=\'time\' name=\'sched_end[]\' class=\'form-input\' style=\'width:auto;\'></div>')">+ Add Time Slot</button>
          <br>
          <button type="submit" name="add_facility" class="btn btn-primary">Add Facility</button>
        </form>
      </div>
    </div>

    <!-- Existing Facilities -->
    <h3 class="font-semibold mb-4">All Facilities (<?= count($facilities) ?>)</h3>

        <!-- Search & Filter Bar -->
    <div class="card mb-6">
      <div class="card-body">
        <form method="GET" class="search-bar">
          <input type="text" name="search" class="form-input" placeholder="Search facilities by name..."
                 value="<?= htmlspecialchars($search) ?>">
          <select name="category" class="form-select" style="width:auto;min-width:160px;">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat) ?>" <?= $categoryFilter === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
          </select>
          <select name="status" class="form-select" style="width:auto;min-width:160px;">
            <option value="">All Statuses</option>
            <?php foreach (['Available', 'Unavailable', 'Maintenance'] as $sf): ?>
              <option value="<?= $sf ?>" <?= $statusFilter === $sf ? 'selected' : '' ?>><?= $sf ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-primary">Search</button>
          <?php if ($search || $statusFilter || $categoryFilter): ?>
            <a href="/DSPD47_GROUP_1/staff/facilities.php" class="btn btn-ghost">Clear</a>
          <?php endif; ?>
        </form>
      </div>
    </div>

    <?php if (empty($facilities)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">?</div>
        <h3>No facilities found</h3>
        <p>Try adjusting your search or filter criteria.</p>
      </div>
    <?php endif; ?>

    <?php foreach ($facilities as $f): ?>
    <div class="card mb-4">
      <div class="card-header">
        <div>
          <h4 class="font-semibold"><?= htmlspecialchars($f['FacilityName']) ?></h4>
          <p class="text-muted text-sm"><?= htmlspecialchars($f['FacilityCategory']) ?> · <?= $f['ScheduleCount'] ?> schedule slots</p>
        </div>
        <div class="flex gap-2">
          <span class="badge badge-<?= strtolower($f['FacilityStatus']) ?>"><?= $f['FacilityStatus'] ?></span>
          <a href="/DSPD47_GROUP_1/staff/facilities.php?delete=<?= $f['FacilityID'] ?>" class="btn btn-sm btn-danger" data-confirm="Delete <?= htmlspecialchars($f['FacilityName']) ?>? This will also delete all schedules and bookings.">Delete</a>
        </div>
      </div>
      <div class="card-body">
        <div class="grid grid-2 gap-6">
          <!-- Edit Form -->
          <div>
            <h5 class="text-sm font-semibold mb-3 text-muted text-xs uppercase tracking-wider">Edit Details</h5>
            <form method="POST">
              <input type="hidden" name="facility_id" value="<?= $f['FacilityID'] ?>">
              <div class="grid grid-2 gap-2">
                <div class="form-group mb-0">
                  <label class="form-label">Name</label>
                  <input type="text" name="name" class="form-input form-sm" value="<?= htmlspecialchars($f['FacilityName']) ?>">
                </div>
                <div class="form-group mb-0">
                  <label class="form-label">Category</label>
                  <input type="text" name="category" class="form-input form-sm" value="<?= htmlspecialchars($f['FacilityCategory']) ?>">
                </div>
                <div class="form-group mb-0">
                  <label class="form-label">Capacity</label>
                  <input type="number" name="capacity" class="form-input form-sm" value="<?= $f['FacilityCapacity'] ?>">
                </div>
                <div class="form-group mb-0">
                  <label class="form-label">Rate (RM/hr)</label>
                  <input type="number" name="rate" class="form-input form-sm" step="0.01" value="<?= $f['RatePerHour'] ?>">
                </div>
                <div class="form-group mb-0">
                  <label class="form-label">Status</label>
                  <select name="status" class="form-select form-sm">
                    <?php foreach (['Available', 'Unavailable', 'Maintenance'] as $s): ?>
                      <option value="<?= $s ?>" <?= $f['FacilityStatus'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="form-group mt-2">
                <label class="form-label">Description</label>
                <textarea name="detail" class="form-textarea" rows="3"><?= htmlspecialchars($f['FacilityDetail'] ?? '') ?></textarea>
              </div>
              <button type="submit" name="update_facility" class="btn btn-primary btn-sm">Save Changes</button>
            </form>
          </div>

          <!-- Schedule -->
          <div>
            <h5 class="text-sm font-semibold mb-3 text-muted text-xs uppercase tracking-wider">Schedules (<?= $f['ScheduleCount'] ?>)</h5>
            <?php if (isset($allSchedules[$f['FacilityID']]) && !empty($allSchedules[$f['FacilityID']])): ?>
              <div class="table-wrap">
                <table>
                  <thead><tr><th>Time</th><th>Status</th></tr></thead>
                  <tbody>
                    <?php foreach ($allSchedules[$f['FacilityID']] as $s): ?>
                    <tr>
                      <td><?= date('h:i A', strtotime($s['AvailableTime_Start'])) ?> — <?= date('h:i A', strtotime($s['AvailableTime_End'])) ?></td>
                      <td><span class="badge badge-<?= $s['ScheduleStatus'] === 'Available' ? 'available' : ($s['ScheduleStatus'] === 'Booked' ? 'unavailable' : 'maintenance') ?>"><?= $s['ScheduleStatus'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <p class="text-muted text-sm">No schedules defined.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<style>
.form-sm { padding: 0.4rem 0.625rem; font-size: 0.85rem; }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
