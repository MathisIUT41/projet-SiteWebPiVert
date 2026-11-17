import { test } from 'node:test';
import assert from 'node:assert/strict';
import { vueProduit } from '../../public/assets/js/modale-produit.js';

const base = {
  id_produit: 7, nom: 'Lapin nain', prix: '45.00', quantite: 3, photo_nom: 'lapin.jpg',
  libelle_categorie: 'Animaux', libelle_espece: 'Rongeurs', description: 'Calme',
  caracteristiques: [{ libelle: 'Vie', valeur: '8–10 ans', icone: 'sablier' }],
};

test('prix formaté', () => assert.equal(vueProduit(base).prix, '45,00 €'));
test('prix avec milliers', () => assert.equal(vueProduit({ ...base, prix: 1299.5 }).prix, '1 299,50 €'));
test('image', () => assert.equal(vueProduit(base).image, 'images/produits/lapin.jpg'));
test('image par défaut', () => assert.equal(vueProduit({ ...base, photo_nom: null }).image, 'images/produits/defaut.svg'));
test('rupture', () => { const v = vueProduit({ ...base, quantite: 0 }); assert.ok(v.rupture); assert.equal(v.qteMax, 0); });
test('étiquettes catégorie et espèce', () => assert.deepEqual(vueProduit(base).etiquettes, ['Animaux', 'Rongeurs']));
test('étiquette catégorie seule', () => assert.deepEqual(vueProduit({ ...base, libelle_espece: null }).etiquettes, ['Animaux']));
test('caractéristiques transmises', () => assert.equal(vueProduit(base).caracteristiques.length, 1));
