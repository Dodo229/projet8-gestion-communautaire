// Les initiales du nom, affichées dans le rond à la place d'une photo : « Kossi Houngbo » -> « KH »
export default function initiales(nom = "") {
  return nom
    .split(" ")
    .filter(Boolean)
    .slice(0, 2)
    .map((mot) => mot[0].toUpperCase())
    .join("");
}
