// Page affichée quand l'adresse demandée n'existe pas (erreur 404).
import { Link } from 'react-router-dom'

export default function NonTrouve() {
  return (
    <div className="py-16 text-center">
      <h1 className="text-3xl font-semibold">Page introuvable</h1>
      <p className="mt-2 text-slate-600">L’adresse demandée n’existe pas ou a été déplacée.</p>
      <Link to="/" className="btn-primaire mt-6">Retour à l’accueil</Link>
    </div>
  )
}
