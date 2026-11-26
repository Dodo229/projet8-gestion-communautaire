// FOURNI PAR LE MEMBRE 1 : liste des pages et de leurs adresses (version réduite au prototype).
import { Navigate, Route, Routes } from 'react-router-dom'
import Layout from './components/Layout'
import Annuaire from './pages/Annuaire'
import ProfilDetail from './pages/ProfilDetail'
import Connexion from './pages/Connexion'
import NonTrouve from './pages/NonTrouve'

export default function App() {
  return (
    <Routes>
      <Route element={<Layout />}>
        <Route index element={<Navigate to="/annuaire" replace />} />
        {/* VOS DEUX PAGES (membre 4) */}
        <Route path="annuaire" element={<Annuaire />} />
        <Route path="profils/:id" element={<ProfilDetail />} />
        {/* Fournie par le membre 2 */}
        <Route path="connexion" element={<Connexion />} />
        <Route path="*" element={<NonTrouve />} />
      </Route>
    </Routes>
  )
}
