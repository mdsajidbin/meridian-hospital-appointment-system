<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin_login('login.php');
$activeMenu = 'appointments';
$breadcrumb = 'Appointment List';

// Inline status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf() && ($_POST['action'] ?? '') === 'update_status') {
    $id = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if (in_array($status, ['pending','confirmed','completed','cancelled'], true)) {
        $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?")->execute([$status, $id]);
    }
    header('Location: appointments.php?' . http_build_query($_GET));
    exit;
}

$filterDate = $_GET['date'] ?? '';
$filterBookedDate = $_GET['booked_on'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$q = trim($_GET['q'] ?? '');

// Analytics (independent of table filter, except date filter card)
$all = (int)$pdo->query("SELECT COUNT(*) c FROM appointments")->fetch()['c'];
$completed = (int)$pdo->query("SELECT COUNT(*) c FROM appointments WHERE status='completed'")->fetch()['c'];
$confirmed = (int)$pdo->query("SELECT COUNT(*) c FROM appointments WHERE status='confirmed'")->fetch()['c'];
$pending = (int)$pdo->query("SELECT COUNT(*) c FROM appointments WHERE status='pending'")->fetch()['c'];
$cancelled = (int)$pdo->query("SELECT COUNT(*) c FROM appointments WHERE status='cancelled'")->fetch()['c'];

$dateCount = 0;
if ($filterBookedDate !== '') {
    $stmt = $pdo->prepare("SELECT COUNT(*) c FROM appointments WHERE DATE(created_at) = ?");
    $stmt->execute([$filterBookedDate]);
    $dateCount = (int)$stmt->fetch()['c'];
}

// Table query
$where = [];
$params = [];
if ($filterDate) { $where[] = "a.appointment_date = ?"; $params[] = $filterDate; }
if ($filterBookedDate !== '') { $where[] = "DATE(a.created_at) = ?"; $params[] = $filterBookedDate; }
if ($filterStatus) { $where[] = "a.status = ?"; $params[] = $filterStatus; }
if ($q !== '') { $where[] = "(a.patient_name LIKE ? OR d.name LIKE ? OR d.bmdc_number LIKE ?)"; $params = array_merge($params, ["%$q%","%$q%","%$q%"]); }
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("
  SELECT a.*, d.name AS doctor_name, d.bmdc_number
  FROM appointments a JOIN doctors d ON d.id = a.doctor_id
  $whereSql
  ORDER BY a.appointment_date DESC, a.appointment_time DESC
  LIMIT 200
");
$stmt->execute($params);
$appointments = $stmt->fetchAll();

include __DIR__ . '/includes/head.php';
?>
<div class="a-content">
  <h1 class="a-page-title">Appointments Analytics</h1>

  <div class="stat-grid">
    <div class="stat-card stat-teal"><div class="icon">&#9776;</div><div class="num" data-countup="<?= $all ?>">0</div><div class="lbl">All Appointment</div><a class="more" href="appointments.php">More info &rarr;</a></div>
    <form method="get" class="stat-card stat-blue" style="cursor:pointer;">
      <div class="icon">&#128197;</div><div class="num" data-countup="<?= $dateCount ?>">0</div><div class="lbl">Appointments Booked On Date</div>
      <input type="date" name="booked_on" value="<?= clean($filterBookedDate) ?>" onchange="this.form.submit()" aria-label="Count appointments booked on a date" style="margin-top:8px;width:100%;border-radius:6px;border:none;padding:4px;">
      <a class="more" href="appointments.php<?= $filterBookedDate !== '' ? '?booked_on=' . urlencode($filterBookedDate) : '' ?>">More info &rarr;</a>
    </form>
    <div class="stat-card stat-green"><div class="icon">&#9873;</div><div class="num" data-countup="<?= $completed ?>">0</div><div class="lbl">Completed</div><a class="more" href="appointments.php?status=completed">More info &rarr;</a></div>
    <div class="stat-card stat-teal"><div class="icon">&#128204;</div><div class="num" data-countup="<?= $confirmed ?>">0</div><div class="lbl">Confirmed</div><a class="more" href="appointments.php?status=confirmed">More info &rarr;</a></div>
    <div class="stat-card stat-yellow"><div class="icon">&#128337;</div><div class="num" data-countup="<?= $pending ?>">0</div><div class="lbl">Pending</div><a class="more" href="appointments.php?status=pending">More info &rarr;</a></div>
    <div class="stat-card stat-red"><div class="icon">&#10006;</div><div class="num" data-countup="<?= $cancelled ?>">0</div><div class="lbl">Cancel</div><a class="more" href="appointments.php?status=cancelled">More info &rarr;</a></div>
  </div>

  <div class="a-card">
    <div class="a-table-toolbar">
      <form method="get" style="display:flex;gap:8px;flex-wrap:wrap;">
        <input class="a-search" type="text" name="q" placeholder="Search patient, doctor, BMDCâ€¦" value="<?= clean($q) ?>">
        <input type="date" name="date" value="<?= clean($filterDate) ?>" class="a-search" style="min-width:150px;">
        <select name="status" class="a-search" style="min-width:150px;">
          <option value="">All Status</option>
          <?php foreach (['pending','confirmed','completed','cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= $filterStatus===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="a-btn a-btn-outline a-btn-sm" type="submit">Filter</button>
        <a href="appointments.php" class="a-btn a-btn-outline a-btn-sm">Reset</a>
      </form>
      <div class="a-export-btns">
        <button class="a-btn a-btn-outline a-btn-sm" data-export="copy" data-export-table="apptTable">Copy</button>
        <button class="a-btn a-btn-outline a-btn-sm" data-export="csv" data-export-table="apptTable">CSV</button>
        <button class="a-btn a-btn-outline a-btn-sm" data-export="excel" data-export-table="apptTable">Excel</button>
        <button class="a-btn a-btn-outline a-btn-sm" data-export="print" data-export-table="apptTable">Print</button>
      </div>
    </div>

    <div class="a-table-wrap">
      <table class="a-table" id="apptTable">
        <thead>
          <tr>
            <th>#SL</th><th>Time</th><th>Date</th><th>Doctor</th><th>BMDC</th>
            <th>Patient</th><th>Age</th><th>Gender</th><th>Problem</th><th>Address</th><th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($appointments as $a): ?>
            <tr>
              <td><?= (int)$a['serial_number'] ?></td>
              <td><?= clean($a['appointment_time']) ?></td>
              <td><?= date('d/m/Y', strtotime($a['appointment_date'])) ?></td>
              <td><?= clean($a['doctor_name']) ?></td>
              <td><?= clean($a['bmdc_number']) ?></td>
              <td><?= clean($a['patient_name']) ?></td>
              <td><?= clean($a['patient_age']) ?></td>
              <td><?= clean($a['patient_gender']) ?></td>
              <td style="white-space:normal;max-width:220px;"><?= clean($a['patient_problem']) ?></td>
              <td><?= clean($a['patient_address']) ?></td>
              <td>
                <form method="post">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="update_status">
                  <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                  <select name="status" class="select-status <?= $a['status'] ?>" onchange="this.form.submit()">
                    <?php foreach (['pending','confirmed','completed','cancelled'] as $s): ?>
                      <option value="<?= $s ?>" <?= $a['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$appointments): ?><tr><td colspan="11" class="text-center">No appointments found.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/foot.php'; ?>


