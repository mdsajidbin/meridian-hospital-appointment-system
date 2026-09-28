// Meridian Hospital Chattogram — Admin panel interactivity
document.addEventListener('DOMContentLoaded', function () {

  // ---- Sidebar collapsible groups ----
  document.querySelectorAll('.a-nav-link[data-toggle]').forEach(function (link) {
    link.addEventListener('click', function (e) {
      var sub = document.getElementById(link.getAttribute('data-toggle'));
      if (sub) { e.preventDefault(); sub.classList.toggle('open'); }
    });
  });

  // ---- Mobile sidebar toggle ----
  var burger = document.getElementById('sidebar-burger');
  var sidebar = document.querySelector('.a-sidebar');
  if (burger && sidebar) {
    burger.addEventListener('click', function () { sidebar.classList.toggle('open'); });
  }

  // ---- Animated counters (CountUp-style, no external dependency) ----
  document.querySelectorAll('[data-countup]').forEach(function (el) {
    var target = parseInt(el.getAttribute('data-countup'), 10) || 0;
    var duration = 900;
    var start = null;
    function step(ts) {
      if (!start) start = ts;
      var progress = Math.min((ts - start) / duration, 1);
      el.textContent = Math.floor(progress * target).toLocaleString();
      if (progress < 1) requestAnimationFrame(step);
      else el.textContent = target.toLocaleString();
    }
    requestAnimationFrame(step);
  });

  // ---- Status dropdown auto-submit ----
  document.querySelectorAll('select.select-status').forEach(function (sel) {
    sel.classList.add(sel.value);
    sel.addEventListener('change', function () {
      sel.className = 'select-status ' + sel.value;
      sel.closest('form').submit();
    });
  });

  // ---- Simple table search filter (client side) ----
  document.querySelectorAll('.a-search[data-table]').forEach(function (input) {
    input.addEventListener('keyup', function () {
      var table = document.getElementById(input.getAttribute('data-table'));
      if (!table) return;
      var q = input.value.toLowerCase();
      table.querySelectorAll('tbody tr').forEach(function (row) {
        row.style.display = row.textContent.toLowerCase().indexOf(q) > -1 ? '' : 'none';
      });
    });
  });

  // ---- Export buttons: Copy / CSV / Print ----
  document.querySelectorAll('[data-export]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var tableId = btn.getAttribute('data-export-table');
      var table = document.getElementById(tableId);
      if (!table) return;
      var type = btn.getAttribute('data-export');

      if (type === 'print') { window.print(); return; }

      var rows = Array.from(table.querySelectorAll('tr')).map(function (tr) {
        return Array.from(tr.querySelectorAll('th,td')).map(function (td) {
          return '"' + td.textContent.trim().replace(/"/g, '""') + '"';
        }).join(',');
      });
      var csv = rows.join('\n');

      if (type === 'copy') {
        navigator.clipboard.writeText(csv).then(function () {
          btn.textContent = 'Copied!';
          setTimeout(function () { btn.textContent = 'Copy'; }, 1500);
        });
      } else if (type === 'csv' || type === 'excel') {
        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = tableId + '.csv';
        link.click();
      }
    });
  });
});
