// FOURNI PAR LE MEMBRE 1 (chef de projet) : client HTTP partagé par toute l'équipe.
// Toutes les communications avec l'API Laravel passent par ici.
import axios from 'axios'

// Adresse de l'API. En développement : http://localhost:8000 (voir le fichier .env).
export const BASE = import.meta.env.VITE_API_URL ?? 'http://localhost:8000'

const api = axios.create({
  baseURL: `${BASE}/api/v1`,
  withCredentials: true,   // envoie le cookie de session avec chaque requête
  withXSRFToken: true,
  headers: { Accept: 'application/json' },
})

// Récupère le cookie CSRF (exigence de Laravel Sanctum) avant la connexion
export const csrf = () => axios.get(`${BASE}/sanctum/csrf-cookie`, { withCredentials: true })

// Transforme une erreur axios en liste de messages lisibles
export function messagesErreur(erreur) {
  const rep = erreur?.response?.data
  if (rep?.errors) return Object.values(rep.errors).flat()
  if (rep?.message) return [rep.message]
  return ['Impossible de joindre le serveur. Vérifiez que l’API est lancée et réessayez.']
}

// Met en forme une date ISO (« 2026-11-22 ») en français (« 22/11/2026 »)
export const formaterDate = (d) => (d ? new Date(d).toLocaleDateString('fr-FR') : '')

export default api
