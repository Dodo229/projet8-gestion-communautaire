import { useEffect, useState } from "react";
import { useSearchParams } from "react-router-dom";
import api from "../../api";
import Carte from "../../components/Carte";
import Bouton from "../../components/Bouton";
import CarteProfil from "./CarteProfil";

// Page /recherche — RÔLE 4 (Jean-Pierre)
// Les filtres sont gardés dans l'adresse (?q=menuisier&secteur=Artisanat&page=2) :
// on peut donc partager le lien d'une recherche, et le bouton « retour » du navigateur fonctionne.
export default function Recherche() {
  const [params, setParams] = useSearchParams();
  const [texte, setTexte] = useState(params.get("q") || ""); // ce qui est tapé dans la barre
  const [filtres, setFiltres] = useState({ categories: [], secteurs: [], niveaux: [], localisations: [] });
  const [resultat, setResultat] = useState(null); // la réponse paginée de Laravel
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState("");

  // 1. Les choix des listes déroulantes : chargés une seule fois, à l'ouverture
  useEffect(() => {
    api.get("/profils/filtres").then((rep) => setFiltres(rep.data)).catch(() => {});
  }, []);

  // 2. Les résultats : rechargés chaque fois que l'adresse (les filtres) change
  useEffect(() => {
    api
      .get("/profils", { params: Object.fromEntries(params) })
      .then((rep) => {
        setResultat(rep.data);
        setErreur("");
      })
      .catch(() => setErreur("Impossible de charger les profils. Le serveur Laravel est-il lancé ?"))
      .finally(() => setChargement(false));
  }, [params]);

  // Met à jour l'adresse et affiche le chargement
  function appliquer(suivant) {
    setChargement(true);
    setParams(suivant);
  }

  // Change un filtre dans l'adresse (et revient à la page 1)
  function changer(nom, valeur) {
    const suivant = new URLSearchParams(params);
    if (valeur) suivant.set(nom, valeur);
    else suivant.delete(nom);
    suivant.delete("page");
    appliquer(suivant);
  }

  function chercher(e) {
    e.preventDefault(); // empêche la page de se recharger
    changer("q", texte.trim());
  }

  function reinitialiser() {
    setTexte("");
    appliquer({});
  }

  function allerPage(numero) {
    const suivant = new URLSearchParams(params);
    suivant.set("page", numero);
    appliquer(suivant);
    window.scrollTo({ top: 0, behavior: "smooth" });
  }

  const profils = resultat?.data ?? [];

  return (
    <div className="max-w-6xl mx-auto p-4 space-y-6">
      {/* ---------- Bandeau de recherche ---------- */}
      <section className="bg-primaire rounded-carte px-6 py-10 sm:px-10 text-white">
        {/* style={{ color: "white" }} : la charte met les titres en vert, on force le blanc sur le fond vert */}
        <h1 className="text-3xl sm:text-4xl font-bold" style={{ color: "white" }}>
          Trouvez un talent de la commune
        </h1>
        <p className="mt-2 text-white/80">
          Cadres, artisans, commerçants, entrepreneurs : cherchez par nom, métier ou compétence.
        </p>
        <form onSubmit={chercher} className="mt-6 flex flex-col sm:flex-row gap-2">
          <input
            type="search"
            value={texte}
            onChange={(e) => setTexte(e.target.value)}
            placeholder="Ex. menuisier, comptable, développeur web…"
            className="champ flex-1 text-texte"
          />
          <button
            type="submit"
            className="px-6 py-2 rounded-lg font-semibold bg-accent text-texte hover:opacity-90 transition"
          >
            Rechercher
          </button>
        </form>
      </section>

      <div className="grid gap-6 md:grid-cols-[260px_1fr]">
        {/* ---------- Filtres ---------- */}
        <aside>
          <Carte className="space-y-4 md:sticky md:top-4">
            <h2 className="text-lg font-semibold">Filtrer</h2>

            <Filtre label="Catégorie" valeur={params.get("categorie_id")} onChange={(v) => changer("categorie_id", v)}>
              {filtres.categories.map((c) => (
                <option key={c.id} value={c.id}>{c.nom}</option>
              ))}
            </Filtre>

            <Filtre label="Secteur" valeur={params.get("secteur")} onChange={(v) => changer("secteur", v)}>
              {filtres.secteurs.map((s) => <option key={s} value={s}>{s}</option>)}
            </Filtre>

            <Filtre label="Niveau d'étude" valeur={params.get("niveau_etude")} onChange={(v) => changer("niveau_etude", v)}>
              {filtres.niveaux.map((n) => <option key={n} value={n}>{n}</option>)}
            </Filtre>

            <Filtre label="Localisation" valeur={params.get("localisation")} onChange={(v) => changer("localisation", v)}>
              {filtres.localisations.map((l) => <option key={l} value={l}>{l}</option>)}
            </Filtre>

            <label className="block">
              <span className="block mb-1 text-sm font-semibold">Trier par</span>
              <select
                className="champ"
                value={params.get("tri") || ""}
                onChange={(e) => changer("tri", e.target.value)}
              >
                <option value="">Nom (A → Z)</option>
                <option value="recent">Plus récents</option>
              </select>
            </label>

            <Bouton variante="contour" className="w-full" onClick={reinitialiser}>
              Réinitialiser
            </Bouton>
          </Carte>
        </aside>

        {/* ---------- Résultats ---------- */}
        <section className="space-y-4">
          {!chargement && !erreur && resultat && (
            <p className="text-gris">
              <span className="font-semibold text-texte">{resultat.total}</span>{" "}
              profil{resultat.total > 1 ? "s" : ""} trouvé{resultat.total > 1 ? "s" : ""}
            </p>
          )}

          {erreur && (
            <Carte className="border-danger">
              <p className="text-danger">{erreur}</p>
            </Carte>
          )}

          {/* Pendant le chargement : des cartes grises qui « clignotent » */}
          {chargement && (
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              {[1, 2, 3, 4, 5, 6].map((i) => (
                <div key={i} className="h-44 rounded-carte bg-white border border-contour animate-pulse" />
              ))}
            </div>
          )}

          {!chargement && !erreur && profils.length === 0 && (
            <Carte className="text-center py-10">
              <p className="text-4xl">🔍</p>
              <h2 className="mt-2 text-xl font-semibold">Aucun profil ne correspond</h2>
              <p className="mt-1 text-gris">Essayez un autre mot-clé ou retirez un filtre.</p>
              <Bouton className="mt-4" onClick={reinitialiser}>Voir tous les profils</Bouton>
            </Carte>
          )}

          {!chargement && profils.length > 0 && (
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              {profils.map((p) => <CarteProfil key={p.id} profil={p} />)}
            </div>
          )}

          {/* ---------- Pagination ---------- */}
          {!chargement && resultat && resultat.last_page > 1 && (
            <div className="flex items-center justify-center gap-3 pt-2">
              <Bouton
                variante="contour"
                disabled={resultat.current_page === 1}
                onClick={() => allerPage(resultat.current_page - 1)}
              >
                ← Précédent
              </Bouton>
              <span className="text-gris">
                Page {resultat.current_page} sur {resultat.last_page}
              </span>
              <Bouton
                variante="contour"
                disabled={resultat.current_page === resultat.last_page}
                onClick={() => allerPage(resultat.current_page + 1)}
              >
                Suivant →
              </Bouton>
            </div>
          )}
        </section>
      </div>
    </div>
  );
}

// Une liste déroulante de filtre, avec « Tous » en premier choix
function Filtre({ label, valeur, onChange, children }) {
  return (
    <label className="block">
      <span className="block mb-1 text-sm font-semibold">{label}</span>
      <select className="champ" value={valeur || ""} onChange={(e) => onChange(e.target.value)}>
        <option value="">Tous</option>
        {children}
      </select>
    </label>
  );
}
