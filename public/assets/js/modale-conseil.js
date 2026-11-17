// Pop-up d'un conseil : reprend le titre, le texte et l'icône de la carte cliquée.
const modale = document.getElementById('modale-conseil');

if (modale) {
  const titre = document.getElementById('modale-conseil-titre');
  const texte = document.getElementById('modale-conseil-texte');
  const icone = document.getElementById('modale-conseil-icone');
  let declencheur = null;

  document.addEventListener('click', (e) => {
    const bouton = e.target.closest('[data-ouvrir-conseil]');
    if (!bouton) return;
    declencheur = bouton;
    titre.textContent = bouton.dataset.titre;
    texte.textContent = bouton.dataset.texte;
    const svg = bouton.closest('.carte-conseil')?.querySelector('.carte-conseil-icone svg');
    icone.replaceChildren(...(svg ? [svg.cloneNode(true)] : []));
    // La couleur d'accent de la section d'origine suit la pop-up (page Conseils).
    const theme = bouton.closest('.theme-vegetal, .theme-animalier');
    modale.classList.toggle('theme-vegetal', theme?.classList.contains('theme-vegetal') ?? false);
    modale.classList.toggle('theme-animalier', theme?.classList.contains('theme-animalier') ?? false);
    modale.showModal();
  });

  modale.addEventListener('click', (e) => {
    if (e.target === modale || e.target.closest('[data-fermer-modale]')) modale.close();
  });

  modale.addEventListener('close', () => declencheur?.focus());
}
