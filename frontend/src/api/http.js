import axios from 'axios';
import { getCartToken } from '../utils/storage.js';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

const API_ROOT = API_URL.replace(/\/api\/?$/, '');

export const http = axios.create({
    baseURL: API_URL,
    withCredentials: true,
    withXSRFToken: true,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
});

http.interceptors.request.use((config) => {
    config.headers['X-Cart-Token'] = getCartToken();

    return config;
});

export async function csrfCookie() {
    await axios.get(`${API_ROOT}/sanctum/csrf-cookie`, {
        withCredentials: true,
    });
}