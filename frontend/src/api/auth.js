import { http, csrfCookie } from "./http.js";
import { clearCartToken } from "../utils/storage.js";

export async function register(data) {
  await csrfCookie();

  const response = await http.post("/auth/register", data);
  clearCartToken();
  return response;
}

export async function login(credentials) {
  await csrfCookie();

  const response = await http.post("/auth/login", credentials);
  clearCartToken();
  return response;
}

export async function logout() {
  await csrfCookie();
  return http.post("/auth/logout");
}

export async function getCurrentUser() {
  return http.get("/auth/me");
}
