const CART_TOKEN_KEY = "primus_cart_token";

export function getCartToken() {
  return localStorage.getItem(CART_TOKEN_KEY);
}

export function setCartToken(token) {
  if (token) localStorage.setItem(CART_TOKEN_KEY, token);
  else clearCartToken();
}

export function clearCartToken() {
  localStorage.removeItem(CART_TOKEN_KEY);
}
