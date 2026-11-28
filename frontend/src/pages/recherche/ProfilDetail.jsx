import { useEffect, useState } from "react";
import { Link, useNavigate, useParams } from "react-router-dom";
import api from "../../api";
import Carte from "../../components/Carte";
import Badge from "../../components/Badge";
import Bouton from "../../components/Bouton";
import initiales from "./initiales";

// Page /profils/:id — RÔLE 4 (Jean-Pierre) : le profil complet d'un membre validé
export default function ProfilDetail() {
  const { id } = useParams();       // l'identifiant lu dans l'adresse : /profils/12 -> "12"
  const navigate = useNavigate();
  const [profil, setProfil] = useState(null);
  const [etat, setEtat] = useState("chargement"); // "chargement", "ok", "introuvable" ou "erreur"

  useEffect(() => {
    api
      .get(`/profils/${id}`)
      .then((rep) => {
        setProfil(rep.data);
        setEtat("ok");
      })
      .catch((err) => setEtat(err.response?.status === 404 ? "introuvable" : "erreur"));
  }, [id]);

  if (etat === "chargement") {
    return (
      <div className="max-w-4xl mx-auto p-4">
        <div className="h-48 rounded-carte bg-white border border-contour animate-pulse" />
      </div>
    );
  }

  if (etat !== "ok") {
    return (
      <div className="max-w-4xl mx-auto p-4">
        <Carte className="text-center py-10">
          <h1 className="text-2xl font-bold">
            {etat === "introuvable" ? "Ce profil n'existe pas ou n'est pas encore validé" : "Une erreur est survenue"}
          </h1>
          <Link to="/recherche"><Bouton className="mt-4">Retour à la recherche</Bouton></Link>
        </Carte>
      </div>
    );
  }

  // « Travail en équipe, rigueur » -> ["Travail en équipe", "rigueur"]
  const competences = (profil.competences || "").split(",").map((c) => c.trim()).filter(Boolean);
  const membreDepuis = new Date(profil.created_at).toLocaleDateString("fr-FR", { month: "long", year: "numeric" });

  return (
    <div className="max-w-4xl mx-auto p-4 space-y-6">
      <button onClick={() => navigate(-1)} className="text-primaire font-semibold hover:underline">
        ← Retour aux résultats
      </button>

      {/* ---------- En-tête : bandeau vert, rond avec les initiales, puis le nom ---------- */}
      <div className="bg-white rounded-carte border border-contour shadow-sm overflow-hidden">
        <div className="h-24 bg-primaire" />
        <div className="px-6 pb-6">
          <div className="-mt-12 w-24 h-24 rounded-full bg-primaire-clair text-primaire border-4 border-white font-titre font-bold text-3xl flex items-center justify-center">
            {initiales(profil.nom)}
          </div>
          <h1 className="mt-3 text-3xl font-bold">{profil.nom}</h1>
          <p className="text-lg text-gris">{profil.metier}</p>
          <div className="mt-3 flex flex-wrap gap-2">
            {profil.categorie && <Badge>{profil.categorie}</Badge>}
            <Badge couleur="accent">{profil.secteur}</Badge>
          </div>
        </div>
      </div>

      {/* ---------- Infos clés ---------- */}
      <div className="grid gap-4 sm:grid-cols-3">
        <Info titre="Localisation" valeur={`📍 ${profil.localisation}`} />
        <Info titre="Niveau d'étude" valeur={`🎓 ${profil.niveau_etude}`} />
        <Info titre="Membre depuis" valeur={`🗓️ ${membreDepuis}`} />
      </div>

      <div className="grid gap-6 md:grid-cols-[1fr_280px]">
        <div className="space-y-6">
          {profil.bio && (
            <Carte>
              <h2 className="text-xl font-semibold mb-2">Présentation</h2>
              <p className="leading-relaxed">{profil.bio}</p>
            </Carte>
          )}

          {competences.length > 0 && (
            <Carte>
              <h2 className="text-xl font-semibold mb-3">Compétences</h2>
              <div className="flex flex-wrap gap-2">
                {competences.map((c) => <Badge key={c}>{c}</Badge>)}
              </div>
            </Carte>
          )}

          {profil.experiences && (
            <Carte>
              <h2 className="text-xl font-semibold mb-2">Expériences</h2>
              <p className="leading-relaxed whitespace-pre-line">{profil.experiences}</p>
            </Carte>
          )}
        </div>

        {/* ---------- Contact ---------- */}
        <aside>
          <Carte className="space-y-3 md:sticky md:top-4">
            <h2 className="text-xl font-semibold">Contacter</h2>
            {profil.telephone ? (
              <>
                <p className="text-gris text-sm">Joignable par téléphone :</p>
                <a
                  href={`tel:${profil.telephone}`}
                  className="block text-center px-4 py-2 rounded-lg font-semibold bg-primaire text-white hover:bg-primaire-fonce transition"
                >
                  📞 {profil.telephone}
                </a>
              </>
            ) : (
              <p className="text-gris text-sm">Ce membre n'a pas rendu ses coordonnées publiques.</p>
            )}
          </Carte>
        </aside>
      </div>
    </div>
  );
}

function Info({ titre, valeur }) {
  return (
    <Carte>
      <p className="text-xs font-semibold uppercase text-gris">{titre}</p>
      <p className="mt-1 font-semibold">{valeur}</p>
    </Carte>
  );
}
