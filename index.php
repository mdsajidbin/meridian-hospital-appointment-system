<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Home';

$categories = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY id LIMIT 6")->fetchAll();
$topDoctors = $pdo->query("SELECT * FROM doctors WHERE status='approved' ORDER BY priority DESC, id ASC LIMIT 8")->fetchAll();
$today = date('D');
$todayActiveQuery = $pdo->prepare("SELECT COUNT(DISTINCT d.id) FROM doctors d INNER JOIN doctor_availability da ON da.doctor_id = d.id WHERE d.status = 'approved' AND da.day_of_week = ?");
$todayActiveQuery->execute([$today]);
$todayActiveDoctors = (int)$todayActiveQuery->fetchColumn();

include __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="container">
    <div class="hero-text" data-aos="fade-up">
      <span class="badge-verified">✅ Trusted by 20,000+ patients</span>
      <h1>Book Appointment With <span>Trusted Doctors</span> at Meridian Hospital</h1>
      <p class="muted" style="max-width:480px;">Find the right specialist, pick a convenient time, and confirm your visit in under a minute — Meridian Hospital Chattogram is here for you, 24/7.</p>
      <div style="display:flex;gap:14px;margin-top:22px;flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>doctors.php" class="btn btn-primary magnetic">Book Appointment →</a>
        <a href="<?= BASE_URL ?>about.php" class="btn btn-outline">Learn More</a>
      </div>
    </div>
    <div class="hero-visual" data-aos="fade-up">
      <div class="glass-card">
        <img src="<?= BASE_URL ?>assets/images/doctors/doc1.png" alt="Doctor" style="border-radius:12px;">
        <div style="margin-top:14px;display:flex;align-items:center;gap:8px;">
          <span class="dot green"></span><strong><?= $todayActiveDoctors ?></strong> <span class="muted">doctors available today</span>
        </div>
      </div>
      <div class="float-icon i1" style="top:6%;right:8%;">🩺</div>
      <div class="float-icon i2" style="bottom:14%;left:2%;">💊</div>
      <div class="float-icon i3" style="top:50%;right:-6px;">❤️</div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <h2 class="section-title text-center">Find by Specialty</h2>
    <p class="section-sub text-center">Simply browse through our extensive list of trusted doctors.</p>
    <div class="specialty-grid" data-aos="fade-up">
      <?php foreach ($categories as $c): ?>
        <a href="<?= BASE_URL ?>doctors.php?specialty=<?= urlencode($c['name']) ?>" class="specialty-item">
          <div class="specialty-icon">
            <img src="<?= BASE_URL ?>assets/images/icons/<?= clean($c['icon']) ?>" alt="<?= clean($c['name']) ?>">
          </div>
          <div><?= clean($c['name']) ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="background:#fff;">
  <div class="container">
    <h2 class="section-title text-center">Top Doctors to Book</h2>
    <p class="section-sub text-center">Simply browse through our extensive list of trusted doctors.</p>
    <div class="doctor-grid" data-aos="fade-up">
      <?php foreach ($topDoctors as $d): ?>
        <a href="<?= BASE_URL ?>doctor.php?id=<?= (int)$d['id'] ?>" class="doctor-card">
          <div class="img-wrap">
            <img src="<?= doctor_image_url($d['image_path']) ?>" alt="<?= clean($d['name']) ?>" loading="lazy">
          </div>
          <div class="info">
            <div class="availability"><span class="dot green"></span> Available</div>
            <h4><?= clean($d['name']) ?></h4>
            <div class="spec"><?= clean($d['specialist_in'] ?: $d['designation']) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="text-center" style="margin-top:34px;">
      <a href="<?= BASE_URL ?>doctors.php" class="btn btn-outline">View All Doctors</a>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="cta-banner" data-aos="fade-up">
      <div>
        <h2>Book Appointment With 100+ Trusted Doctors</h2>
        <p style="opacity:.9;">Simple, fast, and always available — day or night.</p>
      </div>
      <a href="<?= BASE_URL ?>register.php" class="btn btn-primary magnetic">Create Account →</a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
