// Spinner, day/night switch (the heart) and confirmation before destructive actions.
(function () {
  function hide() { var s = document.getElementById('spinner'); if (s) s.classList.remove('show'); }
  if (document.readyState === 'complete') setTimeout(hide, 1);
  else window.addEventListener('load', function () { setTimeout(hide, 1); });
})();

document.addEventListener('click', function (e) {
  var h = e.target.closest('.heart');
  if (!h) return;
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
