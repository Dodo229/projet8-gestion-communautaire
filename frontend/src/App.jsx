import { BrowserRouter, Routes, Route } from 'react-router-dom';
import Menu from './components/Menu';
import PiedDePage from './components/PiedDePage';
import EnConstruction from './pages/EnConstruction';
import Introuvable from './pages/Introuvable';

// ===== IMPORTS : chacun ajoute les imports de SES pages sous son nom =====
// Rôle 2 — Oremus
// Rôle 3 — Eckson
import MonProfil from './pages/MonProfil';
import AdminProfils from './pages/admin/AdminProfils';
// Rôle 4 — Jean-Pierre
import Recherche from './pages/recherche/Recherche';
import ProfilDetail from './pages/recherche/ProfilDetail';
// Rôle 5 — Mia
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
          <Route path="/mon-profil" element={<MonProfil />} />
          <Route path="/admin/profils" element={<AdminProfils />} />

          {/* ===== RÔLE 4 — Jean-Pierre : /recherche, /profils/:id ===== */}
          <Route path="/recherche" element={<Recherche />} />
          <Route path="/profils/:id" element={<ProfilDetail />} />

          {/* ===== RÔLE 5 — Mia : /, /annuaire, /admin/categories ===== */}
          <Route path="/" element={<EnConstruction titre="Accueil" />} />
          <Route path="/annuaire" element={<EnConstruction titre="Annuaire" />} />

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
