<?php
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json');

$doctorId = (int)($_GET['doctor_id'] ?? 0);
$date = $_GET['date'] ?? '';

if (!$doctorId || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['slots' => []]);
    exit;
}

$slots = build_time_slots($pdo, $doctorId, $date);
echo json_encode(['slots' => $slots]);
