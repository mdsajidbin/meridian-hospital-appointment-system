<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'About Us';
include __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container">
    <h2 class="section-title text-center">About <?= HOSPITAL_NAME ?></h2>
    <p class="section-sub text-center">Serving with Care &amp; Compassion.</p>

    <div class="profile-grid" data-aos="fade-up" style="align-items:flex-start;">
      <div style="flex:1 1 380px;">
        <img src="assets/images/logo.png" alt="Meridian Hospital Chattogram" style="max-width:280px;margin:0 auto 20px;">
        <p class="muted">Meridian Hospital Chattogram is a modern, patient-first healthcare institution located at the heart of GEC Circle, Chattogram. We bring together a wide network of experienced specialists across dozens of medical fields, backed by a digital appointment platform that lets patients find the right doctor and book a visit in minutes — any time, day or night.</p>
      </div>
      <div style="flex:1 1 380px;">
        <h3>Our Vision</h3>
        <p class="muted">To be the most trusted healthcare provider in Chattogram by combining compassionate care with accessible, technology-driven service — making quality medical attention available to every patient, every day of the year.</p>

        <h3 style="margin-top:26px;">Why Choose Us</h3>
        <div style="display:grid;gap:16px;margin-top:14px;">
          <div class="a-card" style="margin:0;padding:18px;">
            <h4 style="margin:0 0 6px;">⚡ Efficiency</h4>
            <p class="muted" style="margin:0;">Streamlined booking gets you in front of the right doctor fast — no waiting on hold.</p>
          </div>
          <div class="a-card" style="margin:0;padding:18px;">
            <h4 style="margin:0 0 6px;">📱 Convenience</h4>
            <p class="muted" style="margin:0;">Access a full network of specialists and manage every appointment from one account.</p>
          </div>
          <div class="a-card" style="margin:0;padding:18px;">
            <h4 style="margin:0 0 6px;">🤝 Personalization</h4>
            <p class="muted" style="margin:0;">Every visit is tailored around your history, symptoms, and the specialist best suited to you.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
