import { initNavigation } from "../components/navigation.js";
import { requireAdmin } from "../utils/guard.js";
import {
  getAdminProducts,
  createProduct,
  updateProduct,
  deactivateProduct,
  createCategory,
  updateCategory,
  deleteCategory,
  getAdminOrders,
  updateOrderStatus,
} from "../api/admin.js";
import { getCategories } from "../api/categories.js";
import { formatPrice, formatDate, statusLabel } from "../utils/format.js";
import {
  $,
  unwrapList,
  getRecord,
  showAlert,
  hideAlert,
  toast,
} from "../utils/dom.js";

const productModal = () =>
  bootstrap.Modal.getOrCreateInstance($("#productModal"));
const categoryModal = () =>
  bootstrap.Modal.getOrCreateInstance($("#categoryModal"));
let confirmAction = null;
let currentProductImage = null;
let imagePreviewUrl = null;

function showImagePreview(src) {
  const preview = $("#imagePreview");
  if (imagePreviewUrl) {
    URL.revokeObjectURL(imagePreviewUrl);
    imagePreviewUrl = null;
  }
  if (src) {
    if (src.startsWith("blob:")) imagePreviewUrl = src;
    preview.src = src;
    preview.classList.remove("d-none");
  } else {
    preview.removeAttribute("src");
    preview.classList.add("d-none");
  }
}

function fillSelect(categories) {
  const select = $("#category_id");
  if (!select) return;
  select.replaceChildren();
  categories.forEach((c) => {
    const option = document.createElement("option");
    option.value = c.id;
    option.textContent = c.name;
    select.append(option);
  });
}
async function loadCategories() {
  const categories = unwrapList(await getCategories(), "categories");
  fillSelect(categories);
  const body = $("#adminCategoriesBody");
  if (!body) return;
  body.replaceChildren();
  categories.forEach((category) => {
    const node = $("#adminCategoryRowTemplate").content.cloneNode(true),
      row = node.querySelector("tr");
    row.dataset.id = category.id;
    row.querySelector('[data-field="id"]').textContent = category.id;
    row.querySelector('[data-field="name"]').textContent = category.name;
    row.querySelector('[data-field="description"]').textContent =
      category.description || "";
    row
      .querySelector('[data-action="edit"]')
      .addEventListener("click", () => editCategory(category));
    row
      .querySelector('[data-action="delete"]')
      .addEventListener("click", () =>
        confirm("Excluir esta categoria?", () =>
          deleteCategory(category.id).then(refreshAll),
        ),
      );
    body.append(node);
  });
}
let productsPage = 1;

function renderProductsPagination(links) {
  const pagination = $("#adminProductsPagination");
  if (!pagination) return;
  pagination.replaceChildren();
  if (!Array.isArray(links) || links.length <= 3) return;
  links.forEach((link) => {
    const item = document.createElement("li");
    item.className = `page-item${link.active ? " active" : ""}${link.url ? "" : " disabled"}`;
    const button = document.createElement("button");
    button.type = "button";
    button.className = "page-link";
    button.textContent = link.label
      .replace("&laquo; Previous", "‹")
      .replace("Next &raquo;", "›");
    button.disabled = !link.url || link.active;
    if (link.url && !link.active) {
      const page = new URL(link.url, location.origin).searchParams.get("page");
      button.addEventListener("click", async () => {
        productsPage = Number(page);
        await loadProducts();
      });
    }
    item.append(button);
    pagination.append(item);
  });
}

