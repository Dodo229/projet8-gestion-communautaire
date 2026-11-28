import { Link } from 'react-router-dom';

export default function Introuvable() {
  return (
    <section>
      <h1>Page introuvable</h1>
      <p>Cette adresse n'existe pas.</p>
      <Link to="/">Retour à l'accueil</Link>
    </section>
  );
}