import { useEffect, useState } from "react";
import api from "../../api";
import Carte from "../../components/Carte";
import Bouton from "../../components/Bouton";

const vide = { titre: "", contenu: "", type: "annonce" };

export default function AdminAnnonces() {
  const [annonces, setAnnonces] = useState([]);
  const [form, setForm] = useState(vide);
  const [enEdition, setEnEdition] = useState(null); // id de l'annonce modifiée, sinon null
  const [message, setMessage] = useState("");

  function charger() {
    api.get("/annonces").then((rep) => setAnnonces(rep.data.data));
  }
  useEffect(charger, []); // [] = une seule fois, à l'ouverture

  // Met à jour un seul champ du formulaire
  function changer(e) {
    setForm({ ...form, [e.target.name]: e.target.value });
  }

  async function envoyer(e) {
    e.preventDefault();
    setMessage("");
    try {
      if (enEdition) {
        await api.put(`/admin/annonces/${enEdition}`, form);
      } else {
        await api.post("/admin/annonces", form);
      }
      setForm(vide);
      setEnEdition(null);
      charger();
      setMessage("Enregistré.");
    } catch (err) {
      setMessage("Erreur : vérifiez les champs.");
    }
  }

  function modifier(a) {
    setEnEdition(a.id);
    setForm({ titre: a.titre, contenu: a.contenu, type: a.type });
  }

  async function supprimer(id) {
    if (!window.confirm("Supprimer cette publication ?")) return;
    await api.delete(`/admin/annonces/${id}`);
    charger();
  }

  return (
    <div className="max-w-4xl mx-auto p-4 space-y-6">
      <h1 className="text-3xl font-bold text-primaire">Gérer les annonces</h1>

      <Carte>
        <form onSubmit={envoyer} className="space-y-3">
          <input name="titre" value={form.titre} onChange={changer} required placeholder="Titre"
            className="champ" />
          <textarea name="contenu" value={form.contenu} onChange={changer} required rows={4} placeholder="Contenu"
            className="champ" />
          <select name="type" value={form.type} onChange={changer}
            className="champ">
            <option value="annonce">Annonce</option>
            <option value="actualite">Actualité</option>
          </select>
          <div className="flex gap-2">
            <Bouton type="submit">{enEdition ? "Modifier" : "Publier"}</Bouton>
            {enEdition && (
              <Bouton type="button" variante="contour" onClick={() => { setEnEdition(null); setForm(vide); }}>
                Annuler
              </Bouton>
            )}
          </div>
          {message && <p className="text-sm">{message}</p>}
        </form>
      </Carte>

      {annonces.length === 0 && <p>Aucune publication.</p>}
      {annonces.map((a) => (
        <Carte key={a.id} className="flex items-start justify-between gap-4">
          <div>
            <p className="font-semibold">{a.titre} <span className="text-xs text-gris">({a.type})</span></p>
            <p className="text-sm">{a.contenu}</p>
          </div>
          <div className="flex gap-2 shrink-0">
            <Bouton variante="contour" onClick={() => modifier(a)}>Modifier</Bouton>
            <Bouton variante="danger" onClick={() => supprimer(a.id)}>Supprimer</Bouton>
          </div>
        </Carte>
      ))}
    </div>
  );
}
