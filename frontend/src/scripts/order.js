import { requireAuth } from "../utils/guard.js";
import { getOrder, payOrder, cancelOrder } from "../api/orders.js";
import { formatPrice, formatDate } from "../utils/format.js";
import { $, queryId, getRecord, toast, showAlert } from "../utils/dom.js";
let order;
async function loadOrder() {
  if (!(await requireAuth())) return;
  const id = queryId();
  if (!id) {
    $("#orderNotFound")?.classList.remove("d-none");
    return;
  }
  try {
    order = getRecord(await getOrder(id), "order");
    $("#orderId").textContent = `#${order.id}`;
    $("#orderIdCrumb").textContent = order.id;
    $("#orderStatus").textContent = order.status;
    $("#orderDate").textContent = formatDate(order.created_at);
    $("#orderSubtotal").textContent = formatPrice(
      order.subtotal || order.total,
    );
    $("#orderShipping").textContent = formatPrice(order.shipping || 0);
    $("#orderTotal").textContent = formatPrice(order.total);
    const address = order.address || order.shipping_address;
    $("#orderAddress").textContent = address
      ? `${address.recipient_name}, ${address.street}, ${address.number}, ${address.city}/${address.state}`
      : "Endereço não disponível";
    const items = $("#orderItems");
    items.replaceChildren();
    (order.items || []).forEach((item) => {
      const li = document.createElement("li");
      li.textContent = `${item.quantity} × ${item.product_name || item.name} — ${formatPrice(item.subtotal || Number(item.unit_price) * item.quantity)}`;
      items.append(li);
    });
    $("#orderDetail")?.classList.remove("d-none");
    $("#payBtn").classList.toggle(
      "d-none",
      order.status !== "awaiting_payment",
    );
    $("#cancelBtn").classList.toggle(
      "d-none",
      order.status !== "awaiting_payment",
    );
  } catch (e) {
    $("#orderNotFound")?.classList.remove("d-none");
  }
}
$("#payBtn")?.addEventListener("click", async () => {
  try {
    await payOrder(order.id);
    toast("Pagamento confirmado.");
    await loadOrder();
  } catch (e) {
    showAlert(
      $("#orderAlert"),
      e.response?.data?.message || "Não foi possível confirmar pagamento.",
    );
  }
});
$("#cancelBtn")?.addEventListener("click", async () => {
  if (!confirm("Deseja cancelar este pedido?")) return;
  try {
    await cancelOrder(order.id);
    toast("Pedido cancelado.");
    await loadOrder();
  } catch (e) {
    showAlert(
      $("#orderAlert"),
      e.response?.data?.message || "Não foi possível cancelar.",
    );
  }
});
document.addEventListener("DOMContentLoaded", loadOrder);
