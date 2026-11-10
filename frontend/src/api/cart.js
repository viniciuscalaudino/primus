import { http } from "./http.js";
import { csrfCookie } from "./http.js";
import { setCartToken } from "../utils/storage.js";

function saveCartToken(response) {
  if (Object.hasOwn(response.data, "cart_token")) {
    setCartToken(response.data.cart_token);
  }

  const cart = response.data.cart || response.data;
  if (Array.isArray(cart.items)) {
    const count = cart.items.reduce(
      (total, item) => total + Number(item.quantity || 0),
      0,
    );
    document
      .querySelectorAll("[data-cart-count], #cartCount")
      .forEach((element) => {
        element.textContent = count;
      });
  }

  return response;
}

export async function getCart() {
  return saveCartToken(await http.get("/cart"));
}

export async function addToCart(productId, quantity = 1) {
  await csrfCookie();
  return saveCartToken(
    await http.post("/cart/items", {
      product_id: productId,
      quantity,
    }),
  );
}

export async function updateCartItem(productId, quantity) {
  await csrfCookie();
  return http.put(`/cart/items/${productId}`, {
    quantity,
  });
}

export async function removeCartItem(productId) {
  await csrfCookie();
  return http.delete(`/cart/items/${productId}`);
}
