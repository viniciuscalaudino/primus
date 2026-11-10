import { http, csrfCookie } from './http.js';

export async function register(data) {
    await csrfCookie();

    return http.post('/auth/register', data);
}

export async function login(credentials) {
    await csrfCookie();

    return http.post('/auth/login', credentials);
}

export async function logout() {
    return http.post('/auth/logout');
}

export async function getCurrentUser() {
    return http.get('/auth/me');
}