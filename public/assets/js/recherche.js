// Recherche instantanée dans les cartes produit d'une page.

/** Minuscules, sans accents ; apostrophes et tirets deviennent des espaces. */
export function normaliser(texte) {
  return String(texte)
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .toLowerCase()
    .replace(/['’`\-‐–—_]/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();
}

/** Vrai si chaque mot du terme recherché apparaît dans le texte de la carte. */
export function correspond(texteCarte, terme) {
  const mots = normaliser(terme).split(' ').filter(Boolean);
  if (mots.length === 0) return true;
  const texte = normaliser(texteCarte);
  return mots.every((mot) => texte.includes(mot));
}

if (typeof document !== 'undefined') {
  const formulaire = document.querySelector('[data-recherche-formulaire]');
  const champ = document.getElementById('recherche');
  const cartes = [...document.querySelectorAll('.carte-produit[data-recherche]')];
  const aucun = document.getElementById('aucun-resultat');
  const compte = document.getElementById('compte-resultats');

  if (formulaire && champ) {
    formulaire.hidden = false; // amélioration progressive : sans JS, pas de champ inutile
    formulaire.addEventListener('submit', (e) => e.preventDefault());

    champ.addEventListener('input', () => {
      let visibles = 0;
      for (const carte of cartes) {
        const ok = correspond(carte.dataset.recherche, champ.value);
        carte.hidden = !ok;
        if (ok) visibles += 1;
      }
      if (aucun) aucun.hidden = visibles !== 0;
      if (compte) compte.textContent = `${visibles} produit${visibles > 1 ? 's' : ''}`;
    });
  }
}
