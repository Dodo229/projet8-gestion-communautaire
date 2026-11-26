import { useState, useEffect } from "react";
import "./Accueil.css";

const API_URL = "http://localhost:8000/api";

export default function Accueil() {
  const [categories, setCategories] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState("");

  useEffect(() => {
    fetch(`${API_URL}/categories`, { headers: { Accept: "application/json" } })
      .then((reponse) => {
        if (!reponse.ok) throw new Error("Erreur " + reponse.status);
        return reponse.json();
      })
      .then((donnees) => setCategories(donnees))
      .catch(() => setErreur("Impossible de charger les catégories."))
      .finally(() => setChargement(false));
  }, []);

  return (
    <main className="accueil">
      <h1>Annuaire de la communauté</h1>
      <p className="accueil-intro">Trouvez des profils par catégorie.</p>

      {chargement && <p>Chargement...</p>}
      {erreur && <p className="message-erreur">{erreur}</p>}
      {!chargement && !erreur && categories.length === 0 && (
        <p>Aucune catégorie pour le moment.</p>
      )}

      <div className="accueil-grille">
        {categories.map((c) => (
          <div key={c.id} className="carte-categorie">
            <h2>{c.nom}</h2>
           <span className="badge">
  {c.profils_count} profil{c.profils_count > 1 ? "s" : ""}
</span>
          </div>
        ))}
      </div>
    </main>
  );
}