<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'All Doctors';

// Build the filter list: top-level categories + "Specialist" sub-categories
$allFilters = $pdo->query("
    SELECT c.*, p.name AS parent_name FROM categories c
    LEFT JOIN categories p ON p.id = c.parent_id
    ORDER BY (c.parent_id IS NULL) DESC, c.name ASC
")->fetchAll();

$selected = isset($_GET['specialty']) ? array_map('strval', (array)$_GET['specialty']) : [];
$search = trim((string)($_GET['q'] ?? ''));

// The imported directory stores doctors under the broad "Specialist"
// category, with the actual discipline in its sub-category. Map the six
// public specialties to those imported sub-category names as well.
$specialtyAliases = [
    'General Physician' => ['Medicine'],
    'Gastroenterologist' => ['Gastroenterology'],
    'Gynecologist' => ['Gyne & OBS'],
    'Neurologist' => ['Neuro Medicine'],
    'Pediatricians' => ['Pediatrics Specialist', 'Pediatric & Surgery'],
];
$categoryIds = [];
$subCategoryIds = [];
$filterNames = [];
$filterNamesForSelectedCategory = [];
foreach ($allFilters as $filter) {
    $value = ($filter['parent_id'] === null ? 'cat-' : 'sub-') . $filter['id'];
    if (!in_array($value, $selected, true)) continue;
    if ($filter['parent_id'] === null) {
        $categoryIds[] = (int)$filter['id'];
        $filterNamesForSelectedCategory[] = $filter['name'];
        foreach ($specialtyAliases[$filter['name']] ?? [] as $alias) $filterNames[] = $alias;
    } else {
        $subCategoryIds[] = (int)$filter['id'];
        $filterNames[] = $filter['name'];
    }
}
// Continue to accept existing links such as doctors.php?specialty=Neurologist.
foreach ($selected as $value) {
    if (preg_match('/^(cat|sub)-\d+$/', $value)) continue;
    $filterNamesForSelectedCategory[] = $value;
    $filterNames[] = $value;
    foreach ($specialtyAliases[$value] ?? [] as $alias) $filterNames[] = $alias;
}
$filterNames = array_values(array_unique($filterNames));
$filterNamesForSelectedCategory = array_values(array_unique($filterNamesForSelectedCategory));

$sql = "SELECT d.*, c.name AS cat_name, s.name AS subcat_name FROM doctors d
        LEFT JOIN categories c ON c.id = d.category_id
        LEFT JOIN categories s ON s.id = d.sub_category_id
        WHERE d.status = 'approved'";
$params = [];

if ($categoryIds || $subCategoryIds || $filterNames || $filterNamesForSelectedCategory) {
    $conditions = [];
    if ($categoryIds) {
        $conditions[] = 'c.id IN (' . implode(',', array_fill(0, count($categoryIds), '?')) . ')';
        array_push($params, ...$categoryIds);
    }
    if ($subCategoryIds) {
        $conditions[] = 's.id IN (' . implode(',', array_fill(0, count($subCategoryIds), '?')) . ')';
        array_push($params, ...$subCategoryIds);
    }
    if ($filterNamesForSelectedCategory) {
        $ph = implode(',', array_fill(0, count($filterNamesForSelectedCategory), '?'));
        $conditions[] = "c.name IN ($ph)";
        array_push($params, ...$filterNamesForSelectedCategory);
    }
    if ($filterNames) {
        $ph = implode(',', array_fill(0, count($filterNames), '?'));
        $conditions[] = "(s.name IN ($ph) OR d.specialist_in IN ($ph))";
        array_push($params, ...$filterNames, ...$filterNames);
    }
    $sql .= ' AND (' . implode(' OR ', $conditions) . ')';
}
if ($search !== '') {
    $sql .= " AND (d.name LIKE ? OR c.name LIKE ? OR s.name LIKE ? OR d.specialist_in LIKE ?)";
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like, $like);
}
$sql .= " ORDER BY d.priority DESC, d.name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$doctors = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container">
    <h2 class="section-title">All Doctors</h2>
    <p class="section-sub">Browse our <?= count($doctors) ?> verified doctors and book an appointment instantly.</p>

    <form method="get" id="filterForm">
    <div class="doctors-layout">
      <aside class="filter-sidebar">
        <h4>Filter by Specialty</h4>
          <?php foreach ($allFilters as $f): ?>
            <?php if ($f['parent_id'] === null): ?>
              <label><strong><?= clean($f['name']) ?></strong>
                <input type="checkbox" name="specialty[]" value="cat-<?= (int)$f['id'] ?>"
                  <?= in_array('cat-' . $f['id'], $selected, true) ? 'checked' : '' ?>>
              </label>
            <?php endif; ?>
          <?php endforeach; ?>
          <hr style="border-color:var(--border);margin:12px 0;">
          <?php foreach ($allFilters as $f): ?>
            <?php if ($f['parent_id'] !== null): ?>
              <label><?= clean($f['name']) ?>
                <input type="checkbox" name="specialty[]" value="sub-<?= (int)$f['id'] ?>"
                  <?= in_array('sub-' . $f['id'], $selected, true) ? 'checked' : '' ?>>
              </label>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php if ($selected): ?>
          <a href="doctors.php" class="btn btn-outline btn-sm btn-block" style="margin-top:12px;">Clear Filters</a>
        <?php endif; ?>
      </aside>

      <div style="flex:1;">
        <div class="doctor-search">
          <input type="search" name="q" value="<?= clean($search) ?>" class="form-control" placeholder="Search by doctor name or specialty" aria-label="Search doctors">
          <button type="submit" class="btn btn-primary">Search</button>
        </div>
        <?php if (!$doctors): ?>
          <p class="muted">No doctors found for the selected specialty.</p>
        <?php endif; ?>
        <div class="doctor-grid">
          <?php foreach ($doctors as $d): ?>
            <a href="doctor.php?id=<?= (int)$d['id'] ?>" class="doctor-card">
              <div class="img-wrap">
                <img src="<?= doctor_image_url($d['image_path']) ?>" alt="<?= clean($d['name']) ?>" loading="lazy">
              </div>
              <div class="info">
                <div class="availability"><span class="dot green"></span> Available</div>
                <h4><?= clean($d['name']) ?></h4>
                <div class="spec"><?= clean($d['subcat_name'] ?: $d['specialist_in']) ?></div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    </form>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
