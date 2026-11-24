import axios from 'axios';

// Adresse du back-end Laravel (lancé avec : php artisan serve)
const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: { Accept: 'application/json' },
});

// Ajoute automatiquement le jeton de connexion à chaque demande, s'il existe
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;