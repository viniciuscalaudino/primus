import { initNavigation } from "../components/navigation.js";
import { requireAuth } from "../utils/guard.js";
import { getAddresses, createAddress } from "../api/addresses.js";
import { getCart } from "../api/cart.js";
import { createOrder } from "../api/orders.js";
import { formatPrice } from "../utils/format.js";
import { $, getRecord, toast, showAlert } from "../utils/dom.js";
let selectedAddress = null;
let cartHasItems = false;
let hasUnavailable = false;

function syncCreateOrderButton() {
  const button = $("#placeOrderBtn");
  if (button) button.disabled = !selectedAddress || !cartHasItems || hasUnavailable;
}
const fields = [
  "recipient_name",
  "zip_code",
  "street",
  "number",
  "complement",
  "neighborhood",
  "city",
  "state",
];
function addressPayload() {
  return Object.fromEntries(
    fields.map((key) => [
      key,
      document.getElementById(key)?.value.trim() || "",
    ]),
  );
}
function renderAddresses(addresses, preferredAddressId = selectedAddress) {
  const list = $("#addressList");
  list.replaceChildren();
  selectedAddress = null;
  addresses.forEach((address, index) => {
    const label = document.createElement("label");
    label.className = "form-check d-block mb-2";
    const radio = document.createElement("input");
    radio.type = "radio";
    radio.name = "address";
    radio.className = "form-check-input";
    radio.value = address.id;
    radio.checked =
      String(address.id) === String(preferredAddressId) ||
      (!preferredAddressId && (address.is_default || index === 0));
    if (radio.checked) selectedAddress = address.id;
    radio.addEventListener("change", () => {
      selectedAddress = address.id;
      syncCreateOrderButton();
    });
    const text = document.createElement("span");
    text.className = "form-check-label ms-2";
    text.textContent = `${address.recipient_name} — ${address.street}, ${address.number}, ${address.city}/${address.state}`;
    label.append(radio, text);
    list.append(label);
  });
  $("#addressEmpty")?.classList.toggle("d-none", addresses.length > 0);
  syncCreateOrderButton();
}
async function loadCheckout(preferredAddressId = selectedAddress) {
  if (!(await requireAuth())) return;
  try {
    const [addressesResponse, cartResponse] = await Promise.all([
      getAddresses(),
      getCart(),
    ]);
    renderAddresses(
      getRecord(addressesResponse, "addresses"),
      preferredAddressId,
    );
    const cart = getRecord(cartResponse, "cart");
    cartHasItems = (cart.items || []).length > 0;
    hasUnavailable = (cart.items || []).some((item) => item.available === false);
    if (hasUnavailable) {
      showAlert(
        $("#checkoutAlert"),
        "Há itens indisponíveis ou sem estoque suficiente. Volte ao carrinho e ajuste.",
      );
    }
    $("#checkoutItems").replaceChildren();
    (cart.items || []).forEach((item) => {
      const li = document.createElement("li");
      li.textContent = `${item.quantity} × ${item.name} — ${formatPrice(item.subtotal)}`;
      $("#checkoutItems").append(li);
    });
    $("#checkoutSubtotal").textContent = formatPrice(cart.total || 0);
    const subtotalCents = Math.round(Number(cart.total || 0) * 100);
    const shippingCents = subtotalCents >= 29900 ? 0 : 1990;
    $("#checkoutShipping").textContent = formatPrice(shippingCents / 100);
    $("#checkoutTotal").textContent = formatPrice(
      (subtotalCents + shippingCents) / 100,
    );
    syncCreateOrderButton();
    if (!cartHasItems) {
      toast("Seu carrinho está vazio.", "warning");
      location.href = "carrinho.html";
    }
  } catch (e) {
    showAlert(
      $("#checkoutAlert"),
      e.response?.data?.message || "Não foi possível carregar o checkout.",
    );
    cartHasItems = false;
    syncCreateOrderButton();
  }
}
$("#addressForm")?.addEventListener("submit", async (e) => {
  e.preventDefault();
  try {
    const response = await createAddress(addressPayload());
    const address = getRecord(response, "address");
    selectedAddress = address.id;
    await loadCheckout(address.id);
    $("#addressForm").reset();
    toast("Endereço cadastrado.");
  } catch (error) {
    showAlert(
      $("#addressAlert"),
      error.response?.data?.message || "Erro ao salvar endereço.",
    );
  }
});
$("#placeOrderBtn")?.addEventListener("click", async () => {
  if (!selectedAddress) {
    showAlert($("#checkoutAlert"), "Selecione um endereço de entrega.");
    return;
  }
  const button = $("#placeOrderBtn");
  button.disabled = true;
  try {
    const response = await createOrder(selectedAddress);
    const order = getRecord(response, "order");
    location.href = `pedido.html?id=${order.id}`;
  } catch (e) {
    showAlert(
      $("#checkoutAlert"),
      e.response?.data?.message || "Não foi possível criar o pedido.",
    );
    syncCreateOrderButton();
  }
});
document.addEventListener("DOMContentLoaded", loadCheckout);

document.addEventListener("DOMContentLoaded", initNavigation);
