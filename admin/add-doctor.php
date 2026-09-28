<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin_login('login.php');
$activeMenu = 'add-doctor';
$breadcrumb = 'Add Doctor';

$mainCats = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name")->fetchAll();
$subCats  = $pdo->query("SELECT * FROM categories WHERE parent_id IS NOT NULL ORDER BY name")->fetchAll();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Session expired, please try again.';
    } else {
        $name = clean($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        if (!$name) $errors[] = 'Doctor name is required.';
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';

        $imagePath = null;
        $sigPath = null;

        if (!empty($_FILES['image']['name'])) {
            $imagePath = handle_doctor_upload($_FILES['image'], 'img');
            if ($imagePath === false) $errors[] = 'Doctor image upload failed (jpg/png only, max 3MB).';
        }
        if (!empty($_FILES['signature']['name'])) {
            $sigPath = handle_doctor_upload($_FILES['signature'], 'sig');
            if ($sigPath === false) $errors[] = 'Signature upload failed (jpg/png only, max 3MB).';
        }

        if (!$errors) {
            $stmt = $pdo->prepare("INSERT INTO doctors
              (category_id, sub_category_id, name, designation, image_path, signature_path, degree, higher_degree, phone, bmdc_number, email, work_place, password_hash, specialist_in, experience_years, fee, about, priority, status, editing_status)
              VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([
                $_POST['category_id'] ?? null ?: null,
                $_POST['sub_category_id'] ?? null ?: null,
                $name,
                clean($_POST['designation'] ?? ''),
                $imagePath,
                $sigPath,
                clean($_POST['degree'] ?? ''),
                clean($_POST['higher_degree'] ?? ''),
                clean($_POST['phone'] ?? ''),
                clean($_POST['bmdc_number'] ?? ''),
                $email,
                clean($_POST['work_place'] ?? 'Meridian Hospital Chattogram'),
                $password ? password_hash($password, PASSWORD_BCRYPT) : null,
                clean($_POST['specialist_in'] ?? ''),
                (int)($_POST['experience_years'] ?? 0),
                (float)($_POST['fee'] ?? 0),
                clean($_POST['about'] ?? ''),
                (int)($_POST['priority'] ?? 0),
                $_POST['status'] ?? 'pending',
                'unlocked',
            ]);
            $doctorId = $pdo->lastInsertId();

            // Availability
            $days = $_POST['days'] ?? [];
            $start = $_POST['start_time'] ?? '17:00';
            $end   = $_POST['end_time'] ?? '20:00';
            $duration = (int)($_POST['consultation_duration'] ?? 10);
            if ($days) {
                $ains = $pdo->prepare("INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, consultation_duration) VALUES (?,?,?,?,?)");
                foreach ($days as $day) {
                    if (in_array($day, ['Sat','Sun','Mon','Tue','Wed','Thu','Fri'], true)) {
                        $ains->execute([$doctorId, $day, $start, $end, $duration ?: 10]);
                    }
                }
            }
            $success = true;
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
  <h1 class="a-page-title">Add Doctor</h1>

  <div class="a-card">
    <?php if ($success): ?><div class="alert alert-success" style="background:#e7f9ef;color:#0f7a45;padding:12px 16px;border-radius:8px;margin-bottom:16px;">Doctor added successfully. <a href="doctors.php">View Doctor List →</a></div><?php endif; ?>
    <?php foreach ($errors as $e): ?><div class="alert alert-error" style="background:#fdeceb;color:#c0392b;padding:12px 16px;border-radius:8px;margin-bottom:16px;"><?= clean($e) ?></div><?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <h3>Basic Information</h3>
      <div class="a-form-grid">
        <div class="a-group"><label>Category</label>
          <select name="category_id" id="categorySel">
            <option value="">Select Category</option>
            <?php foreach ($mainCats as $c): ?><option value="<?= $c['id'] ?>"><?= clean($c['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="a-group"><label>Sub Category</label>
          <select name="sub_category_id" id="subCategorySel">
            <option value="">Select Sub Category</option>
            <?php foreach ($subCats as $s): ?><option value="<?= $s['id'] ?>"><?= clean($s['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="a-group"><label>Name</label><input name="name" required></div>
        <div class="a-group"><label>Designation</label><input name="designation"></div>
        <div class="a-group"><label>Image</label><input type="file" name="image" accept="image/*"></div>
        <div class="a-group"><label>Signature</label><input type="file" name="signature" accept="image/*"></div>
        <div class="a-group"><label>Degree</label><input name="degree"></div>
        <div class="a-group"><label>Higher Degree</label><input name="higher_degree"></div>
        <div class="a-group"><label>Phone</label><input name="phone"></div>
        <div class="a-group"><label>BMDC Number</label><input name="bmdc_number"></div>
        <div class="a-group"><label>Email</label><input type="email" name="email" required></div>
        <div class="a-group"><label>Work Place</label><input name="work_place" value="Meridian Hospital Chattogram"></div>
        <div class="a-group"><label>Password (doctor login, optional)</label><input type="password" name="password"></div>
        <div class="a-group"><label>Specialist In</label><input name="specialist_in"></div>
        <div class="a-group"><label>Doctor Experience (years)</label><input type="number" name="experience_years" min="0"></div>
        <div class="a-group"><label>Doctor Charge / Fee (৳)</label><input type="number" name="fee" min="0" step="0.01"></div>
        <div class="a-group"><label>Categorize Priority</label><input type="number" name="priority" value="0"></div>
        <div class="a-group"><label>Status</label>
          <select name="status">
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="hold">Hold</option>
            <option value="banned">Banned</option>
          </select>
        </div>
      </div>
      <div class="a-group" style="margin-top:16px;"><label>About Doctor</label><textarea name="about" rows="4"></textarea></div>

      <h3 style="margin-top:26px;">Appointment Availability</h3>
      <div class="a-group">
        <label>Available On</label>
        <div class="a-checks">
          <?php foreach (['Sat','Sun','Mon','Tue','Wed','Thu','Fri'] as $d): ?>
            <label><input type="checkbox" name="days[]" value="<?= $d ?>"> <?= $d ?></label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="a-form-grid" style="margin-top:14px;">
        <div class="a-group"><label>Starting Time</label><input type="time" name="start_time" value="17:00"></div>
        <div class="a-group"><label>Ending Time</label><input type="time" name="end_time" value="20:00"></div>
        <div class="a-group"><label>Consultation Time (minutes)</label><input type="number" name="consultation_duration" value="10" min="5"></div>
      </div>

      <button type="submit" class="a-btn" style="margin-top:24px;">Submit</button>
    </form>
  </div>
</div>
<script>
  // Simple category->subcategory relationship isn't enforced server-side beyond FK;
  // this just keeps the UI tidy (no filtering data available client-side in this lean build).
</script>
<?php include __DIR__ . '/includes/foot.php'; ?>
