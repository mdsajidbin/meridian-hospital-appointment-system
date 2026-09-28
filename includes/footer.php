<footer>
  <div class="container footer-grid">
    <div>
      <div class="nav-logo" style="margin-bottom:14px;">
        <img src="<?= BASE_URL ?>assets/images/logo.png" alt="logo" style="height:44px;">
        <span style="color:#fff;font-weight:700;">Meridian Hospital Chattogram</span>
      </div>
      <p style="max-width:340px;color:#a9c9bb;">Serving with Care &amp; Compassion. A modern, trusted appointment platform connecting patients with the right doctor, fast.</p>
    </div>
    <div>
      <h4>Company</h4>
      <ul style="display:flex;flex-direction:column;gap:8px;margin-top:14px;">
        <li><a href="<?= BASE_URL ?>index.php">Home</a></li>
        <li><a href="<?= BASE_URL ?>about.php">About Us</a></li>
        <li><a href="<?= BASE_URL ?>doctors.php">All Doctors</a></li>
        <li><a href="<?= BASE_URL ?>contact.php">Contact Us</a></li>
        <li><a href="<?= BASE_URL ?>admin/login.php">Admin Panel</a></li>
      </ul>
    </div>
    <div>
      <h4>Get in Touch</h4>
      <ul style="display:flex;flex-direction:column;gap:8px;margin-top:14px;color:#a9c9bb;">
        <li>📍 <?= HOSPITAL_ADDRESS ?></li>
        <li>📞 <?= HOSPITAL_PHONE_MAIN ?></li>
        <li>🚨 Emergency: <?= HOSPITAL_PHONE_EMERGENCY ?></li>
        <li>✉️ <?= HOSPITAL_EMAIL ?></li>
        <li>🕐 Open <?= HOSPITAL_HOURS ?></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    &copy; <?= date('Y') ?> <?= HOSPITAL_NAME ?>. All rights reserved.
  </div>
</footer>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
