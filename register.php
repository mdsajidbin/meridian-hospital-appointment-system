<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Create Account';

if (is_patient()) redirect('my-appointments.php');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Session expired, please try again.';
    } else {
        $name  = clean($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = clean($_POST['phone'] ?? '');
        $age   = (int)($_POST['age'] ?? 0);
        $gender = clean($_POST['gender'] ?? '');
        $address = clean($_POST['address'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (!$name || !$email || !$password) $errors[] = 'Name, email and password are required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirm) $errors[] = 'Passwords do not match.';

        if (!$errors) {
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                $errors[] = 'An account with this email already exists.';
            } else {
                $pdo->beginTransaction();
                $ins = $pdo->prepare("INSERT INTO users (name, email, password_hash, phone, role) VALUES (?, ?, ?, ?, 'patient')");
                $ins->execute([$name, $email, password_hash($password, PASSWORD_BCRYPT), $phone]);
                $userId = $pdo->lastInsertId();
                $pins = $pdo->prepare("INSERT INTO patients (user_id, age, gender, address) VALUES (?, ?, ?, ?)");
                $pins->execute([$userId, $age ?: null, $gender ?: null, $address ?: null]);
                $pdo->commit();

                login_user(['id' => $userId, 'name' => $name, 'email' => $email, 'role' => 'patient']);

                if (!empty($_SESSION['redirect_after_login'])) {
                    $to = $_SESSION['redirect_after_login']; unset($_SESSION['redirect_after_login']);
                    redirect($to);
                }
                redirect('my-appointments.php');
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<section class="auth-wrap">
  <div class="auth-card" data-aos="fade-up">
    <h2 class="text-center">Create Account</h2>
    <p class="muted text-center" style="margin-bottom:24px;">Sign up to book appointments with our doctors.</p>

    <?php foreach ($errors as $e): ?><div class="alert alert-error"><?= clean($e) ?></div><?php endforeach; ?>

    <form method="post" action="register.php">
      <?= csrf_field() ?>
      <div class="form-group"><label>Full Name</label><input class="form-control" name="name" required value="<?= clean($_POST['name'] ?? '') ?>"></div>
      <div class="form-group"><label>Email</label><input type="email" class="form-control" name="email" required value="<?= clean($_POST['email'] ?? '') ?>"></div>
      <div class="form-row">
        <div class="form-group"><label>Phone</label><input class="form-control" name="phone" value="<?= clean($_POST['phone'] ?? '') ?>"></div>
        <div class="form-group"><label>Age</label><input type="number" class="form-control" name="age" min="0" value="<?= clean($_POST['age'] ?? '') ?>"></div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Gender</label>
          <select class="form-control" name="gender">
            <option value="">Select</option>
            <option>Male</option><option>Female</option><option>Other</option>
          </select>
        </div>
        <div class="form-group"><label>Address</label><input class="form-control" name="address" value="<?= clean($_POST['address'] ?? '') ?>"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Password</label><input type="password" class="form-control" name="password" required></div>
        <div class="form-group"><label>Confirm Password</label><input type="password" class="form-control" name="confirm_password" required></div>
      </div>
      <button type="submit" class="btn btn-primary btn-block magnetic">Create Account</button>
    </form>
    <p class="text-center muted" style="margin-top:18px;">Already have an account? <a href="login.php" style="color:var(--primary);font-weight:600;">Log in</a></p>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
