// RendezVous - scripts communs
document.addEventListener('DOMContentLoaded', function () {

    // 1) Confirmation avant suppression : <a data-confirm="Message ?">
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!window.confirm(el.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });

    // 2) Validation côté client des formulaires (la validation PHP reste la référence)
    document.querySelectorAll('form.form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            let valid = true;
            form.querySelectorAll('.field-error-msg').forEach(function (m) { m.remove(); });
            form.querySelectorAll('.field-error').forEach(function (f) { f.classList.remove('field-error'); });

            form.querySelectorAll('input, select, textarea').forEach(function (field) {
                if (field.type === 'submit') return;
                const value = field.value.trim();
                let message = '';

                if (field.required && value === '') {
                    message = 'Ce champ est obligatoire.';
                } else if (field.type === 'email' && value !== '' &&
                           !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    message = 'Adresse email invalide.';
                }

                if (message) {
                    valid = false;
                    field.classList.add('field-error');
                    const span = document.createElement('div');
                    span.className = 'field-error-msg';
                    span.textContent = message;
                    field.insertAdjacentElement('afterend', span);
                }
            });

            if (!valid) e.preventDefault();
        });
    });
});
