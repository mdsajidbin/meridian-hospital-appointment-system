<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin_login('login.php');
$activeMenu = 'doctors';
$breadcrumb = 'Edit Doctor';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->execute([$id]);
$doctor = $stmt->fetch();
if (!$doctor) { header('Location: doctors.php'); exit; }

$mainCats = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name")->fetchAll();
$subCats  = $pdo->query("SELECT * FROM categories WHERE parent_id IS NOT NULL ORDER BY name")->fetchAll();

$avStmt = $pdo->prepare("SELECT * FROM doctor_availability WHERE doctor_id = ?");
$avStmt->execute([$id]);
$availability = $avStmt->fetchAll();
$selectedDays = array_column($availability, 'day_of_week');
$firstAv = $availability[0] ?? ['start_time' => '17:00:00', 'end_time' => '20:00:00', 'consultation_duration' => 10];

$errors = [];
$success = false;

if ($doctor['editing_status'] === 'locked') {
    $errors[] = 'This doctor profile is locked for editing. Unlock it from the Doctor List first.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$errors) {
    if (!verify_csrf()) {
        $errors[] = 'Session expired, please try again.';
    } else {
        $name = clean($_POST['name'] ?? '');
        if (!$name) $errors[] = 'Doctor name is required.';

        $imagePath = $doctor['image_path'];
        $sigPath = $doctor['signature_path'];
        if (!empty($_FILES['image']['name'])) {
            $up = handle_doctor_upload($_FILES['image'], 'img');
            if ($up) $imagePath = $up;
        }
        if (!empty($_FILES['signature']['name'])) {
            $up = handle_doctor_upload($_FILES['signature'], 'sig');
            if ($up) $sigPath = $up;
        }

        if (!$errors) {
            $stmt = $pdo->prepare("UPDATE doctors SET
              category_id=?, sub_category_id=?, name=?, designation=?, image_path=?, signature_path=?, degree=?, higher_degree=?,
              phone=?, bmdc_number=?, email=?, work_place=?, specialist_in=?, experience_years=?, fee=?, about=?, priority=?, status=?
              WHERE id=?");
            $stmt->execute([
                $_POST['category_id'] ?? null ?: null, $_POST['sub_category_id'] ?? null ?: null, $name,
                clean($_POST['designation'] ?? ''), $imagePath, $sigPath,
                clean($_POST['degree'] ?? ''), clean($_POST['higher_degree'] ?? ''),
                clean($_POST['phone'] ?? ''), clean($_POST['bmdc_number'] ?? ''),
                strtolower(trim($_POST['email'] ?? '')), clean($_POST['work_place'] ?? ''),
                clean($_POST['specialist_in'] ?? ''), (int)($_POST['experience_years'] ?? 0),
                (float)($_POST['fee'] ?? 0), clean($_POST['about'] ?? ''), (int)($_POST['priority'] ?? 0),
                $_POST['status'] ?? $doctor['status'], $id,
            ]);

            $pdo->prepare("DELETE FROM doctor_availability WHERE doctor_id = ?")->execute([$id]);
            $days = $_POST['days'] ?? [];
            $start = $_POST['start_time'] ?? '17:00';
            $end   = $_POST['end_time'] ?? '20:00';
            $duration = (int)($_POST['consultation_duration'] ?? 10);
            if ($days) {
                $ains = $pdo->prepare("INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, consultation_duration) VALUES (?,?,?,?,?)");
                foreach ($days as $day) {
                    if (in_array($day, ['Sat','Sun','Mon','Tue','Wed','Thu','Fri'], true)) {
                        $ains->execute([$id, $day, $start, $end, $duration ?: 10]);
                    }
                }
            }
            $success = true;
            $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
            $stmt->execute([$id]);
            $doctor = $stmt->fetch();
        }
    }
}

function handle_doctor_upload(array $file, string $prefix) {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > 3 * 1024 * 1024) return false;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) return false;
    if (!is_dir(DOCTOR_UPLOAD_DIR)) mkdir(DOCTOR_UPLOAD_DIR, 0755, true);
    $filename = $prefix . '_' . uniqid() . '.' . $ext;
    move_uploaded_file($file['tmp_name'], DOCTOR_UPLOAD_DIR . $filename);
    return 'uploads/doctors/' . $filename;
}

