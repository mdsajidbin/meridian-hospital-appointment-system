// Meridian Hospital Chattogram — front-end interactivity
document.addEventListener('DOMContentLoaded', function () {

  // ---- Mobile nav toggle ----
  var toggle = document.querySelector('.menu-toggle');
  var links = document.querySelector('.nav-links');
  if (toggle && links) {
    toggle.addEventListener('click', function () { links.classList.toggle('open'); });
  }

  // ---- AOS-style scroll reveal (lightweight, no external lib needed) ----
  var reveal = document.querySelectorAll('[data-aos]');
  if ('IntersectionObserver' in window && reveal.length) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) {
          e.target.classList.add('aos-animate');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.15 });
    reveal.forEach(function (el) { io.observe(el); });
  } else {
    reveal.forEach(function (el) { el.classList.add('aos-animate'); });
  }

  // ---- Magnetic buttons (desktop only, respects reduced motion) ----
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!reduceMotion && window.matchMedia('(pointer:fine)').matches) {
    document.querySelectorAll('.magnetic').forEach(function (btn) {
      btn.addEventListener('mousemove', function (e) {
        var r = btn.getBoundingClientRect();
        var x = e.clientX - r.left - r.width / 2;
        var y = e.clientY - r.top - r.height / 2;
        btn.style.transform = 'translate(' + x * 0.25 + 'px,' + y * 0.25 + 'px)';
      });
      btn.addEventListener('mouseleave', function () { btn.style.transform = ''; });
    });
  }

  // ---- Doctor image lazy shimmer removal ----
  document.querySelectorAll('.img-wrap img').forEach(function (img) {
    var wrap = img.closest('.img-wrap');
    if (!wrap) return;
    wrap.classList.add('skeleton');
    if (img.complete) { wrap.classList.remove('skeleton'); }
    img.addEventListener('load', function () { wrap.classList.remove('skeleton'); });
  });

  // ---- Specialty filter checkboxes -> submit form ----
  document.querySelectorAll('.filter-sidebar input[type="checkbox"]').forEach(function (cb) {
    cb.addEventListener('change', function () {
      cb.closest('form').submit();
    });
  });

  // ---- Doctor profile: date tabs + slot picker ----
  var dateTabs = document.querySelectorAll('.date-tab');
  var slotGrid = document.getElementById('slot-grid');
  var selectedSlotInput = document.getElementById('selected_time');
  var selectedDateInput = document.getElementById('selected_date');
  var bookBtn = document.getElementById('book-btn');

  function loadSlots(doctorId, date) {
    if (!slotGrid) return;
    slotGrid.innerHTML = '<div class="muted">Loading available times…</div>';
    fetch('get-slots.php?doctor_id=' + encodeURIComponent(doctorId) + '&date=' + encodeURIComponent(date))
      .then(function (r) { return r.json(); })
      .then(function (data) {
        slotGrid.innerHTML = '';
        if (!data.slots || !data.slots.length) {
          slotGrid.innerHTML = '<div class="muted">No slots available on this day.</div>';
          return;
        }
        data.slots.forEach(function (s) {
          var el = document.createElement('div');
          el.className = 'slot' + (s.available ? '' : ' unavailable');
          el.textContent = s.time;
          if (s.available) {
            el.addEventListener('click', function () {
              document.querySelectorAll('.slot').forEach(function (x) { x.classList.remove('selected'); });
              el.classList.add('selected');
              if (selectedSlotInput) selectedSlotInput.value = s.time;
              if (bookBtn) bookBtn.removeAttribute('disabled');
            });
          }
          slotGrid.appendChild(el);
        });
      })
      .catch(function () { slotGrid.innerHTML = '<div class="muted">Could not load slots. Try again.</div>'; });
  }

  if (dateTabs.length && slotGrid) {
    var doctorId = slotGrid.getAttribute('data-doctor-id');
    dateTabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        dateTabs.forEach(function (t) { t.classList.remove('active'); });
        tab.classList.add('active');
        var date = tab.getAttribute('data-date');
        if (selectedDateInput) selectedDateInput.value = date;
        if (selectedSlotInput) selectedSlotInput.value = '';
        if (bookBtn) bookBtn.setAttribute('disabled', 'disabled');
        loadSlots(doctorId, date);
      });
    });
    // auto-load first day
    if (dateTabs[0]) dateTabs[0].click();
  }
});
