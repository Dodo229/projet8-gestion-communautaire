import { BrowserRouter, Routes, Route } from 'react-router-dom';
import Menu from './components/Menu';
import PiedDePage from './components/PiedDePage';
import EnConstruction from './pages/EnConstruction';
import Introuvable from './pages/Introuvable';

// ===== IMPORTS : chacun ajoute les imports de SES pages sous son nom =====
// Rôle 2 — Oremus
// Rôle 3 — Eckson
// Rôle 4 — Jean-Pierre
// Rôle 5 — Mia
import Accueil from './pages/Accueil';
import AnnuaireCategorie from './pages/AnnuaireCategorie';
import AdminCategories from './pages/admin/AdminCategories';
// Rôle 6 — Casimir
// Rôle 7 — Vernon
// Rôle 8 — Eustache

export default function App() {
  return (
    <BrowserRouter>
      <Menu />
      <main>
        <Routes>
          {/* ===== RÔLE 2 — Oremus : /inscription, /connexion, /mot-de-passe-oublie ===== */}
          <Route path="/connexion" element={<EnConstruction titre="Connexion" />} />

          {/* ===== RÔLE 3 — Eckson : /mon-profil, /admin/profils ===== */}

          {/* ===== RÔLE 4 — Jean-Pierre : /recherche, /profils/:id ===== */}
          <Route path="/recherche" element={<EnConstruction titre="Recherche" />} />

          {/* ===== RÔLE 5 — Mia : /, /annuaire, /admin/categories ===== */}
          <Route path="/" element={<Accueil />
           } />
          <Route path="/annuaire" element={<EnConstruction titre="Annuaire" />} />
          <Route path="/categories/:id" element={<AnnuaireCategorie />} />
          <Route path="/admin/categories" element={<AdminCategories />} />

          {/* ===== RÔLE 6 — Casimir : /admin (tableau de bord) ===== */}
          <Route path="/admin" element={<EnConstruction titre="Tableau de bord" />} />

          {/* ===== RÔLE 7 — Vernon : /actualites, /admin/annonces, /admin/newsletter ===== */}
          <Route path="/actualites" element={<EnConstruction titre="Actualités" />} />

          {/* ===== RÔLE 8 — Eustache : /mon-compte, /confidentialite, /admin/comptes, /admin/journal ===== */}

          {/* ===== Page introuvable : toujours en dernier ===== */}
          <Route path="*" element={<Introuvable />} />
        </Routes>
      </main>
      <PiedDePage />
    </BrowserRouter>
  );
}