// Un composant = un bout de page réutilisable.
// Usage : <Bouton>Envoyer</Bouton>   ou   <Bouton variante="danger">Supprimer</Bouton>
const styles = {
  principal: "bg-primaire hover:bg-primaire-fonce text-white",
  danger: "bg-danger hover:opacity-90 text-white",
  contour: "border border-primaire text-primaire hover:bg-primaire hover:text-white",
};

export default function Bouton({ variante = "principal", className = "", ...props }) {
  return (
    <button
      className={`px-4 py-2 rounded-lg font-medium transition disabled:opacity-50 ${styles[variante]} ${className}`}
      {...props}
    />
  );
}
