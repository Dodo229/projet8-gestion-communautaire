import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import api from "../api";
import Carte from "../components/Carte";
import Bouton from "../components/Bouton";
import StatutProfilBadge from "../components/StatutProfilBadge";

const FORM_VIDE = {
  categorie_id: "",
  secteur: "",
  metier: "",
  niveau_etude: "Licence",
  localisation: "",
  telephone: "",
  bio: "",
  competences: "",
  experiences: "",
};

const NIVEAUX_ETUDE = ["CAP", "BEPC", "BAC", "Licence", "Master", "Doctorat", "Autre"];

export default function MonProfil() {
  const [form, setForm] = useState(FORM_VIDE);
  const [categories, setCategories] = useState([]);
  const [profilExistant, setProfilExistant] = useState(null);
  const [documents, setDocuments] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [nonConnecte, setNonConnecte] = useState(false);
  const [envoiEnCours, setEnvoiEnCours] = useState(false);
  const [message, setMessage] = useState("");
  const [erreur, setErreur] = useState("");
  const [docStatut, setDocStatut] = useState("");

  useEffect(() => {
    let actif = true;
    api
      .get("/mon-profil")
      .then((rep) => {
        if (!actif) return;
        setCategories(rep.data.categories || []);
        const p = rep.data.profil;
        setProfilExistant(p);
        if (p) {
          setForm({
            categorie_id: p.categorie_id ? String(p.categorie_id) : "",
            secteur: p.secteur || "",
            metier: p.metier || "",
            niveau_etude: p.niveau_etude || "Licence",
            localisation: p.localisation || "",
            telephone: p.telephone || "",
            bio: p.bio || "",
            competences: p.competences || "",
            experiences: p.experiences || "",
          });
          setDocuments(p.documents || []);
        }
      })
      .catch((err) => {
        if (!actif) return;
        if (err.response?.status === 401) {
          setNonConnecte(true);
        } else {
          setErreur("Impossible de charger votre profil pour le moment.");
        }
      })
      .finally(() => {
        if (actif) setChargement(false);
      });

    return () => {
      actif = false;
    };
  }, []);

  function changerChamp(e) {
    setForm({ ...form, [e.target.name]: e.target.value });
  }

  async function enregistrerProfil(e) {
    e.preventDefault();
    setEnvoiEnCours(true);
    setMessage("");
    setErreur("");

    const charge = {
      ...form,
      categorie_id: form.categorie_id ? Number(form.categorie_id) : null,
    };

    try {
      const rep = profilExistant
        ? await api.put("/mon-profil", charge)
        : await api.post("/mon-profil", charge);

      setProfilExistant(rep.data.profil);
      setDocuments(rep.data.profil.documents || []);
      setMessage(rep.data.message || "Profil enregistré.");
    } catch (err) {
      const msg =
        err.response?.data?.message ||
        "Erreur lors de l'enregistrement. Vérifiez les champs obligatoires.";
      setErreur(msg);
    } finally {
      setEnvoiEnCours(false);
    }
  }

  async function envoyerDocument(type, fichier) {
    if (!fichier) return;
    setDocStatut("");
    setErreur("");

    const donnees = new FormData();
    donnees.append("type", type);
    donnees.append("fichier", fichier);

    try {
      const rep = await api.post("/mon-profil/documents", donnees);
      const nouveauDoc = rep.data.document;
      setDocuments((anciens) => [
        ...anciens.filter((d) => d.type !== type),
        nouveauDoc,
      ]);
      setDocStatut(`Document (${type.toUpperCase()}) envoyé avec succès.`);
    } catch (err) {
      const erreursValidation = err.response?.data?.errors;
      const detail = erreursValidation
        ? Object.values(erreursValidation).flat().join(" ")
        : err.response?.data?.message;
      setErreur(detail || "Fichier refusé (vérifiez le format et la taille).");
    }
  }

  if (chargement) {
    return (
      <div className="max-w-4xl mx-auto p-4">
        <p className="text-gris">Chargement de votre profil...</p>
      </div>
    );
  }

  if (nonConnecte) {
    return (
      <div className="max-w-2xl mx-auto p-4">
        <Carte className="space-y-4 text-center">
          <h1 className="text-2xl font-bold text-primaire">Mon profil professionnel</h1>
          <p className="text-gris">
            Vous devez être connecté pour créer ou modifier votre profil et déposer vos documents.
          </p>
          <Link to="/connexion">
            <Bouton>Se connecter</Bouton>
          </Link>
        </Carte>
      </div>
    );
  }

  const docParType = (type) => documents.find((d) => d.type === type);

  return (
    <div className="max-w-4xl mx-auto p-4 space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-3xl font-bold text-primaire">Mon profil professionnel</h1>
          <p className="text-sm text-gris">
            Renseignez vos informations pour apparaître dans l'annuaire Adtatis après validation de la mairie.
          </p>
        </div>
        {profilExistant && <StatutProfilBadge statut={profilExistant.statut} />}
      </div>

      {message && (
        <div className="rounded-lg bg-primaire-clair text-primaire px-4 py-3 text-sm font-medium">
          {message}
        </div>
      )}
      {erreur && (
        <div className="rounded-lg bg-danger/10 text-danger px-4 py-3 text-sm font-medium">
          {erreur}
        </div>
      )}

      <Carte>
        <h2 className="text-xl font-semibold mb-4">
          {profilExistant ? "Modifier mes informations" : "Créer mon profil"}
        </h2>

        <form onSubmit={enregistrerProfil} className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium mb-1">Catégorie</label>
              <select
                name="categorie_id"
                value={form.categorie_id}
                onChange={changerChamp}
                className="champ"
              >
                <option value="">-- Choisir une catégorie --</option>
                {categories.map((c) => (
                  <option key={c.id} value={c.id}>
                    {c.nom}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium mb-1">Secteur d'activité *</label>
              <input
                name="secteur"
                value={form.secteur}
                onChange={changerChamp}
                required
                placeholder="Ex. Artisanat, Numérique, BTP..."
                className="champ"
              />
            </div>

            <div>
              <label className="block text-sm font-medium mb-1">Métier / Profession *</label>
              <input
                name="metier"
                value={form.metier}
                onChange={changerChamp}
                required
                placeholder="Ex. Menuisier, Développeur web..."
                className="champ"
              />
            </div>

            <div>
              <label className="block text-sm font-medium mb-1">Niveau d'étude *</label>
              <select
                name="niveau_etude"
                value={form.niveau_etude}
                onChange={changerChamp}
                required
                className="champ"
              >
                {NIVEAUX_ETUDE.map((n) => (
                  <option key={n} value={n}>
                    {n}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium mb-1">Localisation (commune / quartier) *</label>
              <input
                name="localisation"
                value={form.localisation}
                onChange={changerChamp}
                required
                placeholder="Ex. Abomey-Calavi, Cotonou..."
                className="champ"
              />
            </div>

            <div>
              <label className="block text-sm font-medium mb-1">Téléphone</label>
              <input
                name="telephone"
                value={form.telephone}
                onChange={changerChamp}
                placeholder="Ex. 01 97 00 00 00"
                className="champ"
              />
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium mb-1">Présentation / Bio</label>
            <textarea
              name="bio"
              value={form.bio}
              onChange={changerChamp}
              rows={3}
              placeholder="Décrivez votre parcours et vos services..."
              className="champ"
            />
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium mb-1">Compétences</label>
              <textarea
                name="competences"
                value={form.competences}
                onChange={changerChamp}
                rows={2}
                placeholder="Ex. Menuiserie aluminium, gestion d'équipe..."
                className="champ"
              />
            </div>

            <div>
              <label className="block text-sm font-medium mb-1">Expériences</label>
              <textarea
                name="experiences"
                value={form.experiences}
                onChange={changerChamp}
                rows={2}
                placeholder="Ex. 5 ans en entreprise, gérant depuis 2022..."
                className="champ"
              />
            </div>
          </div>

          <div className="pt-2">
            <Bouton type="submit" disabled={envoiEnCours}>
              {envoiEnCours
                ? "Enregistrement..."
                : profilExistant
                  ? "Mettre à jour mon profil"
                  : "Créer mon profil"}
            </Bouton>
          </div>
        </form>
      </Carte>

      <Carte className="space-y-4">
        <h2 className="text-xl font-semibold">Mes documents (CV, Photo, Justificatif)</h2>

        {!profilExistant ? (
          <p className="text-sm text-gris">
            Enregistrez d'abord votre profil ci-dessus pour pouvoir déposer votre CV, votre photo et vos documents légaux.
          </p>
        ) : (
          <>
            {docStatut && (
              <p className="text-sm font-medium text-primaire">{docStatut}</p>
            )}

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="border border-contour rounded-lg p-3 space-y-2">
                <p className="font-medium text-sm">CV (PDF uniquement, max 5 Mo)</p>
                <input
                  type="file"
                  accept=".pdf,application/pdf"
                  onChange={(e) => envoyerDocument("cv", e.target.files?.[0])}
                  className="block w-full text-xs text-gris"
                />
                {docParType("cv") && (
                  <a
                    href={docParType("cv").url}
                    target="_blank"
                    rel="noreferrer"
                    className="inline-block text-xs text-primaire underline"
                  >
                    Voir le CV déposé
                  </a>
                )}
              </div>

              <div className="border border-contour rounded-lg p-3 space-y-2">
                <p className="font-medium text-sm">Photo (JPG ou PNG, max 2 Mo)</p>
                <input
                  type="file"
                  accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                  onChange={(e) => envoyerDocument("photo", e.target.files?.[0])}
                  className="block w-full text-xs text-gris"
                />
                {docParType("photo") && (
                  <img
                    src={docParType("photo").url}
                    alt="Photo de profil"
                    className="h-16 w-16 object-cover rounded-lg border border-contour"
                  />
                )}
              </div>

              <div className="border border-contour rounded-lg p-3 space-y-2">
                <p className="font-medium text-sm">Document légal (PDF/JPG/PNG, max 5 Mo)</p>
                <input
                  type="file"
                  accept=".pdf,.jpg,.jpeg,.png"
                  onChange={(e) => envoyerDocument("legal", e.target.files?.[0])}
                  className="block w-full text-xs text-gris"
                />
                {docParType("legal") && (
                  <a
                    href={docParType("legal").url}
                    target="_blank"
                    rel="noreferrer"
                    className="inline-block text-xs text-primaire underline"
                  >
                    Voir le justificatif
                  </a>
                )}
              </div>
            </div>
          </>
        )}
      </Carte>
    </div>
  );
}
