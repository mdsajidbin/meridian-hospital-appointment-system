<?php
/**
 * Meridian Hospital Chattogram — Doctor CSV importer
 *
 * Re-imports doctors from database/data/doctors.csv and sub-categories
 * from database/data/subcategories.csv into the live database.
 * All imported doctors default to category "Specialist" + a matching
 * sub-category, status "approved", editing_status "unlocked".
 *
 * Usage (command line, on your server):
 *   php database/import_doctors.php
 *
 * Note: schema.sql + seed_doctors.sql already contain a ready-made
 * import of the CSV that shipped with this project. Use this script
 * only if you replace database/data/doctors.csv with a NEW export
 * and want to re-run the import without re-installing the schema.
 */

require_once __DIR__ . '/../config/db.php';

function to24($t) {
    $t = trim($t);
    if (preg_match('/(\d{1,2}):(\d{2})\s*([AaPp][Mm])/', $t, $m)) {
        $h = (int)$m[1]; $mi = (int)$m[2]; $ap = strtoupper($m[3]);
        if ($ap === 'PM' && $h !== 12) $h += 12;
        if ($ap === 'AM' && $h === 12) $h = 0;
        return sprintf('%02d:%02d:00', $h, $mi);
    }
    return '09:00:00';
}
function parseMinutes($s) {
    return preg_match('/(\d+)/', $s, $m) ? (int)$m[1] : 10;
}

// 1. Ensure "Specialist" parent category exists
$stmt = $pdo->prepare("SELECT id FROM categories WHERE name = 'Specialist' AND parent_id IS NULL");
$stmt->execute();
$specialistId = $stmt->fetchColumn();
if (!$specialistId) {
    $pdo->prepare("INSERT INTO categories (name, parent_id) VALUES ('Specialist', NULL)")->execute();
    $specialistId = $pdo->lastInsertId();
}

// 2. Import sub-categories
$subMap = [];
$existing = $pdo->query("SELECT id, name FROM categories WHERE parent_id = $specialistId")->fetchAll();
foreach ($existing as $row) $subMap[$row['name']] = $row['id'];

if (($fh = fopen(__DIR__ . '/data/subcategories.csv', 'r')) !== false) {
    $header = fgetcsv($fh);
    while (($row = fgetcsv($fh)) !== false) {
        $name = trim($row[1] ?? '');
        if ($name === '' || isset($subMap[$name])) continue;
        $ins = $pdo->prepare("INSERT INTO categories (name, parent_id) VALUES (?, ?)");
        $ins->execute([$name, $specialistId]);
        $subMap[$name] = $pdo->lastInsertId();
    }
    fclose($fh);
}

// 3. Import doctors
$imported = 0;
$doctorImages = array_map(fn($i) => "doc$i.png", range(1, 15));

if (($fh = fopen(__DIR__ . '/data/doctors.csv', 'r')) !== false) {
    $header = fgetcsv($fh);
    $i = 0;
    while (($row = fgetcsv($fh)) !== false) {
        if (count($row) < 27) continue;
        [$sl, $status, $editing, $name, $category, $subcategory, $designation, $degree,
         $higherDegree, $bmdc, $phone, $email, $workplace, $specialist, $experience, $charge,
         $image, $signature, $priority, $about, $availableOn, $startTime, $endTime, $fee,
         $consultTime, $appWeb, $appApp] = array_slice($row, 0, 27);

        $name = trim($name);
        if ($name === '') continue;

        $subId = $subMap[trim($subcategory)] ?? null;
        $img = 'assets/images/doctors/' . $doctorImages[$i % count($doctorImages)];
        $i++;

        preg_match('/(\d+)/', $experience, $em);
        $expYears = $em[1] ?? 0;
        $feeVal = preg_replace('/[^\d.]/', '', $fee) ?: 0;
        $priorityVal = preg_replace('/[^\d]/', '', $priority) ?: 0;
        $aboutClean = trim($about);
        if (in_array($aboutClean, ['.....', '', '-'], true)) {
            $aboutClean = "$name is a member of the medical team at Meridian Hospital Chattogram.";
        }

        $ins = $pdo->prepare("INSERT INTO doctors
          (category_id, sub_category_id, name, designation, image_path, degree, higher_degree, phone, bmdc_number,
           email, work_place, specialist_in, experience_years, fee, about, priority, status, editing_status)
          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $ins->execute([
            $specialistId, $subId, $name, trim($designation), $img, trim($degree), trim($higherDegree),
            trim($phone), trim($bmdc), trim($email), trim($workplace) ?: 'Meridian Hospital Chattogram',
            trim($specialist), $expYears, $feeVal, $aboutClean, $priorityVal, 'approved', 'unlocked',
        ]);
        $doctorId = $pdo->lastInsertId();

        $days = array_filter(array_map('trim', explode(',', $availableOn)));
        $start = $startTime ? to24($startTime) : '17:00:00';
        $end   = $endTime ? to24($endTime) : '20:00:00';
        $duration = $consultTime ? parseMinutes($consultTime) : 10;
        $validDays = ['Sat','Sun','Mon','Tue','Wed','Thu','Fri'];

        foreach ($days as $day) {
            $day = ucfirst(strtolower(substr($day, 0, 3)));
            if (!in_array($day, $validDays, true)) continue;
            $pdo->prepare("INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, consultation_duration) VALUES (?,?,?,?,?)")
                ->execute([$doctorId, $day, $start, $end, $duration]);
        }
        $imported++;
    }
    fclose($fh);
}

echo "Import complete. $imported doctors imported.\n";
