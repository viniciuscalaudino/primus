import { getCurrentUser, logout } from "../api/auth.js";
import { getCart } from "../api/cart.js";
import { getRecord, toast } from "../utils/dom.js";
import { takeCartWarnings } from "../utils/guard.js";

let navigationReady = false;

export async function initNavigation() {
  if (navigationReady) return;
  navigationReady = true;

  // Avisos da fusão do carrinho (ajustes de quantidade, itens indisponíveis)
  takeCartWarnings().forEach((warning) =>
    toast(warning.message || String(warning), "warning"),
  );

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
