// PARTIE MEMBRE 4 : carte résumant un profil dans la liste de l'annuaire.
// Tout le bloc est un lien : un clic ouvre la page de détail /profils/:id.
import { Link } from 'react-router-dom'
import { Avatar } from './ui'

// « profil » : objet renvoyé par l'API (voir toPublic() dans Profil.php)
export default function CarteProfil({ profil }) {
  return (
    <Link to={`/profils/${profil.id}`} className="carte block transition hover:border-teal-600">
      <div className="flex items-center gap-3">
        <Avatar profil={profil} />
        <div className="min-w-0">
          {/* « truncate » coupe le texte trop long avec des points de suspension */}
          <p className="truncate font-semibold text-slate-900">{profil.prenoms} {profil.nom}</p>
          <p className="truncate text-sm text-slate-600">{profil.metier}</p>
        </div>
      </div>
      <div className="mt-3 flex flex-wrap gap-1.5 text-xs">
        <span className="rounded bg-teal-50 px-2 py-0.5 text-teal-800">{profil.categorie}</span>
        <span className="rounded bg-slate-100 px-2 py-0.5 text-slate-700">{profil.secteur}</span>
      </div>
      <p className="mt-3 text-xs text-slate-500">{profil.localisation} - {profil.niveau_etude}</p>
    </Link>
  )
}
