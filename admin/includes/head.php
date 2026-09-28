<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= clean($breadcrumb ?? 'Admin') ?> | <?= HOSPITAL_NAME ?> Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
<link rel="icon" href="<?= BASE_URL ?>assets/images/logo.png">
</head>
<body>
<div class="admin-wrap">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="a-main">
    <?php include __DIR__ . '/topbar.php'; ?>
