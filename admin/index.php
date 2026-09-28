<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin_login('login.php');
$activeMenu = 'dashboard';
$breadcrumb = 'Dashboard';

$stats = [];
$stats['total_doctors']    = (int)$pdo->query("SELECT COUNT(*) c FROM doctors")->fetch()['c'];
$stats['approved_doctors'] = (int)$pdo->query("SELECT COUNT(*) c FROM doctors WHERE status='approved'")->fetch()['c'];
$stats['pending_doctors']  = (int)$pdo->query("SELECT COUNT(*) c FROM doctors WHERE status='pending'")->fetch()['c'];
$stats['hold_doctors']     = (int)$pdo->query("SELECT COUNT(*) c FROM doctors WHERE status='hold'")->fetch()['c'];
$stats['banned_doctors']   = (int)$pdo->query("SELECT COUNT(*) c FROM doctors WHERE status='banned'")->fetch()['c'];

$stats['all_appointments']       = (int)$pdo->query("SELECT COUNT(*) c FROM appointments")->fetch()['c'];
$stats['completed_appointments'] = (int)$pdo->query("SELECT COUNT(*) c FROM appointments WHERE status='completed'")->fetch()['c'];
$stats['confirmed_appointments'] = (int)$pdo->query("SELECT COUNT(*) c FROM appointments WHERE status='confirmed'")->fetch()['c'];
$stats['pending_appointments']   = (int)$pdo->query("SELECT COUNT(*) c FROM appointments WHERE status='pending'")->fetch()['c'];

$stats['total_patients'] = (int)$pdo->query("SELECT COUNT(*) c FROM users WHERE role='patient'")->fetch()['c'];

// last 7 days appointment trend
$trendLabels = []; $trendData = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $trendLabels[] = date('D', strtotime($d));
    $stmt = $pdo->prepare("SELECT COUNT(*) c FROM appointments WHERE appointment_date = ?");
    $stmt->execute([$d]);
    $trendData[] = (int)$stmt->fetch()['c'];
}

include __DIR__ . '/includes/head.php';
?>
<div class="a-content">
  <h1 class="a-page-title">Dashboard</h1>

  <div class="stat-grid">
    <div class="stat-card stat-teal"><div class="icon">&#9877;</div><div class="num" data-countup="<?= $stats['total_doctors'] ?>">0</div><div class="lbl">Total Doctors</div><a class="more" href="doctors.php">More info &rarr;</a></div>
    <div class="stat-card stat-green"><div class="icon">&#10003;</div><div class="num" data-countup="<?= $stats['approved_doctors'] ?>">0</div><div class="lbl">Approved Doctors</div><a class="more" href="doctors.php?status=approved">More info &rarr;</a></div>
    <div class="stat-card stat-yellow"><div class="icon">&#8987;</div><div class="num" data-countup="<?= $stats['pending_doctors'] ?>">0</div><div class="lbl">Pending Doctors</div><a class="more" href="doctors.php?status=pending">More info &rarr;</a></div>
    <div class="stat-card stat-orange"><div class="icon">&#9208;</div><div class="num" data-countup="<?= $stats['hold_doctors'] ?>">0</div><div class="lbl">Hold Doctors</div><a class="more" href="doctors.php?status=hold">More info &rarr;</a></div>
    <div class="stat-card stat-red"><div class="icon">&#9940;</div><div class="num" data-countup="<?= $stats['banned_doctors'] ?>">0</div><div class="lbl">Banned Doctors</div><a class="more" href="doctors.php?status=banned">More info &rarr;</a></div>
    <div class="stat-card stat-teal"><div class="icon">&#9776;</div><div class="num" data-countup="<?= $stats['all_appointments'] ?>">0</div><div class="lbl">All Appointments</div><a class="more" href="appointments.php">More info &rarr;</a></div>
    <div class="stat-card stat-green"><div class="icon">&#9873;</div><div class="num" data-countup="<?= $stats['completed_appointments'] ?>">0</div><div class="lbl">Completed Appointments</div><a class="more" href="appointments.php?status=completed">More info &rarr;</a></div>
    <div class="stat-card stat-blue"><div class="icon">&#128204;</div><div class="num" data-countup="<?= $stats['confirmed_appointments'] ?>">0</div><div class="lbl">Confirmed Appointments</div><a class="more" href="appointments.php?status=confirmed">More info &rarr;</a></div>
    <div class="stat-card stat-yellow"><div class="icon">&#128337;</div><div class="num" data-countup="<?= $stats['pending_appointments'] ?>">0</div><div class="lbl">Pending Appointments</div><a class="more" href="appointments.php?status=pending">More info &rarr;</a></div>
    <div class="stat-card stat-teal"><div class="icon">&#128101;</div><div class="num" data-countup="<?= $stats['total_patients'] ?>">0</div><div class="lbl">Total Patients</div><a class="more" href="patients.php">More info &rarr;</a></div>
  </div>

  <div class="a-card">
    <h3>Appointments - Last 7 Days</h3>
    <canvas id="trendChart" height="90"></canvas>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
  new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
      labels: <?= json_encode($trendLabels) ?>,
      datasets: [{
        label: 'Appointments',
        data: <?= json_encode($trendData) ?>,
        borderColor: '#1f9d6b',
        backgroundColor: 'rgba(31,157,107,.15)',
        fill: true, tension: .35, pointRadius: 4
      }]
    },
    options: { animation: { duration: 900 }, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
  });
</script>
<?php include __DIR__ . '/includes/foot.php'; ?>
