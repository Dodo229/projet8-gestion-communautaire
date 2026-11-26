// Petite étiquette : <Badge>Annonce</Badge> ou <Badge couleur="accent">Nouveau</Badge>
const styles = {
  vert: "bg-primaire-clair text-primaire",
  accent: "bg-accent/20 text-texte",
};

export default function Badge({ couleur = "vert", children }) {
  return (
    <span className={`inline-block text-xs font-semibold uppercase px-2 py-0.5 rounded-full ${styles[couleur]}`}>
      {children}
    </span>
  );
}
