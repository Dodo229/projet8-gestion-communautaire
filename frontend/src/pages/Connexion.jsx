// FOURNI PAR LE MEMBRE 2 (comptes et sécurité) : copie pour pouvoir tester la connexion.
// Page de connexion (authentification par email et mot de passe).
import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { messagesErreur } from '../api/client'
import { Alerte, Champ } from '../components/ui'
import { useAuth } from '../context/AuthContext'

export default function Connexion() {
  const { login } = useAuth()
  const navigate = useNavigate()
  const [f, setF] = useState({ email: '', password: '' })
  const [erreurs, setErreurs] = useState([])
  const [enCours, setEnCours] = useState(false)

  // Fabrique un gestionnaire pour un champ : maj('email') met à jour f.email
  const maj = (cle) => (e) => setF((precedent) => ({ ...precedent, [cle]: e.target.value }))

  const envoyer = async (e) => {
    e.preventDefault()
    setEnCours(true)
    setErreurs([])
    try {
      const u = await login(f.email, f.password)
      // Un administrateur arrive sur son tableau de bord, un membre sur son profil
      navigate('/annuaire')   // prototype : pas d'espace membre, on retourne à l'annuaire
    } catch (x) {
      setErreurs(messagesErreur(x))
    } finally {
      setEnCours(false)
    }
  }

  return (
    <div className="mx-auto max-w-sm">
      <form onSubmit={envoyer} className="carte space-y-4">
        <h1 className="text-xl font-semibold">Connexion</h1>
        <Alerte type="erreur" messages={erreurs} />
        <Champ label="Adresse email" type="email" required autoComplete="email" value={f.email} onChange={maj('email')} />
        <Champ label="Mot de passe" type="password" required autoComplete="current-password" value={f.password} onChange={maj('password')} />
        <button className="btn-primaire w-full" disabled={enCours}>{enCours ? 'Connexion...' : 'Se connecter'}</button>
        <p className="text-center text-sm text-slate-600">
          Pas encore de compte ? <Link to="/inscription" className="font-medium text-teal-700 hover:underline">S’inscrire</Link>
        </p>
      </form>
    </div>
  )
}
