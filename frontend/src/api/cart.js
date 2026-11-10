
import { http } from './http.js';

export async function getCart() {
    return http.get('/cart');
}

export async function addToCart(productId, quantity = 1) {
    return http.post('/cart/items', {
        product_id: productId,
        quantity,
    });
}

export async function updateCartItem(productId, quantity) {
    return http.put(`/cart/items/${productId}`, {
        quantity,
    });
}