async function loadProducts() {
  const productsResponse = await getAdminProducts({ page: productsPage });
  const products = unwrapList(productsResponse, "products"),
    body = $("#adminProductsBody");
  renderProductsPagination(productsResponse.data?.meta?.links);
  body.replaceChildren();
  products.forEach((product) => {
    const node = $("#adminProductRowTemplate").content.cloneNode(true),
      row = node.querySelector("tr");
    row.dataset.id = product.id;
    row.querySelector('[data-field="id"]').textContent = product.id;
    const image = row.querySelector('[data-field="image"]');
    if (product.image_url) {
      image.src = product.image_url;
      image.alt = `Imagem de ${product.name}`;
      image.classList.remove("d-none");
    }
    row.querySelector('[data-field="name"]').textContent = product.name;
    row.querySelector('[data-field="category"]').textContent =
      product.category?.name || "";
    row.querySelector('[data-field="price"]').textContent = formatPrice(
      product.price,
    );
    row.querySelector('[data-field="stock"]').textContent = product.stock;
    const status = row.querySelector('[data-field="status"]');
    status.textContent = product.active ? "Ativo" : "Inativo";
    row
      .querySelector('[data-action="edit"]')
      .addEventListener("click", () => editProduct(product));
    row
      .querySelector('[data-action="deactivate"]')
      .addEventListener("click", () =>
        confirm("Desativar este produto?", () =>
          deactivateProduct(product.id).then(refreshAll),
        ),
      );
    body.append(node);
  });
}
async function loadOrders() {
  const params = {};
  if ($("#orderStatusFilter")?.value)
    params.status = $("#orderStatusFilter").value;
  const orders = unwrapList(await getAdminOrders(params), "orders"),
    body = $("#adminOrdersBody");
  body.replaceChildren();
  orders.forEach((order) => {
    const node = $("#adminOrderRowTemplate").content.cloneNode(true),
      row = node.querySelector("tr");
    row.dataset.id = order.id;
    row.querySelector('[data-field="id"]').textContent = order.id;
    row.querySelector('[data-field="customer"]').textContent =
      order.user?.name || order.customer?.name || order.user_id;
    row.querySelector('[data-field="date"]').textContent = formatDate(
      order.created_at,
    );
    row.querySelector('[data-field="total"]').textContent = formatPrice(
      order.total,
    );
    const orderStatus = row.querySelector('[data-field="status"]');
    orderStatus.textContent = statusLabel(order.status);
    orderStatus.dataset.status = order.status;
    const ship = row.querySelector('[data-action="ship"]'),
      deliver = row.querySelector('[data-action="deliver"]');
    if (order.status === "paid") {
      ship.classList.remove("d-none");
      ship.addEventListener("click", () => transition(order.id, "shipped"));
    }
    if (order.status === "shipped") {
      deliver.classList.remove("d-none");
      deliver.addEventListener("click", () =>
        transition(order.id, "delivered"),
      );
    }
    body.append(node);
  });
}
async function refreshAll() {
  try {
    await Promise.all([loadCategories(), loadProducts(), loadOrders()]);
  } catch (e) {
    showAlert(
      $("#adminAlert"),
      e.response?.data?.message || "Erro ao carregar o painel.",
    );
  }
}
function confirm(message, action) {
  $("#confirmText").textContent = message;
  confirmAction = action;
  bootstrap.Modal.getOrCreateInstance($("#confirmModal")).show();
}
$("#confirmYes")?.addEventListener("click", async () => {
  if (!confirmAction) return;
  try {
    await confirmAction();
    toast("Operação concluída.");
  } catch (e) {
    showAlert(
      $("#adminAlert"),
      e.response?.data?.message || "Operação não realizada.",
    );
  } finally {
    confirmAction = null;
    bootstrap.Modal.getInstance($("#confirmModal"))?.hide();
  }
});
function editProduct(product) {
  $("#productForm").reset();
  hideAlert($("#productFormAlert"));
  $("#productModalTitle").textContent = "Editar produto";
  currentProductImage = product.image_url || null;
  showImagePreview(currentProductImage);
  $("#productId").value = product.id;
  $("#name").value = product.name;
  $("#description").value = product.description || "";
  $("#price").value = product.price;
  $("#stock").value = product.stock;
  $("#category_id").value = product.category_id;
  $("#active").checked = Boolean(product.active);
  productModal().show();
}
function editCategory(category) {
  $("#categoryForm").reset();
  hideAlert($("#categoryFormAlert"));
  $("#categoryModalTitle").textContent = "Editar categoria";
  $("#categoryId").value = category.id;
  $("#category_name").value = category.name;
  $("#category_description").value = category.description || "";
  categoryModal().show();
}
$("#newProductBtn")?.addEventListener("click", () => {
  $("#productForm").reset();
  currentProductImage = null;
  showImagePreview(null);
  $("#productId").value = "";
  $("#productModalTitle").textContent = "Novo produto";
  hideAlert($("#productFormAlert"));
});
$("#image")?.addEventListener("change", (event) => {
  const file = event.target.files?.[0];
  if (file) {
    showImagePreview(URL.createObjectURL(file));
  } else {
    showImagePreview(currentProductImage);
  }
});
$("#productModal")?.addEventListener("hidden.bs.modal", () => {
  if (imagePreviewUrl) {
    URL.revokeObjectURL(imagePreviewUrl);
    imagePreviewUrl = null;
  }
});
$("#newCategoryBtn")?.addEventListener("click", () => {
  $("#categoryForm").reset();
  $("#categoryId").value = "";
  $("#categoryModalTitle").textContent = "Nova categoria";
  hideAlert($("#categoryFormAlert"));
});
$("#productForm")?.addEventListener("submit", async (e) => {
  e.preventDefault();
  const id = $("#productId").value;
  const data = new FormData();
  ["name", "description", "price", "stock", "category_id"].forEach((key) =>
    data.append(key, $(`#${key}`).value),
  );
  data.append("active", $("#active").checked ? "1" : "0");
  const file = $("#image").files[0];
  if (file) data.append("image", file);
  try {
    if (id) await updateProduct(id, data);
    else await createProduct(data);
    productModal().hide();
    toast("Produto salvo.");
    await refreshAll();
  } catch (error) {
    showAlert(
      $("#productFormAlert"),
      error.response?.data?.message || "Erro ao salvar produto.",
    );
  }
});
$("#categoryForm")?.addEventListener("submit", async (e) => {
  e.preventDefault();
  const id = $("#categoryId").value;
  const data = {
    name: $("#category_name").value.trim(),
    description: $("#category_description")?.value.trim() || "",
  };
  try {
    if (id) await updateCategory(id, data);
    else await createCategory(data);
    categoryModal().hide();
    toast("Categoria salva.");
    await refreshAll();
  } catch (error) {
    showAlert(
      $("#categoryFormAlert"),
      error.response?.data?.message || "Erro ao salvar categoria.",
    );
  }
});
async function transition(id, status) {
  try {
    await updateOrderStatus(id, status);
    toast("Status do pedido atualizado.");
    await loadOrders();
  } catch (e) {
    showAlert(
      $("#adminAlert"),
      e.response?.data?.message || "Não foi possível alterar o status.",
    );
  }
}
$("#orderStatusFilter")?.addEventListener("change", loadOrders);
document.addEventListener("DOMContentLoaded", async () => {
  if (!(await requireAdmin())) return;
  await refreshAll();
});

document.addEventListener("DOMContentLoaded", initNavigation);
