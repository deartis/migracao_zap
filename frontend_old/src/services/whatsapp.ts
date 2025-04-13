import axios from 'axios';

const whatsapp = axios.create({
    baseURL: `https://whatsapp.gnswhatssender.com.br`,
})

export default whatsapp;