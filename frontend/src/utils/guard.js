import { getCurrentUser } from '../api/auth.js';

export async function requireAuth() {
    try {
        const response = await getCurrentUser();

        return response.data;
    } catch (error) {
        window.location.href = 'login.html';
        return null;
    }
}

