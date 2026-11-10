import { getProducts } from '../api/products.js';
import { getCategories } from '../api/categories.js';
import { formatPrice } from '../utils/format.js';

const categoryFilter = document.querySelector('#categoryFilter');
const productGrid = document.querySelector('#productGrid');


async function loadCategories() {
    try {
        const response = await getCategories();

        console.log(response.data);

    } catch (error) {
        console.error('Erro ao carregar categorias:', error);
    }
}

loadCategories();
