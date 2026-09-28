<div class="a-topbar">
  <div style="display:flex;align-items:center;gap:14px;">
    <button id="sidebar-burger" style="display:none;background:none;border:none;font-size:1.3rem;cursor:pointer;">☰</button>
    <div class="crumbs"><a href="index.php">Home</a> / <?= clean($breadcrumb ?? '') ?></div>
  </div>
  <div class="a-topbar-icons">
    <span class="a-icon-btn tooltip" data-tip="Messages">💬<span class="badge">2</span></span>
    <span class="a-icon-btn tooltip" data-tip="Notifications">🔔<span class="badge">1</span></span>
    <div class="a-user">🟢 <?= clean(current_user()['name']) ?></div>
    <a href="logout.php" class="a-btn a-btn-outline a-btn-sm">Logout</a>
  </div>
</div>
<style>@media(max-width:960px){#sidebar-burger{display:block !important;}}</style>
