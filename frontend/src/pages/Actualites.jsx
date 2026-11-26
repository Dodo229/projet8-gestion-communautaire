import { useEffect, useState } from "react";
import api from "../api";
import Carte from "../components/Carte";
import Badge from "../components/Badge";
import Bouton from "../components/Bouton";
import NewsletterForm from "../components/NewsletterForm";

export default function Actualites() {
  const [annonces, setAnnonces] = useState([]);
  const [type, setType] = useState("");        // "" = tout, "annonce" ou "actualite"
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState("");

  // useEffect : s'exécute à l'ouverture de la page ET chaque fois que `type` change
  useEffect(() => {
    setChargement(true);
    setErreur("");
    api
      .get("/annonces", { params: type ? { type } : {} })
      .then((rep) => setAnnonces(rep.data.data)) // paginate() met la liste dans .data
      .catch(() => setErreur("Impossible de charger les annonces."))
      .finally(() => setChargement(false));
  }, [type]);

  return (
    <div className="max-w-4xl mx-auto p-4 space-y-6">
      <h1 className="text-3xl font-bold text-primaire">Actualités et annonces</h1>

      <div className="flex gap-2">
        <Bouton variante={type === "" ? "principal" : "contour"} onClick={() => setType("")}>Tout</Bouton>
        <Bouton variante={type === "annonce" ? "principal" : "contour"} onClick={() => setType("annonce")}>Annonces</Bouton>
        <Bouton variante={type === "actualite" ? "principal" : "contour"} onClick={() => setType("actualite")}>Actualités</Bouton>
      </div>

      {chargement && <p>Chargement...</p>}
      {erreur && <p className="text-danger">{erreur}</p>}
      {!chargement && !erreur && annonces.length === 0 && <p>Aucune publication pour le moment.</p>}

      <div className="grid gap-4">
        {annonces.map((a) => (
          <Carte key={a.id}>
            <Badge>{a.type}</Badge>
            <h2 className="text-xl font-semibold mt-2">{a.titre}</h2>
            <p className="text-sm text-gris">
              {new Date(a.date_publication).toLocaleDateString("fr-FR")}
            </p>
            <p className="mt-2">{a.contenu}</p>
          </Carte>
        ))}
      </div>

      <Carte>
        <h2 className="text-lg font-semibold mb-2">Recevoir la newsletter</h2>
        <NewsletterForm />
      </Carte>
    </div>
  );
}
