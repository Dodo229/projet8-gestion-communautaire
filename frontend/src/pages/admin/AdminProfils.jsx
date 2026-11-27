import { useEffect, useState } from "react";
import api from "../../api";
import Carte from "../../components/Carte";
import Bouton from "../../components/Bouton";
import StatutProfilBadge from "../../components/StatutProfilBadge";

export default function AdminProfils() {
  const [profils, setProfils] = useState([]);
  const [filtre, setFiltre] = useState("en_attente");
  const [version, setVersion] = useState(0);
  const [chargement, setChargement] = useState(true);
  const [message, setMessage] = useState("");
  const [erreur, setErreur] = useState("");

  useEffect(() => {
    let actif = true;
    api
      .get("/admin/profils", { params: { statut: filtre } })
      .then((rep) => {
        if (!actif) return;
        setProfils(rep.data.data || []);
        setErreur("");
      })
      .catch((err) => {
        if (!actif) return;
        if (err.response?.status === 401 || err.response?.status === 403) {
          setErreur("Accès réservé aux administrateurs de la mairie.");
        } else {
          setErreur("Impossible de charger la liste des profils.");
        }
      })
      .finally(() => {
        if (actif) setChargement(false);
      });

    return () => {
      actif = false;
    };
  }, [filtre, version]);

  function changerFiltre(nouveauFiltre) {
    setChargement(true);
    setErreur("");
    setFiltre(nouveauFiltre);
  }

  async function traiter(id, action) {
    setMessage("");
    setErreur("");
    try {
      const rep = await api.patch(`/admin/profils/${id}/${action}`);
      setMessage(rep.data.message || "Statut mis à jour.");
      setChargement(true);
      setVersion((v) => v + 1);
    } catch (err) {
      setErreur(err.response?.data?.message || "Action impossible pour ce profil.");
    }
  }

  return (
    <div className="max-w-5xl mx-auto p-4 space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-primaire">Administration · Profils à valider</h1>
        <p className="text-sm text-gris">
          Vérifiez les informations et les justificatifs avant de rendre un profil visible dans l'annuaire.
        </p>
      </div>

      <div className="flex flex-wrap gap-2">
        {[
          { cle: "en_attente", libelle: "En attente" },
          { cle: "valide", libelle: "Validés" },
          { cle: "rejete", libelle: "Rejetés" },
          { cle: "tous", libelle: "Tous" },
        ].map((onglet) => (
          <Bouton
            key={onglet.cle}
            variante={filtre === onglet.cle ? "principal" : "contour"}
            onClick={() => changerFiltre(onglet.cle)}
          >
            {onglet.libelle}
          </Bouton>
        ))}
      </div>

      {message && (
        <div className="rounded-lg bg-primaire-clair text-primaire px-4 py-3 text-sm font-medium">
          {message}
        </div>
      )}
      {erreur && (
        <div className="rounded-lg bg-danger/10 text-danger px-4 py-3 text-sm font-medium">
          {erreur}
        </div>
      )}

      {chargement && <p className="text-gris">Chargement des profils...</p>}

      {!chargement && !erreur && profils.length === 0 && (
        <Carte>
          <p className="text-gris">Aucun profil dans cette catégorie.</p>
        </Carte>
      )}

      {!chargement && !erreur && profils.length > 0 && (
        <Carte className="overflow-x-auto p-0">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="border-b border-contour text-xs uppercase text-gris">
                <th className="p-4">Usager</th>
                <th className="p-4">Métier & Localisation</th>
                <th className="p-4">Documents</th>
                <th className="p-4">Statut</th>
                <th className="p-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-contour text-sm">
              {profils.map((p) => (
                <tr key={p.id}>
                  <td className="p-4 align-top">
                    <p className="font-semibold text-texte">{p.user?.name || `Usager #${p.user_id}`}</p>
                    <p className="text-xs text-gris">{p.user?.email}</p>
                    {p.telephone && <p className="text-xs text-gris">{p.telephone}</p>}
                  </td>
                  <td className="p-4 align-top">
                    <p className="font-medium text-texte">{p.metier}</p>
                    <p className="text-xs text-gris">
                      {p.secteur} {p.categorie_nom ? `· ${p.categorie_nom}` : ""}
                    </p>
                    <p className="text-xs text-gris">
                      {p.localisation} · {p.niveau_etude}
                    </p>
                  </td>
                  <td className="p-4 align-top space-y-1">
                    {(!p.documents || p.documents.length === 0) && (
                      <span className="text-xs text-gris">Aucun</span>
                    )}
                    {p.documents?.map((d) => (
                      <div key={d.id}>
                        <a
                          href={d.url}
                          target="_blank"
                          rel="noreferrer"
                          className="text-xs text-primaire underline uppercase"
                        >
                          {d.type}
                        </a>
                      </div>
                    ))}
                  </td>
                  <td className="p-4 align-top">
                    <StatutProfilBadge statut={p.statut} />
                  </td>
                  <td className="p-4 align-top">
                    <div className="flex justify-end gap-2">
                      {p.statut !== "valide" && (
                        <Bouton onClick={() => traiter(p.id, "valider")}>
                          Valider
                        </Bouton>
                      )}
                      {p.statut !== "rejete" && (
                        <Bouton variante="danger" onClick={() => traiter(p.id, "rejeter")}>
                          Rejeter
                        </Bouton>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </Carte>
      )}
    </div>
  );
}
