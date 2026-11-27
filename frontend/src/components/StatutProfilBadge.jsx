import Badge from "./Badge";

export default function StatutProfilBadge({ statut }) {
  if (statut === "valide") {
    return <Badge couleur="vert">Validé</Badge>;
  }
  if (statut === "rejete") {
    return (
      <span className="inline-block text-xs font-semibold uppercase px-2 py-0.5 rounded-full bg-danger/15 text-danger">
        Rejeté
      </span>
    );
  }
  return <Badge couleur="accent">En attente</Badge>;
}
