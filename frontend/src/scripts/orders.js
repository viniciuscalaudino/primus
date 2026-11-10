import { requireAuth } from "../utils/guard.js";
import { getOrders } from "../api/orders.js";
import { formatPrice, formatDate } from "../utils/format.js";
import { $, unwrapList } from "../utils/dom.js";
async function loadOrders() {
  if (!(await requireAuth())) return;
  $("#ordersLoading")?.classList.remove("d-none");
  try {
    const orders = unwrapList(await getOrders(), "orders"),
      list = $("#ordersList");
    list.replaceChildren();
    $("#ordersEmpty")?.classList.toggle("d-none", orders.length > 0);
    orders.forEach((order) => {
      const card = $("#orderCardTemplate").content.cloneNode(true);
      const root = card.firstElementChild;
      root.querySelectorAll("[data-field]").forEach((el) => {
        const field = el.dataset.field;
        const value = {
          id: `Pedido #${order.id}`,
          status: order.status,
          date: formatDate(order.created_at),
          total: formatPrice(order.total),
        }[field];
        if (value !== undefined) el.textContent = value;
      });
      const link = root.querySelector("a");
      if (link) link.href = `pedido.html?id=${order.id}`;
      list.append(card);
    });
  } catch (e) {
    console.error("Erro ao carregar pedidos:", e);
  } finally {
    $("#ordersLoading")?.classList.add("d-none");
  }
}
document.addEventListener("DOMContentLoaded", loadOrders);
