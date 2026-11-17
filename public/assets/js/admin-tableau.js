// Tableau des produits de l'admin : recherche, filtre par catégorie, confirmation de suppression.
import { correspond } from './recherche.js';

const recherche = document.getElementById('admin-recherche');
const categorie = document.getElementById('admin-categorie');
const lignes = [...document.querySelectorAll('.tableau tbody tr[data-recherche]')];
const aucun = document.getElementById('admin-aucun');

function filtrer() {
  let visibles = 0;
  for (const ligne of lignes) {
    const ok = correspond(ligne.dataset.recherche, recherche?.value ?? '')
      && (!categorie?.value || ligne.dataset.categorie === categorie.value);
    ligne.hidden = !ok;
    if (ok) visibles += 1;
  }
  if (aucun) aucun.hidden = visibles !== 0;
}

recherche?.addEventListener('input', filtrer);
categorie?.addEventListener('change', filtrer);

document.addEventListener('submit', (e) => {
  const message = e.target.dataset?.confirmer;
  if (message && !window.confirm(message)) e.preventDefault();
});
