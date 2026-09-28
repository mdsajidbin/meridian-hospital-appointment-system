<?php
/**
 * Meridian Hospital Chattogram — Global configuration
 */

// ---- Session (single session namespace holds both patient & admin logins,
//      distinguished by $_SESSION['role']) ----
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---- Base URL ----
// Derive the application mount point from the current script so the site
// works both at the web root and inside a folder (for example /hospital/).
$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
$scriptDir = rtrim(dirname($scriptPath), '/');
if (basename($scriptDir) === 'admin') {
    $scriptDir = rtrim(dirname($scriptDir), '/');
}
define('BASE_URL', ($scriptDir === '' || $scriptDir === '.') ? '/' : $scriptDir . '/');

// ---- Hospital information (used across the site & footer) ----
define('HOSPITAL_NAME', 'Meridian Hospital Chattogram');
define('HOSPITAL_TAGLINE', 'Serving with Care & Compassion');
define('HOSPITAL_ADDRESS', '1367 CDA Avenue, GEC Circle, Chattogram, Bangladesh');
define('HOSPITAL_PHONE_MAIN', '01622295857 / 01307284064');
define('HOSPITAL_PHONE_EMERGENCY', '01879470036');
define('HOSPITAL_PHONE_APPOINTMENT', '01622295857');
define('HOSPITAL_EMAIL', 'Mohammadsajid1114@gmail.com');
define('HOSPITAL_HOURS', '24/7');
define('HOSPITAL_MAP_URL', 'https://share.google/2tXECFFl40rvFYG1w');

// ---- Uploads ----
define('DOCTOR_UPLOAD_DIR', __DIR__ . '/../uploads/doctors/');
define('DOCTOR_UPLOAD_URL', BASE_URL . 'uploads/doctors/');

// ---- Error display (turn off in production) ----
ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Dhaka');
