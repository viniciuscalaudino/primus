export function formatPrice(value) {
    return Number(value).toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    });
}


export function formatDate(value) {
    return new Date(value).toLocaleDateString('pt-BR');
}


