// Configuration de Vite, l'outil qui compile et sert le site React.
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  plugins: [react(), tailwindcss()],   // react() : comprend le JSX ; tailwindcss() : génère le CSS
  server: { port: 5173 },              // port du serveur de développement
})
