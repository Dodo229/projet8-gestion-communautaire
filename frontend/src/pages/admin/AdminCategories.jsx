import { useState, useEffect } from "react";
import api from "../../api";
import Carte from "../../components/Carte";
import Badge from "../../components/Badge";

function lireErreur(err, defaut) {
  const data = err.response && err.response.data;
  if (data && data.errors && data.errors.nom) return data.errors.nom[0];
  if (data && data.message) return data.message;
  return defaut;
}

export default function AdminCategories() {
  const [categories, setCategories] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState("");
  const [succes, setSucces] = useState("");
  const [nouveauNom, setNouveauNom] = useState("");
  const [editionId, setEditionId] = useState(null);
  const [editionNom, setEditionNom] = useState("");

  function charger() {
    api
      .get("/categories")
      .then((reponse) => setCategories(reponse.data))
      .catch(() => setErreur("Impossible de charger les catégories."))
      .finally(() => setChargement(false));
  }

  useEffect(() => {
    charger();
  }, []);

  function effacerMessages() {
    setErreur("");
    setSucces("");
  }

  function ajouter(e) {
    e.preventDefault();
    effacerMessages();
    api
      .post("/admin/categories", { nom: nouveauNom })
      .then(() => {
        setNouveauNom("");
        setSucces("Catégorie ajoutée.");
        charger();
      })
      .catch((err) =>
        setErreur(lireErreur(err, "Impossible d'ajouter la catégorie."))
      );
  }

  function renommer(id) {
    effacerMessages();
    api
      .put(`/admin/categories/${id}`, { nom: editionNom })
      .then(() => {
        setEditionId(null);
        setSucces("Catégorie renommée.");
        charger();
      })
      .catch((err) =>
        setErreur(lireErreur(err, "Impossible de renommer la catégorie."))
      );
  }

  function supprimer(id) {
    effacerMessages();
    api
      .delete(`/admin/categories/${id}`)
      .then(() => {
        setSucces("Catégorie supprimée.");
        charger();
      })
      .catch((err) =>
        setErreur(lireErreur(err, "Impossible de supprimer la catégorie."))
      );
  }

  return (
    <main className="accueil">
      <h1>Administration des catégories</h1>

      <form onSubmit={ajouter}>
        <input
          type="text"
          value={nouveauNom}
          onChange={(e) => setNouveauNom(e.target.value)}
          placeholder="Nom de la nouvelle catégorie"
        />
        <button type="submit">Ajouter</button>
      </form>

      {chargement && <p>Chargement...</p>}
      {erreur && <p className="message-erreur">{erreur}</p>}
      {succes && <p>{succes}</p>}

      <div className="accueil-grille">
        {categories.map((c) => (
          <Carte key={c.id} className="carte-categorie">
            {editionId === c.id ? (
              <div>
                <input
                  type="text"
                  value={editionNom}
                  onChange={(e) => setEditionNom(e.target.value)}
                />
                <button onClick={() => renommer(c.id)}>Enregistrer</button>
                <button onClick={() => setEditionId(null)}>Annuler</button>
              </div>
            ) : (
              <div>
                <h2>{c.nom}</h2>
                <Badge>
                  {c.profils_count} profil{c.profils_count > 1 ? "s" : ""}
                </Badge>
               <div className="admin-cat-actions">
                  <button
                    onClick={() => {
                      effacerMessages();
                      setEditionId(c.id);
                      setEditionNom(c.nom);
                    }}
                  >
                    Renommer
                  </button>
                  <button onClick={() => supprimer(c.id)}>Supprimer</button>
                </div>
              </div>
            )}
          </Carte>
        ))}
      </div>
    </main>
  );
}