import { Link } from 'react-router-dom';

export default function Menu() {
  // Le rôle est enregistré par la page Connexion (Oremus) : 'admin' ou 'usager'
  const role = localStorage.getItem('role');

  return (
    <header>
      <nav>
        <Link to="/">Accueil</Link> |{' '}
        <Link to="/annuaire">Annuaire</Link> |{' '}
        <Link to="/recherche">Recherche</Link> |{' '}
        <Link to="/actualites">Actualités</Link> |{' '}
        <Link to="/connexion">Connexion</Link>
        {/* Le lien admin n'apparaît que pour un admin connecté */}
        {role === 'admin' && (
          <>
            {' '}| <Link to="/admin">Espace admin</Link>
          </>
        )}
      </nav>
    </header>
  );
}