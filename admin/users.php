<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin_login('login.php');
$activeMenu = 'users';
$breadcrumb = 'Users';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_staff') {
        $name = clean($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $role = in_array($_POST['role'] ?? '', ['admin','staff'], true) ? $_POST['role'] : 'staff';

        if (!$name || !$email || strlen($password) < 6) {
            $errors[] = 'Name, valid email and a password (min 6 chars) are required.';
        } else {
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                $errors[] = 'A user with this email already exists.';
            } else {
                $pdo->beginTransaction();
                $ins = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
                $ins->execute([$name, $email, password_hash($password, PASSWORD_BCRYPT), $role]);
                $uid = $pdo->lastInsertId();
                $pdo->prepare("INSERT INTO admins (user_id, permission_level) VALUES (?, ?)")->execute([$uid, $role === 'admin' ? 'admin' : 'staff']);
                $pdo->commit();
                $success = true;
            }
        }
    } elseif ($action === 'toggle_status') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id !== (int)current_user()['id']) {
            $pdo->prepare("UPDATE users SET status = CASE WHEN status = 'active' THEN 'disabled' ELSE 'active' END WHERE id = ?")->execute([$id]);
        }
        $success = true;
    }
}

$staffUsers = $pdo->query("SELECT u.*, a.permission_level FROM users u LEFT JOIN admins a ON a.user_id = u.id WHERE u.role IN ('admin','staff') ORDER BY u.id")->fetchAll();

include __DIR__ . '/includes/head.php';
?>
<div class="a-content">
  <h1 class="a-page-title">Users (Admin &amp; Staff Accounts)</h1>

  <?php if ($success): ?><div class="alert" style="background:#e7f9ef;color:#0f7a45;padding:12px 16px;border-radius:8px;margin-bottom:16px;">Saved successfully.</div><?php endif; ?>
  <?php foreach ($errors as $e): ?><div class="alert" style="background:#fdeceb;color:#c0392b;padding:12px 16px;border-radius:8px;margin-bottom:16px;"><?= clean($e) ?></div><?php endforeach; ?>

  <div class="a-card">
    <h3>Add Admin / Staff User</h3>
    <form method="post" class="a-form-grid">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add_staff">
      <div class="a-group"><label>Full Name</label><input name="name" required></div>
      <div class="a-group"><label>Gmail / Email</label><input type="email" name="email" required></div>
      <div class="a-group"><label>Password</label><input type="password" name="password" required></div>
      <div class="a-group"><label>Role</label>
        <select name="role"><option value="staff">Staff</option><option value="admin">Admin</option></select>
      </div>
      <div class="a-group" style="align-self:end;"><button type="submit" class="a-btn">+ Add User</button></div>
    </form>
  </div>

  <div class="a-card">
    <h3>Platform Users</h3>
    <div class="a-table-wrap">
      <table class="a-table">
        <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Permission</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($staffUsers as $u): ?>
            <tr>
              <td><?= $u['id'] ?></td>
              <td><?= clean($u['name']) ?></td>
              <td><?= clean($u['email']) ?></td>
              <td><?= ucfirst($u['role']) ?></td>
              <td><?= clean($u['permission_level'] ?? '-') ?></td>
              <td><?= $u['status'] === 'active' ? status_badge('approved') : status_badge('banned') ?></td>
              <td><?= date('j M Y', strtotime($u['created_at'])) ?></td>
              <td>
                <form method="post" style="display:inline;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle_status">
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <button class="a-btn a-btn-outline a-btn-sm" type="submit" <?= $u['id']==current_user()['id']?'disabled':'' ?>>
                    <?= $u['status']==='active' ? 'Disable' : 'Enable' ?>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/foot.php'; ?>
