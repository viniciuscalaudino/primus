import { http } from './http.js';

export async function getCategories() {
    return http.get('/categories');
}
