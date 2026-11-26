// PARTIE MEMBRE 4 : page détaillée d'un profil (exigence B : « bio, compétences, expériences, contacts »).
import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import api, { formaterDate } from '../api/client'
import { Alerte, Avatar, Chargement } from '../components/ui'
import { useAuth } from '../context/AuthContext'

export default function ProfilDetail() {
  const { id } = useParams()          // lit l'identifiant dans l'adresse /profils/:id
  const { user } = useAuth()          // utilisateur connecté (ou null)
  const [profil, setProfil] = useState(null)
  const [introuvable, setIntrouvable] = useState(false)

  // On recharge le profil quand l'id change, et aussi quand l'utilisateur se connecte ou se déconnecte :
  // le téléphone apparaît ou disparaît selon la règle RG6, calculée par le serveur.
  useEffect(() => {
    let annule = false
    setProfil(null)
    setIntrouvable(false)
    api.get(`/profils/${id}`)
      .then((r) => { if (!annule) setProfil(r.data) })
      .catch(() => { if (!annule) setIntrouvable(true) })   // 404 : profil inexistant ou non validé
    return () => { annule = true }
  }, [id, user])

  if (introuvable) return <Alerte type="erreur" messages="Ce profil est introuvable." />
  if (!profil) return <Chargement />

  return (
    <article className="mx-auto max-w-3xl space-y-6">
      <Link to="/annuaire" className="text-sm font-medium text-teal-700 hover:underline">Retour à l’annuaire</Link>

      <header className="carte flex flex-wrap items-center gap-4">
        <Avatar profil={profil} taille="h-20 w-20" />
        <div>
          <h1 className="text-2xl font-semibold">{profil.prenoms} {profil.nom}</h1>
          <p className="text-slate-600">{profil.metier}{profil.entreprise ? ` - ${profil.entreprise}` : ''}</p>
          <div className="mt-2 flex flex-wrap gap-2 text-xs">
            <span className="rounded bg-teal-50 px-2 py-0.5 text-teal-800">{profil.categorie}</span>
            <span className="rounded bg-slate-100 px-2 py-0.5 text-slate-700">{profil.secteur}</span>
          </div>
        </div>
      </header>

      {profil.bio && (
        <section className="carte">
          <h2 className="mb-2 font-semibold">Présentation</h2>
          {/* whitespace-pre-line conserve les retours à la ligne saisis par l'utilisateur */}
          <p className="whitespace-pre-line text-sm text-slate-700">{profil.bio}</p>
        </section>
      )}

      <section className="carte">
        <h2 className="mb-3 font-semibold">Informations</h2>
        <dl className="grid gap-3 text-sm sm:grid-cols-2">
          <div><dt className="text-slate-500">Niveau d’étude</dt><dd>{profil.niveau_etude}</dd></div>
          <div><dt className="text-slate-500">Localisation</dt><dd>{profil.localisation}</dd></div>
          <div>
            <dt className="text-slate-500">Téléphone</dt>
            {/* « ?? » : si le serveur a renvoyé null (téléphone masqué), on affiche le message de remplacement */}
            <dd>{profil.telephone ?? <span className="text-slate-500">Réservé aux membres connectés</span>}</dd>
          </div>
        </dl>
      </section>

      {profil.competences.length > 0 && (
        <section className="carte">
          <h2 className="mb-3 font-semibold">Compétences</h2>
          <div className="flex flex-wrap gap-2">
            {/* Chaque compétence est un lien vers l'annuaire filtré sur cette compétence */}
            {profil.competences.map((c) => (
              <Link key={c} to={`/annuaire?competence=${encodeURIComponent(c)}`}
                className="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-700 hover:bg-teal-50 hover:text-teal-800">
                {c}
              </Link>
            ))}
          </div>
        </section>
      )}

      {profil.experiences.length > 0 && (
        <section className="carte">
          <h2 className="mb-3 font-semibold">Expériences</h2>
          <ul className="space-y-4">
            {profil.experiences.map((e) => (
              <li key={e.id} className="border-l-2 border-teal-600 pl-4">
                <p className="font-medium">{e.poste}{e.organisation ? `, ${e.organisation}` : ''}</p>
                <p className="text-xs text-slate-500">
                  {formaterDate(e.debut)}{e.debut || e.fin ? ' - ' : ''}{e.fin ? formaterDate(e.fin) : (e.debut ? 'aujourd’hui' : '')}
                </p>
                {e.description && <p className="mt-1 text-sm text-slate-700">{e.description}</p>}
              </li>
            ))}
          </ul>
        </section>
      )}
    </article>
  )
}
