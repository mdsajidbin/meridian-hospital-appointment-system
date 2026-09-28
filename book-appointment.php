<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Book Appointment';

$doctorId = (int)($_POST['doctor_id'] ?? $_GET['doctor_id'] ?? 0);
$date     = $_POST['selected_date'] ?? $_GET['selected_date'] ?? '';
$time     = $_POST['selected_time'] ?? $_GET['selected_time'] ?? '';
$step     = $_POST['step'] ?? 'select'; // select -> confirm -> success

$stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ? AND status = 'approved'");
$stmt->execute([$doctorId]);
$doctor = $stmt->fetch();

if (!$doctor || !$date || !$time) {
    redirect('doctors.php');
}

// Must be logged in as a patient to confirm a booking
if (!is_patient()) {
    $_SESSION['pending_booking'] = ['doctor_id' => $doctorId, 'date' => $date, 'time' => $time];
    $_SESSION['redirect_after_login'] = 'book-appointment.php?resume=1';
    flash('info', 'Please log in or create an account to confirm your appointment.');
    redirect('login.php');
}

// resume booking saved in session before login
if (isset($_GET['resume']) && isset($_SESSION['pending_booking'])) {
    $pb = $_SESSION['pending_booking'];
    $doctorId = $pb['doctor_id']; $date = $pb['date']; $time = $pb['time'];
    $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
    $stmt->execute([$doctorId]);
    $doctor = $stmt->fetch();
}

$patient = $pdo->prepare("SELECT u.*, p.age, p.gender, p.address FROM users u LEFT JOIN patients p ON p.user_id = u.id WHERE u.id = ?");
$patient->execute([$_SESSION['user']['id']]);
$patient = $patient->fetch();

$error = null;
$booked = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'confirm') {
    if (!verify_csrf()) {
        $error = 'Session expired, please try again.';
    } else {
        $problem = clean($_POST['problem'] ?? '');
        try {
            $ins = $pdo->prepare("INSERT INTO appointments
                (serial_number, doctor_id, patient_id, appointment_date, appointment_time, status, patient_problem, patient_name, patient_age, patient_gender, patient_address)
                VALUES (
                  (SELECT COALESCE(MAX(serial_number),0)+1 FROM appointments a2 WHERE a2.doctor_id = ? AND a2.appointment_date = ?),
                  ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?)");
            $ins->execute([
                $doctorId, $date,
                $doctorId, $_SESSION['user']['id'], $date, $time,
                $problem, $patient['name'], $patient['age'], $patient['gender'], $patient['address']
            ]);
            unset($_SESSION['pending_booking']);
            $booked = true;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'Sorry, this slot was just booked by someone else. Please choose another time.';
            } else {
                $error = 'Something went wrong. Please try again.';
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container" style="max-width:720px;">

    <div class="stepper">
      <div class="step <?= $booked ? 'done' : ($step === 'confirm' ? 'active' : 'done') ?>"><div class="circle">1</div><div class="label">Select Doctor</div></div>
      <div class="step <?= $booked ? 'done' : ($step === 'confirm' ? 'active' : 'done') ?>"><div class="circle">2</div><div class="label">Select Slot</div></div>
      <div class="step <?= $booked ? 'done' : 'active' ?>"><div class="circle">3</div><div class="label">Confirm Details</div></div>
      <div class="step <?= $booked ? 'active' : '' ?>"><div class="circle">4</div><div class="label">Success</div></div>
    </div>

    <?php if ($booked): ?>
      <div class="success-wrap" data-aos="fade-up">
        <div class="checkmark-circle">
          <svg viewBox="0 0 52 52"><path d="M14 27l8 8 16-16"/></svg>
        </div>
        <h2>Appointment Requested!</h2>
        <p class="muted">Your appointment with <strong><?= clean($doctor['name']) ?></strong> on
          <strong><?= date('D, j M Y', strtotime($date)) ?></strong> at <strong><?= clean($time) ?></strong> has been submitted.
          Status: <span class="status-badge badge-yellow">Pending</span> — our team will confirm it shortly.</p>
        <div style="display:flex;gap:14px;justify-content:center;margin-top:24px;">
          <a href="my-appointments.php" class="btn btn-primary magnetic">View My Appointments</a>
          <a href="doctors.php" class="btn btn-outline">Book Another</a>
        </div>
      </div>

    <?php else: ?>
      <div class="a-card glass-card" style="background:#fff;">
        <?php if ($error): ?><div class="alert alert-error"><?= clean($error) ?></div><?php endif; ?>

        <div style="display:flex;gap:18px;align-items:center;margin-bottom:20px;">
          <img src="<?= doctor_image_url($doctor['image_path']) ?>" alt="" style="width:70px;height:70px;border-radius:12px;object-fit:cover;">
          <div>
            <h3 style="margin:0;"><?= clean($doctor['name']) ?></h3>
            <div class="muted"><?= clean($doctor['specialist_in']) ?></div>
            <div class="muted"><?= date('D, j M Y', strtotime($date)) ?> at <?= clean($time) ?> · Fee: <?= format_money($doctor['fee']) ?></div>
          </div>
        </div>

        <form method="post" action="book-appointment.php">
          <?= csrf_field() ?>
          <input type="hidden" name="doctor_id" value="<?= (int)$doctorId ?>">
          <input type="hidden" name="selected_date" value="<?= clean($date) ?>">
          <input type="hidden" name="selected_time" value="<?= clean($time) ?>">
          <input type="hidden" name="step" value="confirm">

          <div class="form-row">
            <div class="form-group"><label>Full Name</label><input class="form-control" value="<?= clean($patient['name']) ?>" disabled></div>
            <div class="form-group"><label>Phone</label><input class="form-control" value="<?= clean($patient['phone'] ?? '') ?>" disabled></div>
          </div>
          <div class="form-group">
            <label>Describe your problem (optional)</label>
            <textarea class="form-control" name="problem" rows="3" placeholder="Briefly describe your symptoms or reason for visit"></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-block magnetic">Confirm Booking</button>
        </form>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
