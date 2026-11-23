document.addEventListener('DOMContentLoaded', () => {
  const search = document.getElementById('searchInput');
  if (search) search.addEventListener('input', () => {
    const q = search.value.toLocaleLowerCase('fr').trim();
    document.querySelectorAll('.establishment-card').forEach(card => {
      card.hidden = !card.innerText.toLocaleLowerCase('fr').includes(q);
    });
  });

  const form = document.getElementById('establishmentForm');
  if (form) {
    const names = ['nom', 'type', 'ville', 'adresse', 'telephone', 'email'];
    const showError = (name, message) => {
      const target = document.getElementById(name + 'Error');
      if (target) target.textContent = message;
      const input = document.getElementById(name);
      if (input) input.setAttribute('aria-invalid', message ? 'true' : 'false');
    };
    const validate = () => {
      let valid = true;
      names.forEach(name => {
        const field = document.getElementById(name);
        const value = field.value.trim();
        let message = '';
        if (!value) message = 'Ce champ est obligatoire.';
        else if (['nom', 'ville', 'adresse'].includes(name) && value.length < 2) message = 'Saisir au moins 2 caractères.';
        else if (name === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) message = 'Adresse e-mail invalide.';
        else if (name === 'telephone' && !/^[0-9 +().-]{8,20}$/.test(value)) message = 'Numéro de téléphone invalide.';
        showError(name, message);
        if (message) valid = false;
      });
      return valid;
    };
    names.forEach(name => {
      const field = document.getElementById(name);
      field.addEventListener('input', () => showError(name, ''));
      field.addEventListener('change', () => showError(name, ''));
    });
    form.addEventListener('submit', event => {
      if (!validate()) {
        event.preventDefault();
        form.querySelector('[aria-invalid="true"]')?.focus();
      }
    });
  }

  const loginForm = document.getElementById('adminLoginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', event => {
      let valid = true;
      ['username', 'password'].forEach(name => {
        const field = document.getElementById(name);
        const error = document.getElementById(name + 'Error');
        const message = field.value.trim() ? '' : 'Ce champ est obligatoire.';
        error.textContent = message;
        if (message) valid = false;
      });
      if (!valid) event.preventDefault();
    });
  }
});

// Validation JavaScript générique des formulaires du module.
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form.js-validate').forEach(form => {
    form.addEventListener('submit', event => {
      let valid = true;
      form.querySelectorAll('input, select, textarea').forEach(field => {
        if (field.type === 'hidden' || field.name === 'csrf_token') return;
        let message = '';
        if (field.required && !field.value.trim()) message = 'Ce champ est obligatoire.';
        if (!message && field.type === 'email' && field.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) message = 'Adresse e-mail invalide.';
        if (!message && field.type === 'number' && field.value) {
          const n = Number(field.value);
          if (!Number.isFinite(n) || (field.min !== '' && n < Number(field.min)) || (field.max !== '' && n > Number(field.max))) message = 'Valeur numérique invalide.';
        }
        let error = field.parentElement.querySelector('.js-field-error');
        if (!error) { error = document.createElement('small'); error.className = 'js-field-error'; field.insertAdjacentElement('afterend', error); }
        error.textContent = message;
        field.setAttribute('aria-invalid', message ? 'true' : 'false');
        if (message) valid = false;
      });
      if (!valid) { event.preventDefault(); form.querySelector('[aria-invalid="true"]')?.focus(); }
    });
  });
});

// Calcul automatique de la durée de sommeil à partir des heures saisies.
document.addEventListener('DOMContentLoaded', () => {
  const sleepForm = document.querySelector('form.js-validate');
  if (!sleepForm) return;
  const type = new URLSearchParams(window.location.search).get('type');
  if (type !== 'sommeil') return;
  const start = sleepForm.querySelector('[name="heureCoucher"]');
  const end = sleepForm.querySelector('[name="heureReveil"]');
  const duration = sleepForm.querySelector('[name="duree"]');
  if (!start || !end || !duration) return;
  duration.readOnly = true;
  const calculate = () => {
    if (!start.value || !end.value) { duration.value = ''; return; }
    const toMinutes = value => { const [h,m] = value.split(':').map(Number); return h*60+m; };
    let diff = toMinutes(end.value)-toMinutes(start.value); if (diff <= 0) diff += 1440;
    duration.value = (diff/60).toFixed(2);
  };
  start.addEventListener('change', calculate); end.addEventListener('change', calculate);
  calculate();
});
