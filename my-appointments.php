<?php
require_once __DIR__ . '/includes/functions.php';
require_patient_login();
$pageTitle = 'My Appointments';

$stmt = $pdo->prepare("
  SELECT a.*, d.name AS doctor_name, d.image_path, d.specialist_in, d.fee
  FROM appointments a JOIN doctors d ON d.id = a.doctor_id
  WHERE a.patient_id = ?
  ORDER BY a.appointment_date DESC, a.id DESC
");
$stmt->execute([$_SESSION['user']['id']]);
$appointments = $stmt->fetchAll();

$msg = flash('success');

include __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container">
    <h2 class="section-title">My Appointments</h2>
    <p class="section-sub">Track and manage your upcoming and past visits.</p>

    <?php if ($msg): ?><div class="alert alert-success"><?= clean($msg) ?></div><?php endif; ?>

    <?php if (!$appointments): ?>
      <div class="a-card text-center" style="padding:50px;">
        <p class="muted">You have no appointments yet.</p>
        <a href="doctors.php" class="btn btn-primary magnetic">Book Your First Appointment</a>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Doctor</th><th>Specialty</th><th>Date</th><th>Time</th><th>Fee</th><th>Status</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach ($appointments as $a): ?>
              <tr>
                <td style="display:flex;align-items:center;gap:10px;">
                  <img src="<?= doctor_image_url($a['image_path']) ?>" style="width:36px;height:36px;border-radius:8px;object-fit:cover;">
                  <?= clean($a['doctor_name']) ?>
                </td>
                <td><?= clean($a['specialist_in']) ?></td>
                <td><?= date('j M Y', strtotime($a['appointment_date'])) ?></td>
                <td><?= clean($a['appointment_time']) ?></td>
                <td><?= format_money($a['fee']) ?></td>
                <td><?= status_badge($a['status']) ?></td>
                <td>
                  <?php if (in_array($a['status'], ['pending','confirmed'], true) && strtotime($a['appointment_date']) >= strtotime('today')): ?>
                    <form method="post" action="cancel-appointment.php" onsubmit="return confirm('Cancel this appointment?');">
                      <?= csrf_field() ?>
                      <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                      <button class="btn btn-outline btn-sm" type="submit">Cancel</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
