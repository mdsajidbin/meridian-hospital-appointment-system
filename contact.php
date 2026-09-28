<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Contact Us';

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    // Phase 1: no email backend configured — acknowledge receipt only.
    $sent = true;
}

include __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container">
    <h2 class="section-title text-center">Contact Us</h2>
    <p class="section-sub text-center">We'd love to hear from you. Reach out any time — we're open 24/7.</p>

    <div class="profile-grid" data-aos="fade-up">
      <div style="flex:1 1 340px;">
        <div class="a-card" style="margin-bottom:16px;">
          <h4>📍 Office Address</h4>
          <p class="muted"><?= HOSPITAL_ADDRESS ?></p>
          <a href="<?= HOSPITAL_MAP_URL ?>" target="_blank" rel="noopener" class="btn btn-outline btn-sm">View on Google Maps</a>
        </div>
        <div class="a-card" style="margin-bottom:16px;">
          <h4>📞 Phone</h4>
          <p class="muted">Main: <?= HOSPITAL_PHONE_MAIN ?><br>Appointment: <?= HOSPITAL_PHONE_APPOINTMENT ?><br>🚨 Emergency (24/7): <?= HOSPITAL_PHONE_EMERGENCY ?></p>
        </div>
        <div class="a-card">
          <h4>✉️ Email &amp; Hours</h4>
          <p class="muted"><?= HOSPITAL_EMAIL ?><br>Open <?= HOSPITAL_HOURS ?> — Emergency services available 24/7.</p>
        </div>
      </div>

      <div style="flex:1 1 380px;">
        <div class="a-card">
          <h3>Send us a message</h3>
          <?php if ($sent): ?>
            <div class="alert alert-success">Thanks for reaching out! Our team will get back to you shortly.</div>
          <?php endif; ?>
          <form method="post" action="contact.php">
            <?= csrf_field() ?>
            <div class="form-group"><label>Your Name</label><input class="form-control" name="name" required></div>
            <div class="form-group"><label>Email</label><input type="email" class="form-control" name="email" required></div>
            <div class="form-group"><label>Message</label><textarea class="form-control" name="message" rows="4" required></textarea></div>
            <button type="submit" class="btn btn-primary btn-block magnetic">Send Message</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
