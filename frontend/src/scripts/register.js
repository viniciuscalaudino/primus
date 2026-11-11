import { initNavigation } from "../components/navigation.js";
import { register } from '../api/auth.js';
import { saveCartWarnings, redirectAfterAuth } from '../utils/guard.js';

const registerForm = document.querySelector('#registerForm');
const formAlert = document.querySelector('#formAlert');
const registerBtn = document.querySelector('#registerBtn');
const spinner = registerBtn.querySelector('.spinner-border');

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

registerForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    clearErrors();

    const data = {
        name: registerForm.name.value.trim(),
        email: registerForm.email.value.trim(),
        password: registerForm.password.value,
        password_confirmation: registerForm.password_confirmation.value,
    };

    if (!data.name) {
        showFieldError('name', 'Informe seu nome.');
        return;
    }

    if (!data.email) {
        showFieldError('email', 'Informe seu e-mail.');
        return;
    }

    if (data.password.length < 8) {
        showFieldError('password', 'A senha deve ter pelo menos 8 caracteres.');
        return;
    }

    if (data.password !== data.password_confirmation) {
        showFieldError('password_confirmation', 'As senhas não coincidem.');
        return;
    }

    registerBtn.disabled = true;
    spinner.classList.remove('d-none');

    try {
        const response = await register(data);

        // O cadastro já deixa o usuário logado e funde o carrinho de visitante.
        saveCartWarnings(response);
        redirectAfterAuth();

    } catch (error) {
        const errors = error.response?.data?.errors;

        if (errors) {
            Object.entries(errors).forEach(([field, messages]) => {
                showFieldError(field, messages[0]);
            });
        } else {
            showFormError(
                error.response?.data?.message ||
                'Não foi possível criar sua conta.'
            );
        }

    } finally {
        registerBtn.disabled = false;
        spinner.classList.add('d-none');
    }
});

document.addEventListener("DOMContentLoaded", initNavigation);
