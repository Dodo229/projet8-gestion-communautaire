// Une "carte" blanche arrondie, utilisée pour afficher un contenu (annonce, profil...)
export default function Carte({ children, className = "" }) {
  return (
    <div className={`bg-white rounded-carte shadow-sm border border-contour p-5 ${className}`}>
      {children}
    </div>
  );
}
