import { initNavigation } from "../components/navigation.js";
import { requireAuth } from "../utils/guard.js";
import { getOrders } from "../api/orders.js";
import { formatPrice, formatDate, statusLabel } from "../utils/format.js";
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
          status: statusLabel(order.status),
          count: (order.items || []).reduce(
            (sum, item) => sum + Number(item.quantity || 0),
            0,
          ),
          date: formatDate(order.created_at),
          total: formatPrice(order.total),
        }[field];
        if (value !== undefined) el.textContent = value;
      });
      root.querySelector('[data-field="status"]').dataset.status = order.status;

      root.href = `pedido.html?id=${order.id}`;
      list.append(card);
    });
  } catch (e) {
    console.error("Erro ao carregar pedidos:", e);
  } finally {
    $("#ordersLoading")?.classList.add("d-none");
  }
}
document.addEventListener("DOMContentLoaded", loadOrders);

document.addEventListener("DOMContentLoaded", initNavigation);
