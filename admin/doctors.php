<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin_login('login.php');
$activeMenu = 'doctors';
$breadcrumb = 'Doctor List';

// Handle inline status / editing-status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (verify_csrf()) {
        $id = (int)($_POST['doctor_id'] ?? 0);
        if ($_POST['action'] === 'update_status') {
            $status = $_POST['status'];
            if (in_array($status, ['approved','pending','hold','banned'], true)) {
                $stmt = $pdo->prepare("UPDATE doctors SET status = ? WHERE id = ?");
                $stmt->execute([$status, $id]);
            }
        } elseif ($_POST['action'] === 'toggle_lock') {
            $stmt = $pdo->prepare("UPDATE doctors SET editing_status = CASE WHEN editing_status = 'locked' THEN 'unlocked' ELSE 'locked' END WHERE id = ?");
            $stmt->execute([$id]);
        }
    }
    $redirectParams = array_filter(['q' => $_GET['q'] ?? '', 'status' => $_GET['status'] ?? ''], static fn($value) => $value !== '');
    header('Location: doctors.php' . ($redirectParams ? '?' . http_build_query($redirectParams) : ''));
    exit;
}

$q = trim($_GET['q'] ?? '');
$allowedStatuses = ['approved', 'pending', 'hold', 'banned'];
$filterStatus = $_GET['status'] ?? '';
if (!in_array($filterStatus, $allowedStatuses, true)) $filterStatus = '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

$conditions = [];
$params = [];
if ($q !== '') {
    $conditions[] = "(d.name LIKE ? OR d.bmdc_number LIKE ? OR d.email LIKE ?)";
    array_push($params, "%$q%", "%$q%", "%$q%");
}
if ($filterStatus !== '') {
    $conditions[] = 'd.status = ?';
    $params[] = $filterStatus;
}
$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$total = $pdo->prepare("SELECT COUNT(*) c FROM doctors d $where");
$total->execute($params);
$total = (int)$total->fetch()['c'];
$totalPages = max(1, ceil($total / $perPage));

$sql = "SELECT d.*, s.name AS subcat_name FROM doctors d LEFT JOIN categories s ON s.id = d.sub_category_id $where ORDER BY d.id DESC LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$doctors = $stmt->fetchAll();

include __DIR__ . '/includes/head.php';
?>
<div class="a-content">
  <h1 class="a-page-title">Doctor List</h1>

  <div class="a-card">
    <div class="a-table-toolbar">
      <form method="get" style="display:flex;gap:8px;">
        <input class="a-search" type="text" name="q" placeholder="Search name, BMDC, email…" value="<?= clean($q) ?>">
        <select class="a-search" name="status" aria-label="Filter doctors by status">
          <option value="">All statuses</option>
          <?php foreach ($allowedStatuses as $status): ?><option value="<?= $status ?>" <?= $filterStatus === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?>
        </select>
        <button class="a-btn a-btn-outline a-btn-sm" type="submit">Search</button>
      </form>
      <div class="a-export-btns">
        <button class="a-btn a-btn-outline a-btn-sm" data-export="copy" data-export-table="doctorTable">Copy</button>
        <button class="a-btn a-btn-outline a-btn-sm" data-export="csv" data-export-table="doctorTable">CSV</button>
        <button class="a-btn a-btn-outline a-btn-sm" data-export="excel" data-export-table="doctorTable">Excel</button>
        <button class="a-btn a-btn-outline a-btn-sm" data-export="print" data-export-table="doctorTable">Print</button>
        <a href="add-doctor.php" class="a-btn a-btn-sm">+ Add Doctor</a>
      </div>
    </div>

    <div class="a-table-wrap">
      <table class="a-table" id="doctorTable">
        <thead>
          <tr>
            <th>#</th><th>Image</th><th>Name</th><th>Sub Category</th><th>BMDC</th><th>Phone</th>
            <th>Fee</th><th>Status</th><th>Editing</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($doctors as $d): ?>
            <tr>
              <td><?= (int)$d['id'] ?></td>
              <td><img src="<?= doctor_image_url($d['image_path']) ?>" style="width:34px;height:34px;border-radius:8px;object-fit:cover;"></td>
              <td><?= clean($d['name']) ?></td>
              <td><?= clean($d['subcat_name']) ?></td>
              <td><?= clean($d['bmdc_number']) ?></td>
              <td><?= clean($d['phone']) ?></td>
              <td><?= format_money($d['fee']) ?></td>
              <td>
                <form method="post">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="update_status">
                  <input type="hidden" name="doctor_id" value="<?= (int)$d['id'] ?>">
                  <select name="status" class="select-status <?= $d['status'] ?>" onchange="this.form.submit()">
                    <option value="approved" <?= $d['status']==='approved'?'selected':'' ?>>Approved</option>
                    <option value="pending" <?= $d['status']==='pending'?'selected':'' ?>>Pending</option>
                    <option value="hold" <?= $d['status']==='hold'?'selected':'' ?>>Hold</option>
                    <option value="banned" <?= $d['status']==='banned'?'selected':'' ?>>Banned</option>
                  </select>
                </form>
              </td>
              <td>
                <form method="post">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle_lock">
                  <input type="hidden" name="doctor_id" value="<?= (int)$d['id'] ?>">
                  <button type="submit" class="a-btn a-btn-outline a-btn-sm tooltip" data-tip="Toggle edit lock">
                    <?= $d['editing_status'] === 'locked' ? '🔒 Locked' : '🔓 Unlocked' ?>
                  </button>
                </form>
              </td>
              <td>
                <a href="edit-doctor.php?id=<?= (int)$d['id'] ?>" class="a-btn a-btn-outline a-btn-sm tooltip" data-tip="Edit doctor">✏️</a>
                <a href="delete-doctor.php?id=<?= (int)$d['id'] ?>" class="a-btn a-btn-outline a-btn-sm tooltip" data-tip="Delete doctor" onclick="return confirm('Delete this doctor permanently?');">🗑️</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$doctors): ?><tr><td colspan="10" class="text-center">No doctors found.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="pagination">
      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <a class="<?= $p === $page ? 'active' : '' ?>" href="?<?= http_build_query(array_filter(['page' => $p, 'q' => $q, 'status' => $filterStatus], static fn($value) => $value !== '')) ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/foot.php'; ?>
