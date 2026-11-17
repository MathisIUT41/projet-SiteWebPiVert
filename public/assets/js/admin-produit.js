// Formulaire produit de l'admin : champ « Animal » selon l'univers, caractéristiques, aperçu photo.
const categorie = document.getElementById('id_categorie');
const champAnimal = document.getElementById('champ-animal');
const liste = document.getElementById('caracteristiques-liste');
const modele = document.getElementById('modele-ligne-caracteristique');
const ajouter = document.getElementById('ajouter-caracteristique');
const photo = document.getElementById('photo');
const apercu = document.getElementById('apercu-photo');
const MAX_CARACTERISTIQUES = 6;

function majAnimal() {
  const option = categorie?.selectedOptions[0];
  if (champAnimal) champAnimal.hidden = option?.dataset.univers !== 'animalier';
}

function majBoutonAjouter() {
  if (ajouter && liste) ajouter.disabled = liste.children.length >= MAX_CARACTERISTIQUES;
}

categorie?.addEventListener('change', majAnimal);
majAnimal();

ajouter?.addEventListener('click', () => {
  const index = Number(liste.dataset.prochainIndex || 0);
  liste.dataset.prochainIndex = String(index + 1);
  const html = modele.innerHTML.replaceAll('__INDEX__', String(index));
  liste.insertAdjacentHTML('beforeend', html);
  liste.lastElementChild.querySelector('input')?.focus();
  majBoutonAjouter();
});

liste?.addEventListener('click', (e) => {
  const bouton = e.target.closest('[data-retirer-caracteristique]');
  if (!bouton) return;
  bouton.closest('li').remove();
  majBoutonAjouter();
  ajouter?.focus();
});
majBoutonAjouter();

photo?.addEventListener('change', () => {
  const fichier = photo.files?.[0];
  if (fichier && apercu) apercu.src = URL.createObjectURL(fichier);
});
