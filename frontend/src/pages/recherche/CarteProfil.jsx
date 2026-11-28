import { Link } from "react-router-dom";
import Carte from "../../components/Carte";
import Badge from "../../components/Badge";
import initiales from "./initiales";

// Une carte de résultat : cliquer dessus ouvre le profil complet
export default function CarteProfil({ profil }) {
  return (
    <Link to={`/profils/${profil.id}`} className="block group">
      <Carte className="h-full transition group-hover:shadow-md group-hover:border-primaire">
        <div className="flex items-center gap-3">
          <div className="w-12 h-12 shrink-0 rounded-full bg-primaire-clair text-primaire font-titre font-bold flex items-center justify-center">
            {initiales(profil.nom)}
          </div>
          <div className="min-w-0">
            <h3 className="text-lg font-semibold leading-tight truncate">{profil.nom}</h3>
            <p className="text-gris truncate">{profil.metier}</p>
          </div>
        </div>

        <div className="mt-4 flex flex-wrap items-center gap-2">
          {profil.categorie && <Badge>{profil.categorie}</Badge>}
        </div>

        <p className="mt-3 text-sm text-gris">
          📍 {profil.localisation} · 🎓 {profil.niveau_etude}
        </p>

        <p className="mt-4 text-sm font-semibold text-primaire group-hover:underline">
          Voir le profil →
        </p>
      </Carte>
    </Link>
  );
}
