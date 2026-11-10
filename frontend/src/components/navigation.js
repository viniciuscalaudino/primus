import { getCurrentUser, logout } from "../api/auth.js";
import { getCart } from "../api/cart.js";
import { getRecord } from "../utils/dom.js";

export async function initNavigation() {
  const accountLink = document.querySelector("#accountLink");
  const logoutBtn = document.querySelector("#logoutBtn");
  const profileLogout = document.querySelector("#profileLogout");

  try {
    const response = await getCurrentUser();
    const user = getRecord(response, "user");
    if (accountLink) {
      accountLink.href = "perfil.html";
      accountLink.title = user?.name || "Minha conta";
    }
    logoutBtn?.classList.remove("d-none");
    document.querySelectorAll('[data-role="admin"]').forEach((el) => {
      el.classList.toggle("d-none", user?.role !== "admin");
    });
  } catch {
    if (accountLink) accountLink.href = "login.html";
    logoutBtn?.classList.add("d-none");
  }

  const doLogout = async () => {
    try {
      await logout();
      location.href = "login.html";
    } catch (error) {
      console.error("Erro ao sair:", error);
    }
  };
  logoutBtn?.addEventListener("click", doLogout);
  profileLogout?.addEventListener("click", doLogout);

  try {
    const response = await getCart();
    const cart = getRecord(response, "cart");
    const count = (cart?.items || []).reduce(
      (sum, item) => sum + Number(item.quantity || 0),
      0,
    );
    document
      .querySelectorAll("[data-cart-count], #cartCount")
      .forEach((el) => (el.textContent = count));
  } catch (error) {
    console.debug("Contador do carrinho indisponível:", error);
  }
}
