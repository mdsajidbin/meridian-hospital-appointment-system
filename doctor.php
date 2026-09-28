<?php
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT d.*, s.name AS subcat_name FROM doctors d LEFT JOIN categories s ON s.id = d.sub_category_id WHERE d.id = ? AND d.status = 'approved'");
$stmt->execute([$id]);
$doctor = $stmt->fetch();

if (!$doctor) {
    http_response_code(404);
    $pageTitle = 'Doctor Not Found';
    include __DIR__ . '/includes/header.php';
    echo '<div class="section container text-center"><h2>Doctor not found</h2><a href="doctors.php" class="btn btn-primary">Back to All Doctors</a></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $doctor['name'];
$days = next_days(7);

// related doctors (same sub-category)
$rel = $pdo->prepare("SELECT * FROM doctors WHERE sub_category_id <=> ? AND id != ? AND status='approved' LIMIT 4");
$rel->execute([$doctor['sub_category_id'], $doctor['id']]);
$related = $rel->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container">
    <div class="profile-grid" data-aos="fade-up">
      <div class="profile-photo">
        <img src="<?= doctor_image_url($doctor['image_path']) ?>" alt="<?= clean($doctor['name']) ?>">
      </div>
      <div class="profile-info">
        <span class="badge-verified">✅ <?= clean($doctor['subcat_name'] ?: $doctor['specialist_in']) ?></span>
        <h1 style="margin:10px 0 2px;"><?= clean($doctor['name']) ?></h1>
        <p class="muted"><?= clean($doctor['degree']) ?><?= $doctor['higher_degree'] ? ' — ' . clean($doctor['higher_degree']) : '' ?></p>
        <p class="muted"><?= (int)$doctor['experience_years'] ?> years experience · <?= clean($doctor['work_place']) ?></p>

        <h4 style="margin-top:20px;">About</h4>
        <p class="muted"><?= nl2br(clean($doctor['about'])) ?></p>

        <p style="margin-top:12px;">Appointment fee: <span class="fee-tag"><?= format_money($doctor['fee']) ?></span></p>

        <h4 style="margin-top:26px;">Book an Appointment</h4>
        <div class="date-tabs">
          <?php foreach ($days as $d): ?>
            <div class="date-tab" data-date="<?= $d['date'] ?>">
              <div class="d"><?= $d['day_label'] ?></div>
              <div class="n"><?= $d['day_num'] ?></div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="slot-grid" id="slot-grid" data-doctor-id="<?= (int)$doctor['id'] ?>">
          <div class="muted">Select a day to see available times.</div>
        </div>

        <form method="post" action="book-appointment.php">
          <?= csrf_field() ?>
          <input type="hidden" name="doctor_id" value="<?= (int)$doctor['id'] ?>">
          <input type="hidden" name="selected_date" id="selected_date" value="">
          <input type="hidden" name="selected_time" id="selected_time" value="">
          <button type="submit" id="book-btn" class="btn btn-primary magnetic" disabled>Book an Appointment</button>
        </form>
      </div>
    </div>
  </div>
</section>

<?php if ($related): ?>
<section class="section" style="background:#fff;">
  <div class="container">
    <h2 class="section-title text-center">Related Doctors</h2>
    <p class="section-sub text-center">Simply browse through our extensive list of trusted doctors.</p>
    <div class="doctor-grid">
      <?php foreach ($related as $d): ?>
        <a href="doctor.php?id=<?= (int)$d['id'] ?>" class="doctor-card">
          <div class="img-wrap"><img src="<?= doctor_image_url($d['image_path']) ?>" alt="<?= clean($d['name']) ?>" loading="lazy"></div>
          <div class="info">
            <div class="availability"><span class="dot green"></span> Available</div>
            <h4><?= clean($d['name']) ?></h4>
            <div class="spec"><?= clean($d['specialist_in']) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