include __DIR__ . '/includes/head.php';
?>
<div class="a-content">
  <h1 class="a-page-title">Edit Doctor — <?= clean($doctor['name']) ?></h1>

  <div class="a-card">
    <?php if ($success): ?><div class="alert" style="background:#e7f9ef;color:#0f7a45;padding:12px 16px;border-radius:8px;margin-bottom:16px;">Doctor updated successfully.</div><?php endif; ?>
    <?php foreach ($errors as $e): ?><div class="alert" style="background:#fdeceb;color:#c0392b;padding:12px 16px;border-radius:8px;margin-bottom:16px;"><?= clean($e) ?></div><?php endforeach; ?>

    <?php if ($doctor['editing_status'] !== 'locked'): ?>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$doctor['id'] ?>">
      <h3>Basic Information</h3>
      <div class="a-form-grid">
        <div class="a-group"><label>Category</label>
          <select name="category_id">
            <option value="">Select Category</option>
            <?php foreach ($mainCats as $c): ?><option value="<?= $c['id'] ?>" <?= $c['id']==$doctor['category_id']?'selected':'' ?>><?= clean($c['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="a-group"><label>Sub Category</label>
          <select name="sub_category_id">
            <option value="">Select Sub Category</option>
            <?php foreach ($subCats as $s): ?><option value="<?= $s['id'] ?>" <?= $s['id']==$doctor['sub_category_id']?'selected':'' ?>><?= clean($s['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="a-group"><label>Name</label><input name="name" value="<?= clean($doctor['name']) ?>" required></div>
        <div class="a-group"><label>Designation</label><input name="designation" value="<?= clean($doctor['designation']) ?>"></div>
        <div class="a-group"><label>Image <?= $doctor['image_path'] ? '(uploaded)' : '' ?></label><input type="file" name="image" accept="image/*"></div>
        <div class="a-group"><label>Signature</label><input type="file" name="signature" accept="image/*"></div>
        <div class="a-group"><label>Degree</label><input name="degree" value="<?= clean($doctor['degree']) ?>"></div>
        <div class="a-group"><label>Higher Degree</label><input name="higher_degree" value="<?= clean($doctor['higher_degree']) ?>"></div>
        <div class="a-group"><label>Phone</label><input name="phone" value="<?= clean($doctor['phone']) ?>"></div>
        <div class="a-group"><label>BMDC Number</label><input name="bmdc_number" value="<?= clean($doctor['bmdc_number']) ?>"></div>
        <div class="a-group"><label>Email</label><input type="email" name="email" value="<?= clean($doctor['email']) ?>"></div>
        <div class="a-group"><label>Work Place</label><input name="work_place" value="<?= clean($doctor['work_place']) ?>"></div>
        <div class="a-group"><label>Specialist In</label><input name="specialist_in" value="<?= clean($doctor['specialist_in']) ?>"></div>
        <div class="a-group"><label>Doctor Experience (years)</label><input type="number" name="experience_years" value="<?= (int)$doctor['experience_years'] ?>"></div>
        <div class="a-group"><label>Doctor Charge / Fee (৳)</label><input type="number" name="fee" step="0.01" value="<?= $doctor['fee'] ?>"></div>
        <div class="a-group"><label>Categorize Priority</label><input type="number" name="priority" value="<?= (int)$doctor['priority'] ?>"></div>
        <div class="a-group"><label>Status</label>
          <select name="status">
            <?php foreach (['pending','approved','hold','banned'] as $s): ?>
              <option value="<?= $s ?>" <?= $doctor['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="a-group" style="margin-top:16px;"><label>About Doctor</label><textarea name="about" rows="4"><?= clean($doctor['about']) ?></textarea></div>

      <h3 style="margin-top:26px;">Appointment Availability</h3>
      <div class="a-group">
        <label>Available On</label>
        <div class="a-checks">
          <?php foreach (['Sat','Sun','Mon','Tue','Wed','Thu','Fri'] as $d): ?>
            <label><input type="checkbox" name="days[]" value="<?= $d ?>" <?= in_array($d,$selectedDays,true)?'checked':'' ?>> <?= $d ?></label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="a-form-grid" style="margin-top:14px;">
        <div class="a-group"><label>Starting Time</label><input type="time" name="start_time" value="<?= substr($firstAv['start_time'],0,5) ?>"></div>
        <div class="a-group"><label>Ending Time</label><input type="time" name="end_time" value="<?= substr($firstAv['end_time'],0,5) ?>"></div>
        <div class="a-group"><label>Consultation Time (minutes)</label><input type="number" name="consultation_duration" value="<?= (int)$firstAv['consultation_duration'] ?>"></div>
      </div>

      <button type="submit" class="a-btn" style="margin-top:24px;">Save Changes</button>
      <a href="doctors.php" class="a-btn a-btn-outline" style="margin-top:24px;">Cancel</a>
    </form>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/includes/foot.php'; ?>
