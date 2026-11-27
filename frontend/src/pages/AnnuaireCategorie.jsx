import { useState, useEffect } from "react";
import { useParams, Link } from "react-router-dom";
import api from "../api";
import Carte from "../components/Carte";
import Badge from "../components/Badge";

export default function AnnuaireCategorie() {
  const { id } = useParams();
  const [profils, setProfils] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState("");

  useEffect(() => {
    api
      .get(`/categories/${id}/profils`)
      .then((reponse) => setProfils(reponse.data))
      .catch(() => setErreur("Impossible de charger les profils."))
      .finally(() => setChargement(false));
  }, [id]);

  return (
    <main className="accueil">
      <h1>Profils de la catégorie</h1>
      <p className="accueil-intro">
        <Link to="/">← Retour à l'accueil</Link>
      </p>

      {chargement && <p>Chargement...</p>}
      {erreur && <p className="message-erreur">{erreur}</p>}
      {!chargement && !erreur && profils.length === 0 && (
        <p>Aucun profil validé dans cette catégorie pour le moment.</p>
      )}

      <div className="accueil-grille">
        {profils.map((p) => (
          <Carte key={p.id} className="carte-categorie">
            <h2>{p.metier}</h2>
            <Badge>{p.secteur}</Badge>
          </Carte>
        ))}
      </div>
    </main>
  );
}