import axios from 'axios';

const api = axios.create({
    baseURL: `https://backend.gnswhatssender.com.br`,
})

export default api;