// Spinner, day/night switch (the heart) and confirmation before destructive actions.
(function () {
  function hide() { var s = document.getElementById('spinner'); if (s) s.classList.remove('show'); }
  if (document.readyState === 'complete') setTimeout(hide, 1);
  else window.addEventListener('load', function () { setTimeout(hide, 1); });
})();

document.addEventListener('click', function (e) {
  var h = e.target.closest('.heart');
  if (!h) return;
  h.classList.add('spin'); setTimeout(function () { h.classList.remove('spin'); }, 500);
  var t = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
  document.documentElement.dataset.theme = t;
  try { localStorage.setItem('bl_theme', JSON.stringify(t)); } catch (err) {}
  document.querySelectorAll('.heart').forEach(function (x) { x.title = t === 'dark' ? 'Switch to day mode' : 'Switch to night mode'; });
});

// <form data-confirm="Are you sure?"> asks before submitting (inline onclick would be blocked by the CSP).
document.addEventListener('submit', function (e) {
  var m = e.target.getAttribute('data-confirm');
  if (m && !confirm(m)) e.preventDefault();
});

// Quick-login buttons (demo mode): fill the email and password of the chosen role.
document.addEventListener('click', function (e) {
  var q = e.target.closest('[data-fill-email]');
  if (!q) return;
  document.getElementById('email').value = q.dataset.fillEmail;
  document.getElementById('password').value = q.dataset.fillPw;
  document.querySelectorAll('.qbtn').forEach(function (b) { b.classList.toggle('on', b === q); });
  document.querySelectorAll('.field-error').forEach(function (el) { el.textContent = ''; });
  document.querySelectorAll('input.invalid').forEach(function (el) { el.classList.remove('invalid'); });
  var go = document.querySelector('form[action*="login"] button.btn'); if (go) go.focus();
});

// Reveal cards when they scroll into view.
(function () {
  var els = document.querySelectorAll('.reveal');
  if (!('IntersectionObserver' in window)) { els.forEach(function (el) { el.classList.add('in'); }); return; }
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } });
  }, { threshold: 0.12 });
  els.forEach(function (el, i) { el.style.transitionDelay = (i % 6) * 60 + 'ms'; io.observe(el); });
})();

// Count-up animation for the numbers of the dashboard cards.
document.querySelectorAll('.stat b').forEach(function (b) {
  var n = parseInt(b.textContent, 10);
  if (isNaN(n) || String(n) !== b.textContent.trim() || n === 0 || matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  var t0 = null;
  (function step(t) { if (!t0) t0 = t; var p = Math.min((t - t0) / 700, 1); b.textContent = Math.round(n * p); if (p < 1) requestAnimationFrame(step); })(performance.now());
});
