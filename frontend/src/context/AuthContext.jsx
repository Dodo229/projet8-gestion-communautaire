// FOURNI PAR LE MEMBRE 2 (comptes et sécurité) : copie pour pouvoir tester la connexion.
// Contexte d'authentification : stocke l'utilisateur connecté et le met à disposition
// de tous les composants via le hook useAuth().
import { createContext, useContext, useEffect, useState } from 'react'
import api, { csrf } from '../api/client'

const AuthContext = createContext(null)

// Hook : dans n'importe quel composant, « const { user, login, logout } = useAuth() »
export const useAuth = () => useContext(AuthContext)

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null)    // null = visiteur non connecté
  const [pret, setPret] = useState(false)   // false tant qu'on n'a pas vérifié la session

  // Au chargement du site : on demande à l'API si une session est déjà ouverte
  useEffect(() => {
    api.get('/moi')
      .then((r) => setUser(r.data))
      .catch(() => setUser(null))           // 401 = personne n'est connecté : cas normal
      .finally(() => setPret(true))
  }, [])

  const login = async (email, password) => {
    await csrf()                                        // 1. cookie CSRF
    const r = await api.post('/connexion', { email, password })   // 2. connexion
    setUser(r.data)
    return r.data
  }

  const logout = async () => {
    await api.post('/deconnexion')
    setUser(null)
  }

  return <AuthContext.Provider value={{ user, pret, login, logout }}>{children}</AuthContext.Provider>
}
