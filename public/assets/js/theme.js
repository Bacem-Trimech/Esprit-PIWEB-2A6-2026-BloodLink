// Loaded in <head>: applies the saved day/night mode before the first paint (no flash).
(function () {
  var t = null;
  try { t = JSON.parse(localStorage.getItem('bl_theme')); } catch (e) {}
  document.documentElement.dataset.theme = t || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
})();
