import { initNavigation } from "../components/navigation.js";
import { getProducts } from "../api/products.js";
import { getCategories } from "../api/categories.js";
import { addToCart, getCart } from "../api/cart.js";
import { formatPrice } from "../utils/format.js";
import { getErrorMessage, unwrapList, toast } from "../utils/dom.js";

async function loadHome() {
  try {
    const [categoryResponse, productResponse] = await Promise.all([
      getCategories(),
      getProducts({ sort: "recent", per_page: 4 }),
    ]);
    let cartQuantities = new Map();
    try {
      const cartResponse = await getCart();
      const cart = cartResponse.data.cart || cartResponse.data;
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
    const categories = unwrapList(categoryResponse, "categories");
    const products = unwrapList(productResponse, "products");
    const categoryList = document.querySelector("#categoriesList");
    const productList = document.querySelector("#novidadesList");
    if (categoryList) {
      categoryList.replaceChildren();
      const categoryStyles = [
        { match: /prote[ií]na/i, theme: "protein", icon: "bi-person-arms-up" },
        { match: /creatina|amino/i, theme: "amino", icon: "bi-lightning-charge" },
        { match: /pr[eé].?treino|energia/i, theme: "energy", icon: "bi-lightning-charge-fill" },
        { match: /acess[oó]rio/i, theme: "accessory", icon: "bi-bag" },
      ];
      categories.slice(0, 4).forEach((category, index) => {
        const col = document.createElement("div");
        col.className = "col";
        const link = document.createElement("a");
        const style = categoryStyles.find(({ match }) =>
          match.test(category.name),
        );
        link.className = `cat-card${style ? ` cat-card--${style.theme}` : ""}`;
        link.href = `products.html?category=${encodeURIComponent(category.id)}`;
        const image = document.createElement("div");
        image.className = "cat-img";
        const number = document.createElement("span");
        number.className = "cat-index";
        number.textContent = String(index + 1).padStart(2, "0");
        const icon = document.createElement("i");
        icon.className = `cat-icon bi ${style?.icon || "bi-grid"}`;
        icon.setAttribute("aria-hidden", "true");
        image.append(number, icon);

        const info = document.createElement("div");
        info.className = "cat-info";
        const title = document.createElement("strong");
        title.textContent = category.name;
        const action = document.createElement("span");
        action.className = "cat-action";
        action.append(
          document.createTextNode("Explorar coleção"),
          Object.assign(document.createElement("i"), {
            className: "bi bi-arrow-up-right",
          }),
        );
        info.append(title, action);
        link.append(image, info);
        col.append(link);
        categoryList.append(col);
      });
    }
    if (productList) {
      productList.replaceChildren();
      products.slice(0, 4).forEach((product) => {
        const card = document.createElement("article");
        card.className = "product-card";
        const link = document.createElement("a");
        link.href = `product.html?id=${product.id}`;
        const imageContainer = document.createElement("div");
        imageContainer.className = "product-img";
        const placeholder = document.createElement("i");
        placeholder.className = "bi bi-image";
        if (product.image_url) {
          const image = document.createElement("img");
          image.src = product.image_url;
          image.alt = product.name;
          image.loading = "lazy";
          image.addEventListener("error", () => image.remove());
          imageContainer.append(image);
        } else {
          imageContainer.append(placeholder);
        }
        const name = document.createElement("h3");
        name.textContent = product.name;
        const price = document.createElement("p");
        price.textContent = formatPrice(product.price);
        const body = document.createElement("div");
        body.className = "product-body";
        body.append(name, price);
        link.append(imageContainer, body);
        const button = document.createElement("button");
        button.type = "button";
        button.className = "btn btn-primus btn-add";
        button.textContent = "Adicionar ao carrinho";
        let currentQuantity = Number(
          cartQuantities.get(Number(product.id)) || 0,
        );
        const alreadyAtStockLimit = currentQuantity >= Number(product.stock);
        button.disabled = Number(product.stock) < 1 || alreadyAtStockLimit;
        if (alreadyAtStockLimit && Number(product.stock) > 0) {
          button.textContent = "Limite do estoque no carrinho";
        }
        button.addEventListener("click", async () => {
          if (currentQuantity >= Number(product.stock)) {
            button.disabled = true;
            button.textContent = "Limite do estoque no carrinho";
            toast(
              "Você já adicionou ao carrinho todo o estoque disponível.",
              "warning",
            );
            return;
          }
          button.disabled = true;
          try {
            await addToCart(product.id);
            currentQuantity += 1;
            toast("Produto adicionado ao carrinho.");
          } catch (e) {
            toast(
              getErrorMessage(e, "Não foi possível adicionar ao carrinho."),
              "danger",
            );
          } finally {
            const limitReached = currentQuantity >= Number(product.stock);
            button.disabled = Number(product.stock) < 1 || limitReached;
            if (limitReached)
              button.textContent = "Limite do estoque no carrinho";
          }
        });
        card.append(link, button);
        productList.append(card);
      });
    }
  } catch (error) {
    console.error("Erro ao carregar a página inicial:", error);
  }
}
document.addEventListener("DOMContentLoaded", loadHome);

document.addEventListener("DOMContentLoaded", initNavigation);
