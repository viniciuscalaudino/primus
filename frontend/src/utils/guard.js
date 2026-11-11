import { getCurrentUser } from '../api/auth.js';

const NEXT_KEY = 'primus_next';
const WARNINGS_KEY = 'primus_cart_warnings';

// Exige login. Se não estiver logado, lembra a página atual e vai para o login.
export async function requireAuth() {
    try {
        const response = await getCurrentUser();

        return response.data;
    } catch (error) {
        const page = location.pathname.split('/').pop();
        try { sessionStorage.setItem(NEXT_KEY, page + location.search); } catch {}
        window.location.href = 'login.html';
        return null;
    }
}

// Exige login e papel admin (o cliente volta para a home).
export async function requireAdmin() {
    const response = await requireAuth();
    if (!response) return null;

    const user = response.data ?? response;
    if (user.role !== 'admin') {
        window.location.href = 'index.html';
        return null;
    }

    return user;
}

// Guarda os avisos da fusão do carrinho para mostrar na próxima página.
export function saveCartWarnings(response) {
    const warnings = response?.data?.cart_warnings?.products || [];
    try {
        if (warnings.length) sessionStorage.setItem(WARNINGS_KEY, JSON.stringify(warnings));
    } catch {}
}

export function takeCartWarnings() {
    try {
        const raw = sessionStorage.getItem(WARNINGS_KEY);
        sessionStorage.removeItem(WARNINGS_KEY);
        return raw ? JSON.parse(raw) : [];
    } catch {
        return [];
    }
}

// Depois de entrar: volta para a página que exigiu login (ex.: checkout) ou vai para a home.
export function redirectAfterAuth() {
    let next = null;
    try {
        next = sessionStorage.getItem(NEXT_KEY);
        sessionStorage.removeItem(NEXT_KEY);
    } catch {}
    window.location.href = next || 'index.html';
}
