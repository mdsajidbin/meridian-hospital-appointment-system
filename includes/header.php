<?php
/**
 * Shared <head> + navbar for all patient-facing pages.
 * Expects (optional) $pageTitle to be set before include.
 */
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? HOSPITAL_NAME;
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= clean($pageTitle) ?> | <?= HOSPITAL_NAME ?></title>
<meta name="description" content="Book appointments with trusted doctors at Meridian Hospital Chattogram — 24/7 care, serving with care & compassion.">
<link rel="icon" href="<?= BASE_URL ?>assets/images/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<header class="navbar">
  <div class="container">
    <a href="<?= BASE_URL ?>index.php" class="nav-logo">
      <img src="<?= BASE_URL ?>assets/images/logo.png" alt="<?= HOSPITAL_NAME ?> logo">
      <span>Meridian Hospital<br><small style="font-weight:400;font-size:.65rem;color:var(--text-muted)">Chattogram</small></span>
    </a>
    <nav class="nav-links" id="navLinks">
      <a href="<?= BASE_URL ?>index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Home</a>
      <a href="<?= BASE_URL ?>doctors.php" class="<?= $currentPage === 'doctors.php' ? 'active' : '' ?>">All Doctors</a>
      <a href="<?= BASE_URL ?>about.php" class="<?= $currentPage === 'about.php' ? 'active' : '' ?>">About</a>
      <a href="<?= BASE_URL ?>contact.php" class="<?= $currentPage === 'contact.php' ? 'active' : '' ?>">Contact</a>
      <?php if (is_patient()): ?>
        <a href="<?= BASE_URL ?>my-appointments.php" class="<?= $currentPage === 'my-appointments.php' ? 'active' : '' ?>">My Appointments</a>
      <?php endif; ?>
      <a href="<?= BASE_URL ?>admin/login.php" class="nav-admin-link">Admin Panel</a>
    </nav>
    <div class="nav-actions">
      <?php if (is_patient()): ?>
        <span class="user-chip">👤 <?= clean(explode(' ', current_user()['name'])[0]) ?></span>
        <a href="<?= BASE_URL ?>logout.php" class="btn btn-outline btn-sm">Logout</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>login.php" class="btn btn-outline btn-sm">Login</a>
        <a href="<?= BASE_URL ?>register.php" class="btn btn-primary btn-sm magnetic">Create Account</a>
      <?php endif; ?>
      <button class="menu-toggle" aria-label="Menu">☰</button>
    </div>
  </div>
</header>
