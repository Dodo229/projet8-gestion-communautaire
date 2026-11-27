import { useState, useEffect } from "react";
import "./Accueil.css";
import api from "../api";
import Carte from "../components/Carte";
import Badge from "../components/Badge";

export default function Accueil() {
  const [categories, setCategories] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState("");

  useEffect(() => {
    api
      .get("/categories")
      .then((reponse) => setCategories(reponse.data))
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
          <Carte key={c.id} className="carte-categorie">
            <h2>{c.nom}</h2>
            <Badge>
              {c.profils_count} profil{c.profils_count > 1 ? "s" : ""}
            </Badge>
          </Carte>
        ))}
      </div>
    </main>
  );
}