import { initNavigation } from "../components/navigation.js";
import { getProduct } from "../api/products.js";
import { addToCart, getCart } from "../api/cart.js";
import { formatPrice } from "../utils/format.js";
import { $, getErrorMessage, getRecord, queryId, toast } from "../utils/dom.js";
let product;
let availableToAdd = 0;
async function loadProduct() {
  const id = queryId();
  if (!id) {
    $("#productNotFound")?.classList.remove("d-none");
    return;
  }
  try {
    const response = await getProduct(id);
    product = response.data.product || response.data.data || response.data;
    let currentQuantity = 0;
    try {
      const cart = getRecord(await getCart(), "cart");
      currentQuantity = Number(
        cart.items?.find(
          (item) => Number(item.product_id) === Number(product.id),
        )?.quantity || 0,
      );
    } catch (error) {
      console.debug(
        "Não foi possível consultar a quantidade atual no carrinho:",
        error,
      );
    }
    availableToAdd = Math.max(0, Number(product.stock) - currentQuantity);
    $("#productName").textContent = product.name;
    $("#crumbName").textContent = product.name;
    $("#productPrice").textContent = formatPrice(product.price);
    $("#productDescription").textContent = product.description || "";
    $("#productCategory").textContent =
      product.category?.name || product.category_name || "";
    $("#productStock").textContent =
      availableToAdd > 0
        ? `${availableToAdd} disponível(is) para adicionar`
        : "Indisponível";
    if (product.image_url || product.image) {
      $("#productImage").src = product.image_url || product.image;
      $("#productImage").alt = product.name;
      $("#productImage").classList.remove("d-none");
      $("#productPlaceholder")?.classList.add("d-none");
      $("#productImage").addEventListener(
        "error",
        () => {
          $("#productImage").classList.add("d-none");
          $("#productPlaceholder")?.classList.remove("d-none");
        },
        { once: true },
      );
    }
    $("#productLoading")?.classList.add("d-none");
    $("#productDetail")?.classList.remove("d-none");
    $("#qtyInput").max = availableToAdd;
    $("#qtyInput").value = availableToAdd > 0 ? 1 : 0;
    $("#addToCartBtn").disabled = availableToAdd < 1;
  } catch (e) {
    $("#productLoading")?.classList.add("d-none");
    $("#productNotFound")?.classList.remove("d-none");
  }
}
function changeQty(delta) {
  const input = $("#qtyInput");
  input.value = Math.min(
    availableToAdd,
    Math.max(1, Number(input.value || 1) + delta),
  );
}
$("#qtyMinus")?.addEventListener("click", () => changeQty(-1));
$("#qtyPlus")?.addEventListener("click", () => changeQty(1));
$("#qtyInput")?.addEventListener("change", (e) => {
  const quantity = Number(e.target.value || 1);
  e.target.value = Math.min(availableToAdd, Math.max(1, quantity));
});
$("#addToCartBtn")?.addEventListener("click", async () => {
  if (!product || availableToAdd < 1) {
    toast("Não há mais unidades disponíveis para adicionar.", "warning");
    return;
  }

  const button = $("#addToCartBtn");
  button.disabled = true;
  try {
    const quantity = Number($("#qtyInput").value);
    await addToCart(product.id, quantity);
    availableToAdd = Math.max(0, availableToAdd - quantity);
    $("#qtyInput").max = availableToAdd;
    $("#qtyInput").value = availableToAdd > 0 ? 1 : 0;
    $("#productStock").textContent =
      availableToAdd > 0
        ? `${availableToAdd} disponível(is) para adicionar`
        : "Limite do estoque atingido no carrinho";
    toast("Produto adicionado ao carrinho.");
  } catch (e) {
    toast(
      getErrorMessage(e, "Não foi possível adicionar ao carrinho."),
      "danger",
    );
  } finally {
    button.disabled = availableToAdd < 1;
  }
});
document.addEventListener("DOMContentLoaded", loadProduct);

document.addEventListener("DOMContentLoaded", initNavigation);
