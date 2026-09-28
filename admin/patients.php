<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin_login('login.php');
$activeMenu = 'patients';
$breadcrumb = 'Patients';

$q = trim($_GET['q'] ?? '');
$where = "WHERE u.role = 'patient'";
$params = [];
if ($q !== '') {
    $where .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $params = ["%$q%", "%$q%", "%$q%"];
}

$stmt = $pdo->prepare("
  SELECT u.id, u.name, u.email, u.phone, u.created_at, p.age, p.gender, p.address,
    (SELECT COUNT(*) FROM appointments a WHERE a.patient_id = u.id) AS appointment_count
  FROM users u LEFT JOIN patients p ON p.user_id = u.id
  $where ORDER BY u.created_at DESC
");
$stmt->execute($params);
$patients = $stmt->fetchAll();

include __DIR__ . '/includes/head.php';
?>
<div class="a-content">
  <h1 class="a-page-title">Patients</h1>

  <div class="a-card">
    <div class="a-table-toolbar">
      <form method="get" style="display:flex;gap:8px;">
        <input class="a-search" type="text" name="q" placeholder="Search name, email, phone…" value="<?= clean($q) ?>">
        <button class="a-btn a-btn-outline a-btn-sm" type="submit">Search</button>
      </form>
      <div class="a-export-btns">
        <button class="a-btn a-btn-outline a-btn-sm" data-export="copy" data-export-table="patientTable">Copy</button>
        <button class="a-btn a-btn-outline a-btn-sm" data-export="csv" data-export-table="patientTable">CSV</button>
        <button class="a-btn a-btn-outline a-btn-sm" data-export="print" data-export-table="patientTable">Print</button>
      </div>
    </div>
    <div class="a-table-wrap">
      <table class="a-table" id="patientTable">
        <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Age</th><th>Gender</th><th>Address</th><th>Appointments</th><th>Joined</th></tr></thead>
        <tbody>
          <?php foreach ($patients as $p): ?>
            <tr>
              <td><?= $p['id'] ?></td>
              <td><?= clean($p['name']) ?></td>
              <td><?= clean($p['email']) ?></td>
              <td><?= clean($p['phone']) ?></td>
              <td><?= clean($p['age']) ?></td>
              <td><?= clean($p['gender']) ?></td>
              <td><?= clean($p['address']) ?></td>
              <td><?= (int)$p['appointment_count'] ?></td>
              <td><?= date('j M Y', strtotime($p['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$patients): ?><tr><td colspan="9" class="text-center">No patients found.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/foot.php'; ?>
