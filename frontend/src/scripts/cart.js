import { getCart, updateCartItem, removeCartItem } from "../api/cart.js";
import { formatPrice } from "../utils/format.js";
import { $, getErrorMessage, getRecord, toast } from "../utils/dom.js";
async function loadCart() {
  $("#cartLoading")?.classList.remove("d-none");
  $("#cartContent")?.classList.add("d-none");
  $("#cartEmpty")?.classList.add("d-none");
  try {
    const cart = getRecord(await getCart(), "cart"),
      items = cart.items || [];
    const list = $("#cartItems");
    list.replaceChildren();
    if (!items.length) {
      $("#cartEmpty")?.classList.remove("d-none");
      return;
    }
    items.forEach((item) => {
      const node = $("#cartItemTemplate").content.cloneNode(true),
        row = node.querySelector("[data-product-id]");
      row.dataset.productId = item.product_id || item.id;
      const maxStock = Number(item.stock ?? Number.MAX_SAFE_INTEGER);
      const isAvailable = item.available !== false;
      row.classList.toggle("is-unavailable", !isAvailable);
      node.querySelector('[data-field="name"]').textContent = item.name;
      node.querySelector('[data-field="unitPrice"]').textContent =
        `${formatPrice(item.unit_price || item.price)} cada`;
      node.querySelector('[data-field="subtotal"]').textContent = formatPrice(
        item.subtotal ??
          Number(item.unit_price || item.price) * Number(item.quantity),
      );
      const quantity = node.querySelector('[data-field="quantity"]');
      quantity.value = item.quantity;
      quantity.max = maxStock;
      if (!isAvailable) {
        const warning = node.querySelector('[data-field="warning"]');
        warning.textContent =
          maxStock > 0
            ? `Estoque disponível: ${maxStock}. Ajuste a quantidade ou remova o item.`
            : "Produto indisponível ou sem estoque. Remova o item para continuar.";
        warning.classList.remove("d-none");
      }
      const change = async (value) => {
        if (value > maxStock) {
          quantity.value = maxStock;
          toast(
            `Estoque insuficiente. Há somente ${maxStock} unidade(s) disponíveis.`,
            "warning",
          );
          return;
        }
        try {
          if (value < 1) await removeCartItem(row.dataset.productId);
          else await updateCartItem(row.dataset.productId, value);
          await loadCart();
        } catch (e) {
          toast(getErrorMessage(e, "Erro ao atualizar carrinho."), "danger");
        }
      };
      node
        .querySelector('[data-action="decrease"]')
        .addEventListener("click", () => change(Number(quantity.value) - 1));
      node
        .querySelector('[data-action="increase"]')
        .addEventListener("click", () => change(Number(quantity.value) + 1));
      quantity.addEventListener("change", () => change(Number(quantity.value)));
      node
        .querySelector('[data-action="remove"]')
        .addEventListener("click", () => change(0));
      list.append(node);
    });
    $("#cartTotal").textContent = formatPrice(cart.total || 0);
    $("#cartContent")?.classList.remove("d-none");
  } catch (e) {
    toast(
      e.response?.data?.message || "Não foi possível carregar o carrinho.",
      "danger",
    );
  } finally {
    $("#cartLoading")?.classList.add("d-none");
  }
}
document.addEventListener("DOMContentLoaded", loadCart);
