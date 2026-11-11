import { requireAuth } from "../utils/guard.js";
import { getCurrentUser, updateProfile } from "../api/auth.js";
import { initNavigation } from "../components/navigation.js";
import {
  getAddresses,
  createAddress,
  setDefaultAddress,
} from "../api/addresses.js";
import { $, getRecord, unwrapList, showAlert, toast } from "../utils/dom.js";
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
async function loadProfile() {
  await initNavigation();
  if (!(await requireAuth())) return;
  try {
    const user = getRecord(await getCurrentUser(), "user");
    $("#profileName").value = user.name;
    $("#profileEmail").value = user.email;
    const addresses = unwrapList(await getAddresses(), "addresses"),
      list = $("#addressList");
    list.replaceChildren();
    $("#addressEmpty")?.classList.toggle("d-none", addresses.length > 0);
    addresses.forEach((address) => {
      const card = $("#addressCardTemplate").content.cloneNode(true),
        root = card.firstElementChild;
      root.querySelector('[data-field="recipient"]').textContent =
        address.recipient_name;
      root.querySelector('[data-field="line"]').textContent = [
        `${address.street}, ${address.number}`,
        address.complement,
        address.neighborhood,
        `${address.city}/${address.state}`,
        address.zip_code,
      ]
        .filter(Boolean)
        .join(" · ");
      root
        .querySelector('[data-field="defaultBadge"]')
        ?.classList.toggle("d-none", !address.is_default);
      const button = root.querySelector('[data-action="set-default"]');
      if (button) {
        button.disabled = Boolean(address.is_default);
        button.addEventListener("click", async () => {
          try {
            await setDefaultAddress(address.id);
            await loadProfile();
          } catch (error) {
            showAlert(
              $("#addressAlert"),
              error.response?.data?.message ||
                "Não foi possível definir o endereço padrão.",
            );
          }
        });
      }
      list.append(card);
    });
  } catch (e) {
    console.error("Erro ao carregar perfil:", e);
  }
}
$("#addressForm")?.addEventListener("submit", async (e) => {
  e.preventDefault();
  const data = Object.fromEntries(
    fields.map((key) => [
      key,
      document.getElementById(key)?.value.trim() || "",
    ]),
  );
  try {
    await createAddress(data);
    e.target.reset();
    toast("Endereço cadastrado.");
    await loadProfile();
  } catch (error) {
    showAlert(
      $("#addressAlert"),
      error.response?.data?.message || "Erro ao cadastrar endereço.",
    );
  }
});
document.addEventListener("DOMContentLoaded", loadProfile);

$("#profileForm")?.addEventListener("submit", async (e) => {
  e.preventDefault();
  document
    .querySelectorAll("#profileForm .is-invalid")
    .forEach((el) => el.classList.remove("is-invalid"));
  document
    .querySelectorAll("#profileForm [data-error-for]")
    .forEach((el) => (el.textContent = ""));
  $("#profileAlert")?.classList.add("d-none");
  try {
    await updateProfile({
      name: $("#profileName").value.trim(),
      email: $("#profileEmail").value.trim(),
    });
    toast("Dados atualizados.");
    await initNavigation();
  } catch (error) {
    const errors = error.response?.data?.errors;
    if (errors) {
      Object.entries(errors).forEach(([field, messages]) => {
        const input = document.querySelector(`#profileForm [name="${field}"]`);
        const feedback = document.querySelector(`#profileForm [data-error-for="${field}"]`);
        input?.classList.add("is-invalid");
        if (feedback) feedback.textContent = messages[0];
      });
    } else {
      showAlert($("#profileAlert"), error.response?.data?.message || "Não foi possível salvar.");
    }
  }
});
