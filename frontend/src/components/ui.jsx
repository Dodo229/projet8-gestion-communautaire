// FOURNI PAR LE MEMBRE 1 : composants d'interface partagés (version réduite au prototype).
// Dans le projet complet, ce fichier contient aussi Modal, Case, StatutBadge, etc.
// VOS composants dans ce fichier : Pagination et Avatar (à modifier avec précaution : d'autres membres les utilisent).
//
// REGLE IMPORTANTE : ces composants sont définis ICI, en dehors des pages. Définis à l'intérieur
// d'un composant de page, React les recréerait à chaque frappe et les champs perdraient le focus
// (on ne pourrait écrire qu'une lettre à la fois).
import { useState } from 'react'
import { BASE } from '../api/client'

// ---------- Messages ----------  type : 'erreur' | 'succes' | 'info'
export function Alerte({ type = 'info', messages }) {
  const liste = [].concat(messages || []).filter(Boolean)
  if (liste.length === 0) return null
  const styles = {
    erreur: 'border-red-200 bg-red-50 text-red-800',
    succes: 'border-teal-200 bg-teal-50 text-teal-900',
    info: 'border-slate-200 bg-slate-50 text-slate-700',
  }
  return (
    <div role={type === 'erreur' ? 'alert' : 'status'} className={`rounded-md border px-4 py-3 text-sm ${styles[type]}`}>
      {liste.length === 1 ? liste[0] : <ul className="list-disc pl-5">{liste.map((m, i) => <li key={i}>{m}</li>)}</ul>}
    </div>
  )
}

export const Chargement = () => <p className="py-8 text-center text-sm text-slate-500">Chargement...</p>

// ---------- PAGINATION (membre 4) ----------
// « page » est l'objet renvoyé par Laravel : { current_page, last_page, total, data: [...] }.
// Le composant ne charge rien lui-même : il affiche les boutons et prévient le parent
// (onChange) quand l'utilisateur change de page. C'est la page Annuaire qui recharge les données.
export function Pagination({ page, onChange }) {
  if (!page || page.last_page <= 1) return null   // une seule page : rien à afficher
  return (
    <nav aria-label="Pagination" className="mt-5 flex items-center justify-center gap-3 text-sm">
      <button className="btn-secondaire" disabled={page.current_page <= 1} onClick={() => onChange(page.current_page - 1)}>
        Précédent
      </button>
      <span className="text-slate-600">Page {page.current_page} sur {page.last_page}</span>
      <button className="btn-secondaire" disabled={page.current_page >= page.last_page} onClick={() => onChange(page.current_page + 1)}>
        Suivant
      </button>
    </nav>
  )
}

// ---------- Champs de formulaire ----------
// Utilisation : <Champ label="Nom" value={...} onChange={...} />
// Les propriétés non listées (type, placeholder...) sont transmises à <input> grâce à « ...proprietes ».
export function Champ({ label, aide, ...proprietes }) {
  return (
    <label className="block">
      <span className="etiquette">{label}</span>
      <input className="champ" {...proprietes} />
      {aide && <span className="mt-1 block text-xs text-slate-500">{aide}</span>}
    </label>
  )
}

// Liste déroulante. « elements » est un tableau d'objets { id, libelle }.
export function Liste({ label, elements, vide = 'Choisir...', ...proprietes }) {
  return (
    <label className="block">
      <span className="etiquette">{label}</span>
      <select className="champ" {...proprietes}>
        <option value="">{vide}</option>
        {elements.map((e) => <option key={e.id} value={e.id}>{e.libelle}</option>)}
      </select>
    </label>
  )
}

// ---------- AVATAR (membre 4) : photo du profil, ou initiales s'il n'y en a pas ----------
export function Avatar({ profil, taille = 'h-12 w-12' }) {
  const [erreur, setErreur] = useState(false)   // true si l'image n'a pas pu se charger
  const initiales = `${profil.prenoms?.[0] ?? ''}${profil.nom?.[0] ?? ''}`.toUpperCase()

  if (profil.a_photo && !erreur) {
    return (
      <img
        src={`${BASE}/api/v1/profils/${profil.id}/photo`}
        alt={`Photo de ${profil.prenoms} ${profil.nom}`}
        onError={() => setErreur(true)}     // image introuvable : on bascule sur les initiales
        className={`${taille} flex-none rounded-full object-cover`}
      />
    )
  }
  return (
    <div className={`${taille} flex flex-none items-center justify-center rounded-full bg-teal-100 text-sm font-semibold text-teal-800`} aria-hidden="true">
      {initiales}
    </div>
  )
}

export function TitrePage({ titre, sousTitre, children }) {
  return (
    <div className="mb-6 flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 className="text-2xl font-semibold">{titre}</h1>
        {sousTitre && <p className="mt-1 text-sm text-slate-600">{sousTitre}</p>}
      </div>
      {children}
    </div>
  )
}
