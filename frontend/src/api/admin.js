import { http } from './http.js';

// PEDIDOS

export async function getAdminOrders(params = {}) {
    return http.get('/admin/orders', { params });
}

export async function updateOrderStatus(id, status) {
    return http.patch(`/admin/orders/${id}/status`, {
        status,
    });
}

// CATEGORIAS

export async function createCategory(data) {
    return http.post('/admin/categories', data);
}

export async function updateCategory(id, data) {
    return http.put(`/admin/categories/${id}`, data);
}

export async function deleteCategory(id) {
    return http.delete(`/admin/categories/${id}`);
}

// PRODUTOS

export async function getAdminProducts(params = {}) {
    return http.get('/admin/products', { params });
}

export async function createProduct(data) {
    return http.post('/admin/products', data);
}

export async function updateProduct(id, data) {
    return http.put(`/admin/products/${id}`, data);
}

export async function deactivateProduct(id) {
    return http.delete(`/admin/products/${id}`);
}