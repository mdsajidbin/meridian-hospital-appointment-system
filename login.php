<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Login';

if (is_patient()) redirect('my-appointments.php');

$errors = [];
$info = flash('info');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Session expired, please try again.';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'patient'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Invalid email or password.';
        } elseif ($user['status'] !== 'active') {
            $errors[] = 'Your account has been disabled. Please contact the hospital.';
        } else {
            login_user($user);
            if (!empty($_SESSION['redirect_after_login'])) {
                $to = $_SESSION['redirect_after_login']; unset($_SESSION['redirect_after_login']);
                redirect($to);
            }
            redirect('my-appointments.php');
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<section class="auth-wrap">
  <div class="auth-card" data-aos="fade-up">
    <h2 class="text-center">Welcome Back</h2>
    <p class="muted text-center" style="margin-bottom:24px;">Log in to manage your appointments.</p>

    <?php if ($info): ?><div class="alert alert-success"><?= clean($info) ?></div><?php endif; ?>
    <?php foreach ($errors as $e): ?><div class="alert alert-error"><?= clean($e) ?></div><?php endforeach; ?>

    <form method="post" action="login.php">
      <?= csrf_field() ?>
      <div class="form-group"><label>Email</label><input type="email" class="form-control" name="email" required></div>
      <div class="form-group"><label>Password</label><input type="password" class="form-control" name="password" required></div>
      <button type="submit" class="btn btn-primary btn-block magnetic">Login</button>
    </form>
    <p class="text-center muted" style="margin-top:18px;">New here? <a href="register.php" style="color:var(--primary);font-weight:600;">Create an account</a></p>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
