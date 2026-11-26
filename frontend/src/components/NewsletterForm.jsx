import { useState } from "react";
import api from "../api"; // le fichier api.js créé par le chef de projet (instance Axios)
import Bouton from "./Bouton";

export default function NewsletterForm() {
  const [email, setEmail] = useState("");     // ce que l'utilisateur tape
  const [message, setMessage] = useState(""); // message de succès ou d'erreur
  const [erreur, setErreur] = useState(false);
  const [envoi, setEnvoi] = useState(false);

  async function envoyer(e) {
    e.preventDefault(); // empêche la page de se recharger
    setEnvoi(true);
    setMessage("");
    try {
      const reponse = await api.post("/newsletter", { email });
      setErreur(false);
      setMessage(reponse.data.message);
      setEmail("");
    } catch (err) {
      setErreur(true);
      // Laravel renvoie les erreurs de validation dans err.response.data.errors
      setMessage(err.response?.data?.errors?.email?.[0] || "Une erreur est survenue.");
    } finally {
      setEnvoi(false);
    }
  }

  return (
    <form onSubmit={envoyer} className="flex flex-col gap-2 sm:flex-row">
      <input
        type="email"
        required
        value={email}
        onChange={(e) => setEmail(e.target.value)}
        placeholder="Votre e-mail"
        className="flex-1 champ"
      />
      <Bouton type="submit" disabled={envoi}>
        {envoi ? "Envoi..." : "S'abonner"}
      </Bouton>
      {message && (
        <p className={`text-sm ${erreur ? "text-danger" : "text-primaire"}`}>{message}</p>
      )}
    </form>
  );
}
