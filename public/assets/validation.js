// Client-side input validation. It only helps the user: the same rules are enforced again in PHP (Validator.php).
// Usage: <form data-validate novalidate> and <input data-rule="email"> (optional: data-optional, data-match="#other").
(function () {
  var MIN_AGE = 18;
  var rules = {
    required: function (v) { return v !== '' ? '' : 'This field is required.'; },
    username: function (v) { return /^[a-zA-Z0-9_\-]{3,50}$/.test(v) ? '' : 'Username: 3 to 50 characters (letters, digits, _ or -).'; },
    email: function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) && v.length <= 150 ? '' : 'Enter a valid email address.'; },
    password: function (v) { return v.length >= 8 ? '' : 'Password must be at least 8 characters long.'; },
    city: function (v) { return /^[\p{L}\s'\-]{2,80}$/u.test(v) ? '' : 'City: 2 to 80 letters.'; },
    adult: function (v) {
      var d = new Date(v + 'T00:00:00'), n = new Date();
      if (isNaN(d.getTime())) return 'Enter a valid date of birth.';
      if (d > n) return 'Date of birth cannot be in the future.';
      var age = n.getFullYear() - d.getFullYear();
      if (n < new Date(n.getFullYear(), d.getMonth(), d.getDate())) age--;
      return age >= MIN_AGE ? '' : 'You must be at least ' + MIN_AGE + ' years old.';
    }
  };

  function check(input) {
    var v = input.value.trim(), msg = '';
    if (input.type === 'password') v = input.value;            // never trim passwords
    var optional = input.hasAttribute('data-optional') || input.getAttribute('data-rule') === 'city';
    if (v === '' && optional) msg = '';
    else if (rules[input.getAttribute('data-rule')]) msg = rules[input.getAttribute('data-rule')](v);
    if (!msg && input.getAttribute('data-match')) {
      var other = document.querySelector(input.getAttribute('data-match'));
      if (other && other.value !== input.value) msg = 'Passwords do not match.';
    }
    var box = input.parentNode.querySelector('.field-error');
    if (!box) { box = document.createElement('div'); box.className = 'field-error'; input.parentNode.appendChild(box); }
    box.textContent = msg;
    input.classList.toggle('invalid', msg !== '');
    return msg === '';
  }

  document.querySelectorAll('form[data-validate]').forEach(function (form) {
    var fields = form.querySelectorAll('[data-rule]');
    fields.forEach(function (f) { f.addEventListener('blur', function () { check(f); }); });
    form.addEventListener('submit', function (e) {
      var ok = true;
      fields.forEach(function (f) { if (!check(f)) ok = false; });
      if (!ok) { e.preventDefault(); form.querySelector('.invalid').focus(); }
    });
  });
})();
