<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin_login('login.php');

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $pdo->prepare("DELETE FROM doctors WHERE id = ?")->execute([$id]);
}
header('Location: doctors.php');
exit;
