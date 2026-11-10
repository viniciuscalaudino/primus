import { http } from './http.js';

export async function getProducts(params = {}) {
    return http.get('/products', { params });
}

export async function getProduct(id) {
    return http.get(`/products/${id}`);
}