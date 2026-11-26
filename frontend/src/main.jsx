// FOURNI PAR LE MEMBRE 1 (chef de projet) : point d entrée.
// Point d'entrée du site : c'est le premier fichier exécuté par le navigateur.
import React from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom'
import App from './App.jsx'
import { AuthProvider } from './context/AuthContext.jsx'
import './index.css'

createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    {/* BrowserRouter : active la navigation entre pages sans rechargement du site */}
    <BrowserRouter>
      {/* AuthProvider : rend l'utilisateur connecté disponible dans toute l'application */}
      <AuthProvider>
        <App />
      </AuthProvider>
    </BrowserRouter>
  </React.StrictMode>
)
