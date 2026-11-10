
import { http } from './http.js';

export async function getAddresses() {
    return http.get('/addresses');
}

export async function createAddress(data) {
    return http.post('/addresses', data);
}

export async function setDefaultAddress(addressId) {
    return http.patch(`/addresses/${addressId}/default`);
}
