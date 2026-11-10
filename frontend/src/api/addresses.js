import { http, csrfCookie } from "./http.js";

export async function getAddresses() {
  return http.get("/addresses");
}

export async function createAddress(data) {
  await csrfCookie();
  return http.post("/addresses", data);
}

export async function setDefaultAddress(addressId) {
  await csrfCookie();
  return http.patch(`/addresses/${addressId}/default`);
}
