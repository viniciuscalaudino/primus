import { login } from '../api/auth.js';

const loginForm = document.querySelector('#loginForm');
const formAlert = document.querySelector('#formAlert');
const loginBtn = document.querySelector('#loginBtn');
const spinner = loginBtn.querySelector('.spinner-border');

function clearErrors() {
    formAlert.classList.add('d-none');
    formAlert.textContent = '';

    document.querySelectorAll('.is-invalid').forEach((input) => {
        input.classList.remove('is-invalid');
    });

    document.querySelectorAll('[data-error-for]').forEach((element) => {
        element.textContent = '';
    });
}

function showFieldError(field, message) {
    const input = document.querySelector(`#${field}`);
    const errorElement = document.querySelector(
        `[data-error-for="${field}"]`
    );

    if (input && errorElement) {
        input.classList.add('is-invalid');
        errorElement.textContent = message;
    }
}

function showFormError(message) {
    formAlert.textContent = message;
    formAlert.classList.remove('d-none');
}

loginForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    clearErrors();

    const credentials = {
        email: loginForm.email.value.trim(),
        password: loginForm.password.value,
    };

    if (!credentials.email) {
        showFieldError('email', 'Informe seu e-mail.');
        return;
    }

    if (!credentials.password) {
        showFieldError('password', 'Informe sua senha.');
        return;
    }

    loginBtn.disabled = true;
    spinner.classList.remove('d-none');

    try {
        await login(credentials);

        window.location.href = './index.html';

    } catch (error) {
        const errors = error.response?.data?.errors;

        if (errors) {
            Object.entries(errors).forEach(([field, messages]) => {
                showFieldError(field, messages[0]);
            });
        } else {
            showFormError(
                error.response?.data?.message ||
                'Não foi possível realizar o login.'
            );
        }

    } finally {
        loginBtn.disabled = false;
        spinner.classList.add('d-none');
    }
});