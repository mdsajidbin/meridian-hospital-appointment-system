<?php
require_once __DIR__ . '/includes/functions.php';
require_patient_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ? AND patient_id = ? AND status IN ('pending','confirmed')");
    $stmt->execute([$id, $_SESSION['user']['id']]);
    flash('success', 'Appointment cancelled.');
}
redirect('my-appointments.php');
