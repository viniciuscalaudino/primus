export function formatPrice(value) {
    return Number(value).toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    });
}


export function formatDate(value) {
    return new Date(value).toLocaleDateString('pt-BR');
}



const STATUS_LABELS = {
    awaiting_payment: 'Aguardando pagamento',
    paid: 'Pago',
    shipped: 'Enviado',
    delivered: 'Entregue',
    cancelled: 'Cancelado',
};

export function statusLabel(status) {
    return STATUS_LABELS[status] || status;
}
