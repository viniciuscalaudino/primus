export const $ = (selector, root = document) => root.querySelector(selector);
export const $$ = (selector, root = document) => [
  ...root.querySelectorAll(selector),
];

export function showAlert(container, message, type = "danger") {
  if (!container) return;
  container.textContent = message;
  container.className = `alert alert-${type}`;
  container.classList.remove("d-none");
}

export function hideAlert(container) {
  if (!container) return;
  container.textContent = "";
  container.classList.add("d-none");
}

export function getErrorMessage(
  error,
  fallback = "Ocorreu um erro. Tente novamente.",
) {
  const errors = error.response?.data?.errors;
  const validationMessage =
    errors && Object.values(errors).flat().find(Boolean);

  return validationMessage || error.response?.data?.message || fallback;
}

export function getValidationErrors(error) {
  return error.response?.data?.errors || {};
}

export function unwrapList(response, key) {
  const body = response?.data ?? {};
  if (Array.isArray(body)) return body;
  if (Array.isArray(body[key])) return body[key];
  if (Array.isArray(body.data)) return body.data;
  if (Array.isArray(body.data?.[key])) return body.data[key];
  return [];
}

export function getRecord(response, key) {
  const body = response?.data ?? {};
  return body[key] ?? body.data ?? body;
}

export function toast(message, type = "success") {
  const area = $("#toastArea");
  if (!area || !window.bootstrap?.Toast) {
    console.info(message);
    return;
  }
  const element = document.createElement("div");
  element.className = `toast text-bg-${type} border-0`;
  element.setAttribute("role", "status");
  element.innerHTML =
    '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
  $(".toast-body", element).textContent = message;
  area.append(element);
  const instance = new bootstrap.Toast(element, { delay: 3000 });
  instance.show();
  element.addEventListener("hidden.bs.toast", () => element.remove());
}

export function queryId() {
  return new URLSearchParams(location.search).get("id");
}

export function setButtonLoading(button, loading, label = "Salvando...") {
  if (!button) return;
  if (loading) {
    button.dataset.originalText ??= button.textContent;
    button.disabled = true;
    button.textContent = label;
  } else {
    button.disabled = false;
    if (button.dataset.originalText)
      button.textContent = button.dataset.originalText;
  }
}
