<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_admin()) { header('Location: index.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Session expired, please try again.';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role IN ('admin','staff')");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Invalid email or password.';
        } elseif ($user['status'] !== 'active') {
            $errors[] = 'This admin account has been disabled.';
        } else {
            login_user($user);
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | <?= HOSPITAL_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="a-login-wrap">
  <div class="a-login-card">
    <div class="logo">
      <img src="../assets/images/logo.png" alt="logo">
      <h3 style="margin:10px 0 0;">Admin Panel Login</h3>
      <p style="color:var(--a-muted);font-size:.85rem;">Meridian Hospital Chattogram</p>
    </div>
    <?php foreach ($errors as $e): ?><div class="alert" style="background:#fdeceb;color:#c0392b;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:.85rem;"><?= clean($e) ?></div><?php endforeach; ?>
    <form method="post" action="login.php">
      <?= csrf_field() ?>
      <div class="a-group" style="margin-bottom:14px;"><label>Gmail / Email</label><input type="email" name="email" required placeholder="you@gmail.com"></div>
      <div class="a-group" style="margin-bottom:20px;"><label>Password</label><input type="password" name="password" required></div>
      <button type="submit" class="a-btn" style="width:100%;justify-content:center;">Login to Dashboard</button>
    </form>
    <p style="text-align:center;margin-top:18px;font-size:.8rem;"><a href="../index.php" style="color:var(--a-primary);">← Back to website</a></p>
  </div>
</div>
</body>
</html>
