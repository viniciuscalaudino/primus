import { initNavigation } from "../components/navigation.js";
import { getProducts } from "../api/products.js";
import { getCategories } from "../api/categories.js";
import { formatPrice } from "../utils/format.js";
import { addToCart, getCart } from "../api/cart.js";
import { getErrorMessage, getRecord, toast, unwrapList } from "../utils/dom.js";

const filtersForm = document.querySelector("#filtersForm");
const categoryFilter = document.querySelector("#categoryFilter");
const searchInput = document.querySelector("#searchInput");
const sortSelect = document.querySelector("#sortSelect");
const productGrid = document.querySelector("#productGrid");
const pagination = document.querySelector("#pagination");
const cardTemplate = document.querySelector("#productCardTemplate");
let currentFilters = {};
let cartQuantities = new Map();

async function loadCategories() {
  const response = await getCategories();
  const categories = unwrapList(response, "categories");

  categories.forEach((category) => {
    const option = document.createElement("option");
    option.value = category.id;
    option.textContent = category.name;
    categoryFilter.append(option);
  });
}

function renderProduct(product) {
  const node = cardTemplate.content.cloneNode(true);
  const card = node.querySelector(".product-card");
  const productUrl = `product.html?id=${encodeURIComponent(product.id)}`;
  const image = node.querySelector('[data-field="image"]');
  const placeholder = node.querySelector('[data-field="placeholder"]');
  const badge = node.querySelector('[data-field="badge"]');
  const addButton = node.querySelector('[data-action="add-to-cart"]');

  card.dataset.id = product.id;
  node.querySelector('[data-field="link"]').href = productUrl;
  node.querySelector('[data-field="nameLink"]').href = productUrl;
  node.querySelector('[data-field="name"]').textContent = product.name;
  node.querySelector('[data-field="category"]').textContent =
    product.category?.name || "";
  node.querySelector('[data-field="price"]').textContent = formatPrice(
    product.price,
  );

  if (product.image_url) {
    image.src = product.image_url;
    image.alt = product.name;
    image.loading = "lazy";
    image.classList.remove("d-none");
    image.addEventListener(
      "error",
      () => {
        image.classList.add("d-none");
        placeholder.classList.remove("d-none");
      },
      { once: true },
    );
    placeholder.classList.add("d-none");
  }

  if (Number(product.stock) < 1 || product.active === false) {
    card.classList.add("is-unavailable");
    badge.classList.remove("d-none");
    addButton.disabled = true;
    addButton.textContent = "Indisponível";
  } else {
    const alreadyInCart = Number(cartQuantities.get(Number(product.id)) || 0);
    if (alreadyInCart >= Number(product.stock)) {
      addButton.disabled = true;
      addButton.textContent = "Limite do estoque no carrinho";
    }
    addButton.addEventListener("click", async () => {
      if (
        Number(cartQuantities.get(Number(product.id)) || 0) >=
        Number(product.stock)
      ) {
        addButton.disabled = true;
        addButton.textContent = "Limite do estoque no carrinho";
        toast(
          "Você já adicionou ao carrinho todo o estoque disponível.",
          "warning",
        );
        return;
      }
      addButton.disabled = true;
      try {
        await addToCart(product.id);
        const quantity =
          Number(cartQuantities.get(Number(product.id)) || 0) + 1;
        cartQuantities.set(Number(product.id), quantity);
        toast("Produto adicionado ao carrinho.");
      } catch (error) {
        toast(
          getErrorMessage(error, "Não foi possível adicionar ao carrinho."),
          "danger",
        );
      } finally {
        const limitReached =
          Number(cartQuantities.get(Number(product.id)) || 0) >=
          Number(product.stock);
        addButton.disabled = limitReached;
        if (limitReached)
          addButton.textContent = "Limite do estoque no carrinho";
      }
    });
  }

  return node;
}

function renderPagination(links) {
  pagination.replaceChildren();
  if (!Array.isArray(links) || links.length <= 3) return;

  links.forEach((link) => {
    const item = document.createElement("li");
    item.className = `page-item${link.active ? " active" : ""}${link.url ? "" : " disabled"}`;
    const button = document.createElement("button");
    button.type = "button";
    button.className = "page-link";
    button.textContent = link.label.replace(/&laquo;|&raquo;/g, (entity) =>
      entity === "&laquo;" ? "‹" : "›",
    );
    button.disabled = !link.url || link.active;

    if (link.url && !link.active) {
      const page = new URL(link.url, window.location.origin).searchParams.get(
        "page",
      );
      button.addEventListener("click", () =>
        loadProducts({ ...currentFilters, page }),
      );
    }

    item.append(button);
    pagination.append(item);
  });
}

async function loadProducts(filters = currentFilters) {
  currentFilters = filters;
  document.querySelector("#catalogLoading")?.classList.remove("d-none");
  document.querySelector("#catalogError")?.classList.add("d-none");
  document.querySelector("#catalogEmpty")?.classList.add("d-none");

  try {
    const response = await getProducts(filters);
    const products = unwrapList(response, "products");
    productGrid.replaceChildren(...products.map(renderProduct));
    renderPagination(response.data?.meta?.links);
    document.querySelector("#resultsInfo").textContent =
      `${response.data?.meta?.total ?? products.length} produto(s)`;
    document
      .querySelector("#catalogEmpty")
      ?.classList.toggle("d-none", products.length > 0);
  } catch (error) {
    console.error("Erro ao carregar produtos:", error);
    document.querySelector("#catalogError")?.classList.remove("d-none");
  } finally {
    document.querySelector("#catalogLoading")?.classList.add("d-none");
  }
}

filtersForm.addEventListener("submit", (event) => {
  event.preventDefault();
  const filters = {
    q: searchInput.value.trim(),
    category: categoryFilter.value,
    sort: sortSelect.value,
  };
  const query = new URLSearchParams(
    Object.entries(filters).filter(([, value]) => value),
  );
  history.replaceState(
    null,
    "",
    `${location.pathname}${query.size ? `?${query}` : ""}`,
  );
  loadProducts(filters);
});

document
  .querySelector("#catalogRetry")
  ?.addEventListener("click", () => loadProducts());

document.addEventListener("DOMContentLoaded", async () => {
  const query = new URLSearchParams(location.search);
  searchInput.value = query.get("q") || "";
  categoryFilter.value = query.get("category") || "";
  sortSelect.value = query.get("sort") || "recent";
  currentFilters = {
    q: searchInput.value,
    category: categoryFilter.value,
    sort: sortSelect.value,
    page: query.get("page") || 1,
  };

  try {
    const cart = getRecord(await getCart(), "cart");
    cartQuantities = new Map(
      (cart.items || []).map((item) => [
        Number(item.product_id),
        Number(item.quantity),
      ]),
    );
  } catch (error) {
    console.debug(
      "Não foi possível consultar os itens atuais do carrinho:",
      error,
    );
  }

  try {
    await loadCategories();
    categoryFilter.value = query.get("category") || "";
  } catch (error) {
    console.error("Erro ao carregar categorias:", error);
  }

  await loadProducts(currentFilters);
});

document.addEventListener("DOMContentLoaded", initNavigation);
