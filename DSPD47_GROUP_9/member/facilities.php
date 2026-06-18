<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
requireMember();

$pageTitle = 'Facilities — FBS';

// Handle search and filter
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');

// Get distinct categories
$categories = $pdo->query("SELECT DISTINCT FacilityCategory FROM facility ORDER BY FacilityCategory")->fetchAll(PDO::FETCH_COLUMN);

// Build query
$sql = "SELECT * FROM facility WHERE FacilityStatus = 'Available'";
$params = [];

if ($search) {
    $sql .= " AND FacilityName LIKE ?";
    $params[] = "%$search%";
}
if ($category) {
    $sql .= " AND FacilityCategory = ?";
    $params[] = $category;
}
$sql .= " ORDER BY FacilityCategory, FacilityName";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$facilities = $stmt->fetchAll();



require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-wrapper">
  <div class="container">
    <div class="page-header">
      <h1 class="page-title">Facilities</h1>
      <p class="page-subtitle">Browse and book available facilities</p>
    </div>

    <form method="GET" class="search-bar">
      <input type="text" name="search" class="form-input" placeholder="Search facilities..."
             value="<?= htmlspecialchars($search) ?>">
      <select name="category" class="form-select" style="width:auto;min-width:160px;">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary">Search</button>
      <?php if ($search || $category): ?>
        <a href="/DSPD47_GROUP_9/member/facilities.php" class="btn btn-ghost">Clear</a>
      <?php endif; ?>
    </form>

    <?php if (empty($facilities)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">?</div>
        <h3>No facilities found</h3>
        <p>Try adjusting your search or filter criteria.</p>
      </div>
    <?php else: ?>
      <div class="facility-grid">
        <?php foreach ($facilities as $i => $f): ?>
        <div class="facility-card animate-in <?= $i < 4 ? 'delay-' . ($i + 1) : '' ?>">
          <div class="facility-card-img">
            <span class="facility-card-icon"><?= htmlspecialchars($f['FacilityCategory'][0]) ?></span>
          </div>
          <div class="facility-card-body">
            <h3 class="facility-card-title"><?= htmlspecialchars($f['FacilityName']) ?></h3>
            <p class="facility-card-meta">
              <?= htmlspecialchars($f['FacilityCategory']) ?> · Up to <?= $f['FacilityCapacity'] ?> people
            </p>
            <div class="flex items-center justify-between mt-4">
              <span class="facility-card-price">RM <?= number_format($f['RatePerHour'], 2) ?>/hr</span>
              <span class="badge badge-<?= strtolower($f['FacilityStatus']) ?>"><?= $f['FacilityStatus'] ?></span>
            </div>
            <a href="/DSPD47_GROUP_9/member/facility_detail.php?id=<?= $f['FacilityID'] ?>" class="btn btn-primary btn-block mt-4">View Details</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
