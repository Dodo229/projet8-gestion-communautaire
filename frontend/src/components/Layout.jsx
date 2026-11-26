// FOURNI PAR LE MEMBRE 1 : structure commune des pages (version réduite au prototype).
import { Link, NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

const lien = ({ isActive }) =>
  `rounded-md px-3 py-2 text-sm font-medium ${isActive ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-100'}`

export default function Layout() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()

  return (
    <div className="flex min-h-screen flex-col">
      {/* Bandeau propre au prototype : il rappelle comment tester la visibilité du téléphone */}
      <div className="bg-amber-50 px-4 py-2 text-center text-xs text-amber-900">
        Prototype du membre 4 (recherche et consultation). Pour voir les téléphones masqués, connectez-vous avec
        {' '}<strong>houngbo0@exemple.local</strong> / <strong>Demo-2026-pass</strong>.
      </div>

      <header className="border-b border-slate-200 bg-white">
        <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
          <Link to="/annuaire" className="font-semibold text-slate-900">Annuaire des acteurs économiques</Link>
          <nav className="flex items-center gap-1">
            <NavLink to="/annuaire" className={lien}>Annuaire</NavLink>
            {user
              ? <button className="rounded-md px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
                  onClick={async () => { await logout(); navigate('/annuaire') }}>Déconnexion ({user.email})</button>
              : <NavLink to="/connexion" className={lien}>Connexion</NavLink>}
          </nav>
        </div>
      </header>

      {/* Outlet : emplacement où s'affiche la page correspondant à l'adresse */}
      <main className="mx-auto w-full max-w-6xl flex-1 px-4 py-8"><Outlet /></main>
    </div>
  )
}
