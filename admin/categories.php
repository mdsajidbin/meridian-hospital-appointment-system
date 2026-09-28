<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin_login('login.php');
$activeMenu = 'categories';
$breadcrumb = 'Categories';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = clean($_POST['name'] ?? '');
        $parent = $_POST['parent_id'] ?: null;
        if (!$name) {
            $errors[] = 'Category name is required.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name, parent_id) VALUES (?, ?)");
            $stmt->execute([$name, $parent]);
            $success = true;
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        // Detach doctors first so FK ON DELETE SET NULL applies cleanly, then remove category
        $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
        $success = true;
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = clean($_POST['name'] ?? '');
        if ($name) {
            $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?")->execute([$name, $id]);
            $success = true;
        }
    }
}

$mainCats = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name")->fetchAll();
$allCats = $pdo->query("
  SELECT c.*, p.name AS parent_name,
    (SELECT COUNT(*) FROM doctors d WHERE d.category_id = c.id OR d.sub_category_id = c.id) AS doctor_count
  FROM categories c LEFT JOIN categories p ON p.id = c.parent_id
  ORDER BY (c.parent_id IS NULL) DESC, p.name, c.name
")->fetchAll();

include __DIR__ . '/includes/head.php';
?>
<div class="a-content">
  <h1 class="a-page-title">Categories</h1>

  <?php if ($success): ?><div class="alert" style="background:#e7f9ef;color:#0f7a45;padding:12px 16px;border-radius:8px;margin-bottom:16px;">Saved successfully.</div><?php endif; ?>
  <?php foreach ($errors as $e): ?><div class="alert" style="background:#fdeceb;color:#c0392b;padding:12px 16px;border-radius:8px;margin-bottom:16px;"><?= clean($e) ?></div><?php endforeach; ?>

  <div class="a-card">
    <h3>Add Main Category / Sub Category</h3>
    <form method="post" style="display:flex;gap:14px;flex-wrap:wrap;align-items:flex-end;">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <div class="a-group" style="min-width:220px;"><label>Name</label><input name="name" required></div>
      <div class="a-group" style="min-width:220px;">
        <label>Parent (leave blank for a Main Category)</label>
        <select name="parent_id">
          <option value="">— None (Main Category) —</option>
          <?php foreach ($mainCats as $c): ?><option value="<?= $c['id'] ?>"><?= clean($c['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="a-btn">+ Add</button>
    </form>
  </div>

  <div class="a-card">
    <h3>All Categories &amp; Sub-Categories</h3>
    <div class="a-table-wrap">
      <table class="a-table">
        <thead><tr><th>#</th><th>Name</th><th>Parent</th><th>Doctors</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($allCats as $c): ?>
            <tr>
              <td><?= $c['id'] ?></td>
              <td>
                <form method="post" style="display:flex;gap:6px;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="edit">
                  <input type="hidden" name="id" value="<?= $c['id'] ?>">
                  <input name="name" value="<?= clean($c['name']) ?>" style="border:1px solid var(--a-border);border-radius:6px;padding:5px 8px;">
                  <button class="a-btn a-btn-outline a-btn-sm" type="submit">Save</button>
                </form>
              </td>
              <td><?= $c['parent_name'] ? clean($c['parent_name']) : '<em>Main Category</em>' ?></td>
              <td><?= (int)$c['doctor_count'] ?></td>
              <td>
                <form method="post" onsubmit="return confirm('Delete this category?');" style="display:inline;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $c['id'] ?>">
                  <button class="a-btn a-btn-outline a-btn-sm" type="submit">🗑️ Delete</button>
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
