import { http, csrfCookie } from "./http.js";

export async function createOrder(addressId) {
  await csrfCookie();
  return http.post("/orders", {
    address_id: addressId,
  });
}

export async function getOrders() {
  return http.get("/orders");
}

export async function getOrder(id) {
  return http.get(`/orders/${id}`);
}

export async function cancelOrder(id) {
  return http.post(`/orders/${id}/cancel`);
}

export async function payOrder(id) {
  return http.post(`/orders/${id}/pay`);
}
