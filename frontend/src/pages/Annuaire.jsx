// PARTIE MEMBRE 4 : page de recherche (exigence B du sujet).
// Recherche libre, filtres, tri, pagination, état de chargement, résultat vide et erreur.
//
// IDEE CENTRALE : les filtres sont stockés DANS L'ADRESSE de la page
//   /annuaire?secteur_id=3&q=menuisier&page=2
// Avantages :
//   - on peut copier l'adresse pour partager ou retrouver une recherche ;
//   - le bouton « retour » du navigateur annule le dernier filtre ;
//   - les liens venant d'ailleurs (page d'accueil, catégories, détail d'un profil) ouvrent
//     l'annuaire déjà filtré.
//
// FLUX D'UNE RECHERCHE :
//   1. l'utilisateur choisit un filtre  -> changer() modifie l'adresse
//   2. l'adresse (params) a changé      -> le useEffect de chargement se déclenche
//   3. api.get('/profils', { params })  -> Laravel : FiltreProfils -> SQL -> JSON
//   4. setResultat(...)                 -> React réaffiche la liste
import { useEffect, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import api, { messagesErreur } from '../api/client'
import CarteProfil from '../components/CarteProfil'
import { Alerte, Champ, Chargement, Liste, Pagination, TitrePage } from '../components/ui'

export default function Annuaire() {
  // params = paramètres de l'adresse (lecture) ; setParams = les modifier
  const [params, setParams] = useSearchParams()

  const [ref, setRef] = useState({ categories: [], secteurs: [], niveaux: [] })   // listes des menus déroulants
  const [resultat, setResultat] = useState(null)    // réponse paginée de l'API ; null = chargement en cours
  const [erreur, setErreur] = useState(null)        // message d'erreur éventuel
  // Texte tapé dans la recherche : copie locale, pour que la frappe reste fluide pendant l'attente de 300 ms
  const [recherche, setRecherche] = useState(params.get('q') || '')

  // 1) Listes des filtres : chargées une seule fois (tableau de dépendances vide)
  useEffect(() => { api.get('/references').then((r) => setRef(r.data)).catch(() => {}) }, [])

  // Modifie UN paramètre de l'adresse. Retour à la page 1 quand un filtre change
  // (sinon on pourrait demander la page 3 d'un résultat qui n'en a plus que 1).
  const changer = (cle, valeur) => {
    const suivant = new URLSearchParams(params)
    valeur ? suivant.set(cle, valeur) : suivant.delete(cle)
    if (cle !== 'page') suivant.delete('page')
    setParams(suivant)
  }

  // 2) ANTI-REBOND (« debounce ») de la recherche libre.
  //    Sans lui, chaque lettre tapée déclencherait un appel API (« m », « me », « men »...).
  //    On attend 300 ms sans frappe avant de modifier l'adresse. La fonction retournée
  //    (« nettoyage ») annule le minuteur précédent si l'utilisateur tape encore.
  useEffect(() => {
    if (recherche === (params.get('q') || '')) return    // rien à faire si c'est déjà à jour
    const minuteur = setTimeout(() => changer('q', recherche), 300)
    return () => clearTimeout(minuteur)
  }, [recherche])

  // 3) Chargement des résultats à chaque changement de l'adresse
  useEffect(() => {
    let annule = false          // protège contre les réponses qui arrivent dans le désordre
    setResultat(null)
    setErreur(null)

    api.get('/profils', { params: Object.fromEntries(params) })
      .then((r) => { if (!annule) setResultat(r.data) })
      .catch((x) => { if (!annule) setErreur(messagesErreur(x)) })

    // Nettoyage : si l'adresse change avant la fin de la requête, on ignore l'ancienne réponse
    return () => { annule = true }
  }, [params])

  const reinitialiser = () => { setRecherche(''); setParams({}) }
  const competence = params.get('competence')

  return (
    <>
      <TitrePage titre="Annuaire" sousTitre="Recherchez parmi les profils validés par la mairie." />

      {/* ---------- Filtres ---------- */}
      <div className="carte mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <Champ label="Recherche" value={recherche} onChange={(e) => setRecherche(e.target.value)} placeholder="Nom, métier..." />
        <Liste label="Catégorie" vide="Toutes" elements={ref.categories}
          value={params.get('categorie_id') || ''} onChange={(e) => changer('categorie_id', e.target.value)} />
        <Liste label="Secteur d’activité" vide="Tous" elements={ref.secteurs}
          value={params.get('secteur_id') || ''} onChange={(e) => changer('secteur_id', e.target.value)} />
        <Liste label="Niveau d’étude" vide="Tous" elements={ref.niveaux}
          value={params.get('niveau_etude_id') || ''} onChange={(e) => changer('niveau_etude_id', e.target.value)} />
        <Champ label="Localisation" value={params.get('localisation') || ''}
          onChange={(e) => changer('localisation', e.target.value)} placeholder="Quartier, arrondissement..." />
        <label className="block">
          <span className="etiquette">Trier par</span>
          <select className="champ" value={params.get('tri') || 'nom'} onChange={(e) => changer('tri', e.target.value)}>
            <option value="nom">Nom (A à Z)</option>
            <option value="recent">Plus récents</option>
            <option value="niveau">Niveau d’étude (le plus élevé)</option>
          </select>
        </label>

        <div className="flex flex-wrap items-center gap-3 sm:col-span-2 lg:col-span-3">
          <button className="btn-secondaire" onClick={reinitialiser}>Réinitialiser les filtres</button>
          {/* Filtre « compétence » : il n'a pas de champ, il vient d'un clic sur une compétence dans un profil */}
          {competence && (
            <span className="inline-flex items-center gap-2 rounded-full bg-teal-50 px-3 py-1 text-sm text-teal-800">
              Compétence : {competence}
              <button aria-label="Retirer le filtre compétence" className="font-semibold" onClick={() => changer('competence', '')}>x</button>
            </span>
          )}
        </div>
      </div>

      {/* ---------- Résultats : quatre états possibles ---------- */}
      {erreur ? (
        <Alerte type="erreur" messages={erreur} />                                    /* 1. erreur réseau ou serveur */
      ) : !resultat ? (
        <Chargement />                                                                 /* 2. chargement */
      ) : (
        <>
          <p className="mb-3 text-sm text-slate-600" aria-live="polite">
            {resultat.total} profil{resultat.total > 1 ? 's' : ''} trouvé{resultat.total > 1 ? 's' : ''}
          </p>
          {resultat.data.length === 0 ? (
            <div className="carte text-center text-slate-600">Aucun profil ne correspond à votre recherche.</div>   // 3. vide
          ) : (
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">                                              {/* 4. résultats */}
              {resultat.data.map((p) => <CarteProfil key={p.id} profil={p} />)}
            </div>
          )}
          <Pagination page={resultat} onChange={(n) => changer('page', n)} />
        </>
      )}
    </>
  )
}
