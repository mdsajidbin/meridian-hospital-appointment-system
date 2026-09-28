<?php
/** Expects $activeMenu to be set, e.g. 'dashboard','doctors','categories','patients','users','appointments' */
$activeMenu = $activeMenu ?? '';
?>
<aside class="a-sidebar">
  <div class="brand">
    <img src="../assets/images/logo.png" alt="logo">
    <span>Meridian Hospital</span>
  </div>
  <nav>
    <a class="a-nav-link <?= $activeMenu === 'dashboard' ? 'active' : '' ?>" href="index.php">
      <span class="a-nav-icon">📊</span> Dashboard
    </a>

    <div class="a-nav-link" data-toggle="sub-doctor"><span class="a-nav-icon">🩺</span> Doctor</div>
    <div class="a-sub <?= in_array($activeMenu, ['doctors','add-doctor']) ? 'open' : '' ?>" id="sub-doctor">
      <a class="<?= $activeMenu === 'add-doctor' ? 'active' : '' ?>" href="add-doctor.php">Add Doctor</a>
      <a class="<?= $activeMenu === 'doctors' ? 'active' : '' ?>" href="doctors.php">Doctor List</a>
    </div>

    <a class="a-nav-link <?= $activeMenu === 'categories' ? 'active' : '' ?>" href="categories.php">
      <span class="a-nav-icon">🗂️</span> Categories
    </a>
    <a class="a-nav-link <?= $activeMenu === 'patients' ? 'active' : '' ?>" href="patients.php">
      <span class="a-nav-icon">🧑‍🤝‍🧑</span> Patient
    </a>
    <a class="a-nav-link <?= $activeMenu === 'users' ? 'active' : '' ?>" href="users.php">
      <span class="a-nav-icon">👥</span> Users
    </a>
    <a class="a-nav-link <?= $activeMenu === 'appointments' ? 'active' : '' ?>" href="appointments.php">
      <span class="a-nav-icon">📅</span> Appointment
    </a>
  </nav>
</aside>
