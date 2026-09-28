<?php
/**
 * Shared helper functions: auth, CSRF, sanitization, formatting.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

// ---------------------------------------------------------------
// CSRF
// ---------------------------------------------------------------
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf(): bool {
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

// ---------------------------------------------------------------
// Sanitization
// ---------------------------------------------------------------
function clean($str): string {
    return htmlspecialchars(trim($str ?? ''), ENT_QUOTES, 'UTF-8');
}

// ---------------------------------------------------------------
// Auth: patients (role=patient) & admins (role=admin/staff) share
// the same `users` table and the same PHP session, distinguished
// by $_SESSION['role']. Admin panel pages check for role=admin/staff
// specifically, giving a separate, role-based access boundary.
// ---------------------------------------------------------------
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
    return isset($_SESSION['user']);
}

function is_patient(): bool {
    return is_logged_in() && $_SESSION['user']['role'] === 'patient';
}

function is_admin(): bool {
    return is_logged_in() && in_array($_SESSION['user']['role'], ['admin', 'staff'], true);
}

function require_patient_login(string $redirect = 'login.php'): void {
    if (!is_patient()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        header('Location: ' . $redirect);
        exit;
    }
}

function require_admin_login(string $redirect = 'login.php'): void {
    if (!is_admin()) {
        header('Location: ' . $redirect);
        exit;
    }
}

function login_user(array $userRow): void {
    $_SESSION['user'] = [
        'id'    => (int)$userRow['id'],
        'name'  => $userRow['name'],
        'email' => $userRow['email'],
        'role'  => $userRow['role'],
    ];
}

function logout_user(): void {
    unset($_SESSION['user']);
    session_regenerate_id(true);
}

// ---------------------------------------------------------------
// Formatting helpers
// ---------------------------------------------------------------
function format_money($n): string {
    return '৳' . number_format((float)$n, 0);
}

function status_badge(string $status): string {
    $map = [
        'approved'  => 'badge-green',
        'pending'   => 'badge-yellow',
        'hold'      => 'badge-orange',
        'banned'    => 'badge-red',
        'confirmed' => 'badge-green',
        'completed' => 'badge-blue',
        'cancelled' => 'badge-red',
    ];
    $cls = $map[$status] ?? 'badge-gray';
    return '<span class="status-badge ' . $cls . '">' . ucfirst($status) . '</span>';
}

function doctor_image_url(?string $path): string {
    if (!$path) return BASE_URL . 'assets/images/doctors/doc1.png';
    return BASE_URL . ltrim($path, '/');
}

function day_short(string $date): string {
    return date('D', strtotime($date));
}

/**
 * Generate the next 7 selectable appointment days starting today,
 * used for the doctor profile "date tab" picker.
 */
function next_days(int $count = 7): array {
    $days = [];
    for ($i = 0; $i < $count; $i++) {
        $ts = strtotime("+{$i} day");
        $days[] = [
            'date'      => date('Y-m-d', $ts),
            'day_label' => date('D', $ts),
            'day_num'   => date('j', $ts),
            'month'     => date('M', $ts),
        ];
    }
    return $days;
}

/**
 * Build the list of bookable time slots for a doctor on a given
 * day-of-week from their doctor_availability rows.
 */
function build_time_slots(PDO $pdo, int $doctorId, string $date): array {
    $dow = date('D', strtotime($date)); // Sat/Sun/Mon/...

    $stmt = $pdo->prepare("SELECT * FROM doctor_availability WHERE doctor_id = ? AND day_of_week = ?");
    $stmt->execute([$doctorId, $dow]);
    $availability = $stmt->fetchAll();

    if (!$availability) return [];

    // fetch already booked times for that doctor/date
    $bstmt = $pdo->prepare("SELECT appointment_time FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND status != 'cancelled'");
    $bstmt->execute([$doctorId, $date]);
    $booked = array_column($bstmt->fetchAll(), 'appointment_time');

    $slots = [];
    foreach ($availability as $slot) {
        $start = strtotime($slot['start_time']);
        $end   = strtotime($slot['end_time']);
        $dur   = max(5, (int)$slot['consultation_duration']) * 60;

        for ($t = $start; $t < $end; $t += $dur) {
            $label = date('g:i A', $t);
            $slots[] = [
                'time'      => $label,
                'available' => !in_array($label, $booked, true),
            ];
        }
    }
    return $slots;
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function flash(string $key, ?string $msg = null) {
    if ($msg !== null) {
        $_SESSION['flash'][$key] = $msg;
        return null;
    }
    $val = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $val;
}
