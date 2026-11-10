const CART_TOKEN_KEY = 'primus_cart_token';

export function getCartToken() {
    let token = localStorage.getItem(CART_TOKEN_KEY);

    if (!token) {
        token = crypto.randomUUID();
        localStorage.setItem(CART_TOKEN_KEY, token);
    }

    return token;
}

export function clearCartToken() {
    localStorage.removeItem(CART_TOKEN_KEY);
}